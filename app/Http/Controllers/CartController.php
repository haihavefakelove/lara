<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Coupon;
class CartController extends Controller
{
    /**
     * Hiển thị giỏ hàng.
     */
    public function index()
    {
        // cart: [ productId => [name, price, quantity, category] ]
        $cart = session()->get('cart', []);
        $this->recalcCouponFromCart($cart);
        return view('cart.index', compact('cart'));
    }

    /**
     * Thêm 1 sản phẩm vào giỏ.
     * - Không cho vượt quá tồn kho (products.quantity)
     * - Hỗ trợ trả JSON khi expectsJson() (giữ như code cũ)
     */
    public function add(Request $request, Product $product)
    {
        $qtyReq = max(1, (int) $request->input('quantity', 1));
        $cart   = session()->get('cart', []);

        // Số lượng hiện có trong giỏ
        $qtyInCart = isset($cart[$product->id]) ? (int)$cart[$product->id]['quantity'] : 0;
        $newQty    = $qtyInCart + $qtyReq;

        if ($newQty > (int)$product->quantity) {
            $msg = "Sản phẩm \"{$product->name}\" chỉ còn {$product->quantity} cái.";
            if ($request->expectsJson()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        // Update cart
        $cart[$product->id] = [
            'name'     => $product->name,
            'quantity' => $newQty,
            'price'    => (float)$product->price,
            'category' => optional($product->category)->name,
        ];
        session()->put('cart', $cart);

        if ($request->expectsJson()) {
            $count = array_sum(array_column($cart, 'quantity'));
            return response()->json(['status' => 'ok', 'count' => $count]);
        }

        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được thêm vào giỏ hàng.');
    }
    public function applyCoupon(Request $request)
{
    $request->validate(['code' => 'required|string|max:50']);
    $code  = strtoupper(trim($request->code));
    $cart  = session('cart', []);
    if (count($cart) == 0) {
        return back()->with('error', 'Giỏ hàng trống.');
    }

    // Tính tổng giỏ
    $total = 0;
    foreach ($cart as $line) {
        $total += $line['price'] * ($line['quantity'] ?? 1);
    }

    $coupon = Coupon::valid()->where('code', $code)->first();
    if (!$coupon) {
        return back()->with('error', 'Mã giảm giá không hợp lệ hoặc đã hết hạn.');
    }

    // Kiểm tra đơn tối thiểu
    if ($coupon->min_order !== null && $total < (float)$coupon->min_order) {
        return back()->with('error', 'Đơn tối thiểu để dùng mã: ' . number_format($coupon->min_order,0,',','.') . ' đ');
    }

    $discount = $coupon->calcDiscount($total);
    if ($discount <= 0) {
        return back()->with('error', 'Mã giảm giá không áp dụng được.');
    }
    if ($discount > $total) $discount = $total;

    session(['coupon' => [
        'id'       => $coupon->id,
        'code'     => $coupon->code,
        'type'     => $coupon->type,
        'value'    => $coupon->value,
        'discount' => $discount,
    ]]);

    return back()->with('success', "Áp dụng mã {$coupon->code} thành công (-".number_format($discount,0,',','.')." đ)");
}

public function removeCoupon()
{
    session()->forget('coupon');
    return back()->with('success', 'Đã huỷ mã giảm giá.');
}

    /**
     * Cập nhật số lượng 1 item trong giỏ.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'quantity' => ['required','integer','min:1']
        ]);

        $cart = session()->get('cart', []);
        if (!isset($cart[$id])) {
            return redirect()->route('cart.index')->with('error', 'Cập nhật thất bại!');
        }

        $product = Product::find($id);
        if (!$product) {
            unset($cart[$id]);
            session()->put('cart', $cart);
            return redirect()->route('cart.index')->with('error', 'Sản phẩm không còn tồn tại.');
        }

        $newQty = (int)$request->quantity;
        if ($newQty > (int)$product->quantity) {
            return redirect()->route('cart.index')
                ->with('error', "Sản phẩm \"{$product->name}\" chỉ còn {$product->quantity} cái.");
        }

        $cart[$id]['quantity'] = $newQty;
        session()->put('cart', $cart);
        $this->recalcCouponFromCart($cart);

        return redirect()->route('cart.index')->with('success', 'Giỏ hàng đã được cập nhật!');
    }

    /**
     * Xoá 1 item khỏi giỏ theo id sản phẩm.
     */
    public function remove($id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
            $this->recalcCouponFromCart($cart);
        }
        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được xoá khỏi giỏ hàng.');
    }

    private function recalcCouponFromCart(array $cart): void
{
      $subtotal = 0;
    foreach ($cart as $line) {
        $subtotal += $line['price'] * $line['quantity'];
    }

    $couponData = session('coupon');
    if (!$couponData) {
        return;
    }
    $coupon = \App\Models\Coupon::valid()
        ->where('code', $couponData['code'])
        ->first();

    if (!$coupon) {
        session()->forget('coupon'); 
        return;
    }

    if ($coupon->min_order !== null && $subtotal < (float) $coupon->min_order) {
        session()->forget('coupon');
        return;
    }
 
    $discount = $coupon->calcDiscount($subtotal);
    if ($discount <= 0) {
        session()->forget('coupon');
        return;
    }
    if ($discount > $subtotal) {
        $discount = $subtotal;
    }
    session([
        'coupon' => [
            'id'       => $coupon->id,
            'code'     => $coupon->code,
            'type'     => $coupon->type,
            'value'    => $coupon->value,
            'discount' => $discount,
        ],
    ]);
}

}
