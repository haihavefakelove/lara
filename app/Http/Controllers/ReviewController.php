<?php

namespace App\Http\Controllers;

use App\Models\Orders;
use App\Models\OrderItems;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    // Form đánh giá cho từng item
    public function create(Orders $order, OrderItems $item)
    {
        // Chỉ chủ đơn được đánh giá
        abort_if($order->user_id !== Auth::id(), 403);

        // Chỉ cho đánh giá khi đơn đã giao/paid
        if (!in_array($order->shipping_status, ['completed']) && $order->payment_status !== 'paid') {
            return back()->with('error', 'Đơn hàng chưa hoàn tất nên chưa thể đánh giá.');
        }

        // Đã đánh giá chưa?
        $isReviewed = Review::where([
            'order_id'      => $order->id,
            'order_item_id' => $item->id,
            'product_id'    => $item->product_id,
            'user_id'       => Auth::id()
        ])->exists();

        if ($isReviewed) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này trong đơn này rồi.');
        }

        $product = $item->product()->first();
        return view('reviews.create', compact('order','item','product'));
    }

    public function store(Request $request, Orders $order, OrderItems $item)
    {
        abort_if($order->user_id !== Auth::id(), 403);

        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000'
        ]);

        // kiểm tra điều kiện đơn đã hoàn tất
        if (!in_array($order->shipping_status, ['completed']) && $order->payment_status !== 'paid') {
            return back()->with('error', 'Đơn hàng chưa hoàn tất nên chưa thể đánh giá.');
        }

        // không cho review 2 lần
        $exists = Review::where([
            'order_id'      => $order->id,
            'order_item_id' => $item->id,
            'product_id'    => $item->product_id,
            'user_id'       => Auth::id()
        ])->exists();

        if ($exists) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này trong đơn này rồi.');
        }

        Review::create([
            'user_id'       => Auth::id(),
            'order_id'      => $order->id,
            'order_item_id' => $item->id,
            'product_id'    => $item->product_id,
            'rating'        => (int)$request->rating,
            'comment'       => $request->comment,
            'status'        => 'approved', // hoặc 'pending' nếu muốn duyệt
        ]);

        return redirect()->route('orders.show', $order)
            ->with('success','Cảm ơn bạn đã đánh giá!');
    }
}
