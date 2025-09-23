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

// PayOS
use App\Services\PayOSService;

class OrderController extends Controller
{
    /* ===================== CUSTOMER – ORDERS LIST / DETAIL ===================== */

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

    /* ===================== CREATE ORDER (COD / MoMo / PayOS) ===================== */

    public function store(Request $request)
    {
        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn trống.');
        }

        $validated = $request->validate([
            'phone'          => ['required', 'string', 'max:20'],
            'address'        => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'in:COD,online,momo_qr,momo_atm,payos'],
        ]);

        // Tính tổng giá theo database
        $computedTotal = 0;
        foreach ($cart as $productId => $line) {
            $p = Product::find($productId);
            if (!$p) {
                return back()->with('error', "Sản phẩm #{$productId} không tồn tại.");
            }
            $qty  = (int) ($line['quantity'] ?? 1);
            $computedTotal += (float) $p->price * $qty;
        }

        /* ---- ÁP MÃ GIẢM GIÁ (NẾU CÓ) TỪ SESSION ---- */
        $couponData = session('coupon');   // ['id','code','discount',...]
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

        // CHỈ xoá giỏ khi COD; PayOS/MoMo sẽ xoá sau khi thanh toán thành công
        if ($validated['payment_method'] === 'COD') {
            session()->forget(['cart', 'coupon']);
            return redirect()->route('orders.index')->with('success', 'Đặt hàng thành công (COD). Cảm ơn bạn!');
        }

        // PAYOS: chuyển sang trang QR
        if ($validated['payment_method'] === 'payos') {
            return redirect()->route('orders.payos', $order);
        }

        // Còn lại: MoMo
        $method      = $validated['payment_method'];     // online|momo_qr|momo_atm
        $requestType = ($method === 'momo_atm') ? 'payWithATM' : 'captureWallet';
        return $this->momoCreate($order, $requestType);
    }

    /* ===================== MOMO – CREATE PAYMENT ===================== */

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

    /* ===================== MOMO – RETURN & IPN ===================== */

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

    /* ===================== PAY AGAIN (MoMo) ===================== */

    public function payAgain(Orders $order)
    {
        abort_if($order->user_id !== Auth::id(), 403);
        if ($order->payment_status === 'paid' || $order->status === 'paid') {
            return redirect()->route('orders.index')->with('success', 'Đơn đã thanh toán.');
        }
        return $this->momoCreate($order, 'captureWallet');
    }

    /* ===================== CANCEL ORDER (RESTORE INVENTORY) ===================== */

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

    /* ===================== PAYOS – CREATE LINK / WEBHOOK / POLLING ===================== */

    // mở trang thanh toán PayOS (QR MB)
    // mở trang thanh toán PayOS (QR MB)
public function payWithPayOS(Orders $order, \App\Services\PayOSService $payos)
{
    abort_unless(auth()->id() === $order->user_id, 403);

    // Đơn đã thanh toán thì thôi
    if (in_array($order->status, ['paid','completed']) || $order->payment_status === 'paid') {
        return redirect()->route('orders.show', $order)->with('success','Đơn đã thanh toán.');
    }

    // PayOS yêu cầu orderCode là số nguyên dương và duy nhất
    $orderCode = (int)($order->id . now()->format('His'));

    $returnUrl = str_replace('%ORDER_ID%', $order->id, config('payos.return_url'));
    $cancelUrl = str_replace('%ORDER_ID%', $order->id, config('payos.cancel_url'));

    $payload = [
        'orderCode'   => $orderCode,
        'amount'      => (int)$order->total_price,
        'description' => 'DH'.str_pad($order->id, 6, '0', STR_PAD_LEFT),
        'returnUrl'   => $returnUrl,
        'cancelUrl'   => $cancelUrl,
    ];

    try {
 $res  = $payos->createPaymentLink($payload);
$data = $res['data'] ?? $res;

$order->update([
    'payos_order_code'   => $orderCode,
    'payos_checkout_url' => $data['checkoutUrl'] ?? null,
    'payment_method'     => 'payos',
    'payment_status'     => 'pending',
]);

if (!empty($data['checkoutUrl'])) {
    return redirect()->away($data['checkoutUrl']);
}

// Cách 2 — Dùng view nội bộ (nếu bạn muốn nằm trong site mình):
return view('payment.payos', [
    'order'     => $order,
    'qrPayload' => $data['qrCode'] ?? null,       // CHUỖI VietQR, không phải ảnh
    'payUrl'    => $data['checkoutUrl'] ?? null,  // ĐỂ NULL nếu không có, KHÔNG dùng '#'
    'addInfo'   => $payload['description'],
    'rawJson'   => json_encode($res, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE),
]);

    } catch (\Throwable $e) {
        \Log::error('[PayOS] create link error: '.$e->getMessage());
        return redirect()->route('orders.show', $order)
            ->with('error','Không tạo được link PayOS, vui lòng chọn phương thức khác.');
    }
}


    // client polling để trang thấy thanh toán xong
    public function status(Orders $order)
    {
        abort_unless(auth()->id() === $order->user_id, 403);
        return response()->json([
            'status' => (in_array($order->status, ['paid','completed']) || $order->payment_status === 'paid') ? 'paid' : 'pending',
        ]);
    }

    // Webhook từ PayOS (cấu hình URL: /webhooks/payos)
   public function webhookPayOS(Request $request, PayOSService $payos)
{
    $raw = $request->getContent();
    $sigHdr = $request->header('x-signature') ?? $request->header('x-payos-signature');

    if (!$payos->verifyWebhook($raw, $sigHdr)) {
        \Log::warning('[PayOS] Invalid signature', ['header'=>$sigHdr, 'body'=>$raw]);
        return response()->json(['ok'=>false], 401);
    }

    $payload = $request->json()->all();
    \Log::info('[PayOS] webhook', $payload);

    $code    = (string)($payload['code']    ?? '');
    $success = (bool)  ($payload['success'] ?? ($code === '00'));
    $data    = (array) ($payload['data']    ?? []);

    $orderCode = (int)($data['orderCode'] ?? 0);
    $amount    = (int)($data['amount']    ?? 0);

    if (!$orderCode) return response()->json(['ok'=>true]);

    $order = \App\Models\Orders::where('payos_order_code', $orderCode)->first();
    if (!$order) return response()->json(['ok'=>true]);

    // Theo tài liệu: giao dịch thành công khi success=true && data.code=="00"
    $dataCode = (string)($data['code'] ?? '');
    $isPaid   = $success && $dataCode === '00';

    if ($isPaid && $amount >= (int)$order->total_price) {
        if ($order->payment_status !== 'paid' && !in_array($order->status, ['paid','completed'])) {
            $order->update([
                'status'         => 'paid',
                'payment_status' => 'paid',
                'paid_at'        => now(),
            ]);
        }
    }

    return response()->json(['ok'=>true]);
}
public function thankyou(Orders $order, \App\Services\PayOSService $payos, Request $req)
{
    abort_unless(auth()->id() === $order->user_id, 403);

    // Fallback: hỏi PayOS nếu đơn chưa paid
    if ($order->payment_status !== 'paid' && $order->payos_order_code) {
        try {
            $rs = $payos->getPaymentRequest((int)$order->payos_order_code);
            $st = strtoupper((string)($rs['data']['status'] ?? ''));
            if ($st === 'PAID') {
                $order->update([
                    'status'         => 'paid',
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                ]);
            } elseif ($st === 'CANCELLED') {
                $order->update(['status' => 'cancelled', 'payment_status' => 'cancelled']);
            }
        } catch (\Throwable $e) {
            \Log::warning('[PayOS] thankyou fallback failed: '.$e->getMessage());
        }
    }

    session()->forget(['cart','coupon']);
    return view('payment.thankyou', ['order'=>$order]);
}

public function cancelled(Orders $order, \App\Services\PayOSService $payos)
{
    abort_unless(auth()->id() === $order->user_id, 403);

    // Fallback cập nhật cancelled
    if (!in_array($order->status, ['cancelled','paid']) && $order->payos_order_code) {
        try {
            $rs = $payos->getPaymentRequest((int)$order->payos_order_code);
            $st = strtoupper((string)($rs['data']['status'] ?? ''));
            if ($st === 'CANCELLED') {
                $order->update(['status' => 'cancelled', 'payment_status' => 'cancelled']);
            } elseif ($st === 'PAID') {
                $order->update([
                    'status'         => 'paid',
                    'payment_status' => 'paid',
                    'paid_at'        => now(),
                ]);
            }
        } catch (\Throwable $e) {
            \Log::warning('[PayOS] cancelled fallback failed: '.$e->getMessage());
        }
    }

    return view('payment.cancelled', ['order'=>$order]);
}

}
