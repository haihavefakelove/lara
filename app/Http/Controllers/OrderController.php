<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\OrderItems;
use App\Models\Product;
use App\Models\Coupon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index()
    {
        $orders = Orders::where('user_id', Auth::id())->latest()->get();
        return view('orders.index', compact('orders'));
    }

    public function show(Orders $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);
        $order->load('items.product');
        return view('orders.show', compact('order'));
    }



    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn trống.');
        }

        $validated = $request->validate([
            'phone'          => ['required', 'string', 'max:20'],
            'address'        => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:COD,online,momo_qr,momo_atm,bank_transfer'],
        ]);


        $computedTotal = 0;
        foreach ($cart as $productId => $line) {
            $p = Product::find($productId);
            if (!$p) {
                return back()->with('error', "Sản phẩm #{$productId} không tồn tại.");
            }
            $qty  = (int) ($line['quantity'] ?? 1);
            $computedTotal += (float) $p->price * $qty;
        }


        $couponData = session('coupon'); 
        $discount   = 0;
        $couponId   = null;

        if ($couponData && !empty($couponData['discount'])) {
            $discount = min((float)$couponData['discount'], $computedTotal);
            $couponId = $couponData['id'] ?? null;
        }

        $finalTotal = max($computedTotal - $discount, 0);

        try {
            DB::beginTransaction();

            // Tạo đơn
            $order = Orders::create([
                'user_id'        => Auth::id(),
                'name'           => Auth::user()->name ?? 'Khách',
                'address'        => $validated['address'],
                'phone'          => $validated['phone'],
                'total_price'    => $finalTotal,
                'discount'       => $discount,
                'coupon_id'      => $couponId,
                'status'         => 'processing',
                'payment_method' => $validated['payment_method'] === 'payos'
                    ? 'payos'
                    : (in_array($validated['payment_method'], ['online','momo_qr','momo_atm']) ? 'momo' : 'COD'),
                'payment_status' => $validated['payment_method'] === 'payos'
                    ? 'pending'
                    : (in_array($validated['payment_method'], ['online','momo_qr','momo_atm']) ? 'pending' : 'unpaid'),
            ]);

            // Lưu chi tiết + trừ kho
            foreach ($cart as $productId => $line) {
                $qty = (int) ($line['quantity'] ?? 1);

                $product = Product::where('id', $productId)->lockForUpdate()->first();
                if (!$product) {
                    throw new \RuntimeException("Sản phẩm #{$productId} không tồn tại.");
                }
                if ($qty > (int)$product->quantity) {
                    throw new \RuntimeException("Sản phẩm \"{$product->name}\" chỉ còn {$product->quantity} cái.");
                }

                OrderItems::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'price'      => (float)$product->price,
                ]);

                $product->decrement('quantity', $qty);
            }

            // Nếu có coupon -> tăng used
            if ($couponId) {
                Coupon::where('id', $couponId)->increment('used');
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Tạo đơn hàng thất bại: ' . $e->getMessage());
            return back()->with('error', $e->getMessage());
        }


        if ($validated['payment_method'] === 'COD') {
            session()->forget(['cart', 'coupon']);
            return redirect()->route('orders.index')->with('success', 'Đặt hàng thành công (COD). Cảm ơn bạn!');
        }


        if ($validated['payment_method'] === 'bank_transfer') {
            return redirect()->route('orders.bank_transfer', $order);
        }


        $method      = $validated['payment_method'];  
        $requestType = ($method === 'momo_atm') ? 'payWithATM' : 'captureWallet';
        return $this->momoCreate($order, $requestType);
    }



    protected function momoCreate(Orders $order, string $requestType = 'captureWallet')
    {
        $endpoint    = env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = env('MOMO_PARTNER_CODE', '');
        $accessKey   = env('MOMO_ACCESS_KEY', '');
        $secretKey   = env('MOMO_SECRET_KEY', '');

        if (!$partnerCode || !$accessKey || !$secretKey) {
            Log::warning('Chưa cấu hình MoMo ENV');
            return redirect()->route('orders.index')
                ->with('error', 'Chưa cấu hình thanh toán MoMo. Vui lòng chọn COD.');
        }

        $momoOrderId = 'ORD' . $order->id . '_' . time();
        $momoReqId   = 'REQ' . time() . rand(1000, 9999);

        $order->momo_request_id = $momoReqId;
        $order->momo_order_id   = $momoOrderId;
        $order->save();

        $amount      = (string) intval(round($order->total_price));
        $orderInfo   = 'Thanh toán đơn hàng #' . $order->id;
        $redirectUrl = env('MOMO_REDIRECT_URL', route('momo.callback'));
        $ipnUrl      = env('MOMO_IPN_URL',      route('momo.ipn'));
        $extraData   = base64_encode(json_encode(['order_id' => $order->id]));

        $rawHash = "accessKey={$accessKey}&amount={$amount}&extraData={$extraData}&ipnUrl={$ipnUrl}"
                 . "&orderId={$momoOrderId}&orderInfo={$orderInfo}&partnerCode={$partnerCode}"
                 . "&redirectUrl={$redirectUrl}&requestId={$momoReqId}&requestType={$requestType}";
        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $payload = [
            'partnerCode' => $partnerCode,
            'accessKey'   => $accessKey,
            'requestId'   => $momoReqId,
            'amount'      => $amount,
            'orderId'     => $momoOrderId,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl'      => $ipnUrl,
            'extraData'   => $extraData,
            'requestType' => $requestType,
            'signature'   => $signature,
        ];

        try {
            $res = Http::withHeaders(['Content-Type' => 'application/json'])
                ->post($endpoint, $payload)
                ->json();

            Log::info('MoMo create response', $res ?? []);

            if (!empty($res['payUrl'])) {
                return redirect()->away($res['payUrl']);
            }
            return redirect()->route('orders.index')
                ->with('error', 'Không thể chuyển đến MoMo: ' . ($res['message'] ?? 'Lỗi không xác định'));
        } catch (\Throwable $e) {
            Log::error('MoMo create error: ' . $e->getMessage());
            return redirect()->route('orders.index')
                ->with('error', 'Kết nối MoMo thất bại. Bạn có thể chọn COD.');
        }
    }


    public function momoCallback(Request $request)
    {
        $extra = json_decode(base64_decode($request->query('extraData', '')), true);
        $orderIdDb = $extra['order_id'] ?? 0;

        $order = Orders::find($orderIdDb);
        if (!$order) {
            $momoOrderId = $request->query('orderId');
            $order = Orders::where('momo_order_id', $momoOrderId)->first();
            if (!$order) {
                return redirect()->route('orders.index')->with('error', 'Không tìm thấy đơn hàng.');
            }
        }

        $resultCode = (int) $request->query('resultCode', -1);
        if ($resultCode === 0) {
            $order->update(['status' => 'paid', 'payment_status' => 'paid']);
            // XOÁ giỏ khi thanh toán thành công
            session()->forget(['cart','coupon']);
            return redirect()->route('orders.index')->with('success', 'Thanh toán MoMo thành công. Cảm ơn bạn!');
        }

        if ($order->status !== 'paid') {
            $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
        }

        return redirect()->route('orders.index')->with('error', 'Thanh toán không thành công hoặc bị huỷ.');
    }

    public function momoIpn(Request $request)
    {
        try {
            $extra = json_decode(base64_decode($request->input('extraData', '')), true);
            $orderIdDb = $extra['order_id'] ?? 0;

            $order = Orders::find($orderIdDb);
            if (!$order) {
                $momoOrderId = $request->input('orderId');
                $order = Orders::where('momo_order_id', $momoOrderId)->first();
                if (!$order) {
                    return response()->json(['resultCode' => 404, 'message' => 'Order not found'], 404);
                }
            }

            $resultCode = (int) $request->input('resultCode', -1);

            if ($resultCode === 0) {
                if ($order->payment_status !== 'paid') {
                    $order->update(['status' => 'paid', 'payment_status' => 'paid']);
                }
                return response()->json(['resultCode' => 0, 'message' => 'Confirm Success']);
            }

            if ($order->payment_status !== 'paid') {
                $order->update(['status' => 'cancelled', 'payment_status' => 'failed']);
            }
            return response()->json(['resultCode' => $resultCode, 'message' => 'Payment not success']);
        } catch (\Throwable $e) {
            Log::error('MoMo IPN error: ' . $e->getMessage());
            return response()->json(['resultCode' => 500, 'message' => 'Server error'], 500);
        }
    }


    public function payAgain(Orders $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);
        if ($order->payment_status === 'paid' || $order->status === 'paid') {
            return redirect()->route('orders.index')->with('success', 'Đơn đã thanh toán.');
        }
        return $this->momoCreate($order, 'payWithATM');
    }

    public function cancel(Orders $order)
    {
        if ($order->user_id !== Auth::id() && !(Auth::check() && Auth::user()->role === 'admin')) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Đơn hàng đã thanh toán, không thể hủy.');
        }

        if ($order->status !== 'cancelled') {
            DB::transaction(function () use ($order) {
                $order->load('items.product');
                foreach ($order->items as $item) {
                    $item->product()->increment('quantity', (int)$item->quantity);
                }
                $order->update(['status' => 'cancelled']);
            });
        }

        return back()->with('success', 'Đơn hàng đã được hủy và hoàn kho.');
    }



    public function payWithBankTransfer(Orders $order)
    {
        abort_unless(auth()->id() === $order->user_id, 403);
        if (in_array($order->status, ['paid','completed']) || $order->payment_status === 'paid') {
            return redirect()->route('orders.show', $order)->with('success','Đơn đã thanh toán.');
        }

        $order->update([
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
        ]);

        $amount = (int)$order->total_price;
        $orderCode = 'DH' . $order->id;
        $qrUrl = "https://img.vietqr.io/image/mbbank-0000153686666-compact.jpg?amount={$amount}&addInfo={$orderCode}";

        return view('payment.bank_transfer', [
            'order' => $order,
            'qrUrl' => $qrUrl,
            'orderCode' => $orderCode,
        ]);
    }

    public function status(Orders $order)
    {
        abort_unless(auth()->id() === $order->user_id, 403);
        return response()->json([
            'status' => (in_array($order->status, ['paid','completed']) || $order->payment_status === 'paid') ? 'paid' : 'pending',
        ]);
    }

public function thankyou(Orders $order, Request $req)
{
    abort_unless(auth()->id() === $order->user_id, 403);

    session()->forget(['cart','coupon']);
    return view('payment.thankyou', ['order'=>$order]);
}

public function cancelled(Orders $order)
{
    abort_unless(auth()->id() === $order->user_id, 403);

    return view('payment.cancelled', ['order'=>$order]);
}


    public function webhookBankTransfer(Request $request)
    {
        $payload = $request->all();

        if (!isset($payload['transferAmount']) || !isset($payload['content']) || !isset($payload['transferType'])) {
            return response()->json(['status' => 'error', 'message' => 'Không đủ dữ liệu'], 400);
        }
        $amount = (int)$payload['transferAmount'];
        $content = trim($payload['content']);
        if (!preg_match('/DH(\d+)/i', $content, $matches)) {
            return response()->json(['status' => 'ok', 'message' => 'Không tìm thấy mã đơn hàng']);
        }

        $orderId = (int)$matches[1];
        $order = Orders::find($orderId);

        if (!$order) {
            return response()->json(['status' => 'ok', 'message' => 'Không tìm thấy đơn hàng']);
        }

        if ($order->payment_status === 'paid' || in_array($order->status, ['paid', 'completed'])) {
            Log::info('[BankTransfer] Order already paid', ['order_id' => $orderId]);
            return response()->json(['status' => 'ok', 'message' => 'Đơn hàng đã được thanh toán']);
        }

        if ($order->payment_method !== 'bank_transfer') {
            Log::warning('[BankTransfer] Order payment method mismatch', [
                'order_id' => $orderId,
                'expected' => 'bank_transfer',
                'actual' => $order->payment_method
            ]);
            return response()->json(['status' => 'ok', 'message' => 'Không đúng phương thức thanh toán']);
        }

        $expectedAmount = (int)$order->total_price;
        if ($amount < $expectedAmount) {
            Log::warning('[BankTransfer] Amount insufficient', [
                'order_id' => $orderId,
                'expected' => $expectedAmount,
                'received' => $amount
            ]);
            return response()->json(['status' => 'ok', 'message' => 'Số tiền không đủ']);
        }

        try {
            DB::transaction(function () use ($order, $payload, $amount) {
                $order->update([
                    'status' => 'paid',
                    'payment_status' => 'paid',
                    'updated_at' => now(),
                ]);
            });

            return response()->json(['status' => 'success', 'message' => 'Thanh toán thành công']);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Thanh toán thất bại'], 500);
        }
    }

}
