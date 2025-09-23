<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Orders;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendOrderCompletedMail;
use App\Jobs\SendReviewReminderMail;
class OrderController extends Controller
{
    /**
     * Danh sách đơn hàng (admin)
     */
    public function index()
    {
        $orders = Orders::with('user')->latest()->get();
        return view('admin.orders.index', compact('orders'));
    }

    /**
     * Chi tiết đơn hàng
     */
    public function show(Orders $order)
    {
        $order->load(['items.product', 'user']);
        return view('admin.orders.show', compact('order'));
    }

    /**
     * Cập nhật trạng thái giao hàng (inline) và đồng bộ payment/status
     *  - completed  -> payment_status = paid,     status = paid
     *  - cancelled  -> payment_status = cancelled,status = cancelled
     *  - khác       -> payment_status = pending,  status = processing (nếu chưa paid/cancelled)
     */
    public function update(Request $request, Orders $order)
    {
        $validated = $request->validate([
            'shipping_status' => 'required|in:not_shipped,packaged,shipping,completed,cancelled',
        ]);

        DB::transaction(function () use ($order, $validated) {

            $shipping = $validated['shipping_status'];
            $order->shipping_status = $shipping;

            switch ($shipping) {
                case 'completed':
                    // Hoàn tất: coi như đã thanh toán thành công
                    $order->payment_status = 'paid';
                    $order->status         = 'paid';         // để tương thích báo cáo cũ dùng 'status'
                    // Nếu bạn có cột paid_at thì mở dòng dưới:
                    // $order->paid_at = now();
                     SendOrderCompletedMail::dispatch($order);

                    // Gửi email nhắc đánh giá sau 3 ngày (nếu chưa review)
                    SendReviewReminderMail::dispatch($order)
                        ->delay(now()->addDays(3));
                    break;

                case 'cancelled':
                    // Hủy đơn
                    $order->payment_status = 'cancelled';
                    $order->status         = 'cancelled';
                    break;

                default:
                    // Các trạng thái còn lại: chỉ để pending/processing khi chưa paid/cancelled
                    if (! in_array($order->payment_status, ['paid', 'cancelled'])) {
                        $order->payment_status = 'pending';
                    }
                    if (! in_array($order->status, ['paid', 'cancelled'])) {
                        $order->status = 'processing';
                    }
                    break;
            }

            $order->save();
        });

        return back()->with('success', 'Cập nhật trạng thái giao hàng thành công!');
    }
}
