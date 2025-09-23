<?php
namespace App\Mail;

use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReviewReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Orders $order;

    public function __construct(Orders $order)
    {
        $this->order = $order->load(['items.product','user']);
    }

    public function build()
    {
        return $this->subject('Nhắc bạn đánh giá đơn hàng #' . $this->order->id)
            ->markdown('emails.orders.reminder', ['order' => $this->order]);
    }
}
