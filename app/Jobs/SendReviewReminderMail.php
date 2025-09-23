<?php
namespace App\Jobs;

use App\Mail\ReviewReminderMail;
use App\Models\Orders;
use App\Models\Review;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendReviewReminderMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Orders $order;

    public function __construct(Orders $order)
    {
        $this->order = $order->withoutRelations();
    }

    public function handle(): void
    {
        $order = Orders::find($this->order->id);
        if (!$order || !$order->user) return;

        // Nếu đã có review thì không gửi
        $hasReview = Review::where('order_id', $order->id)->exists();
        if ($hasReview) return;

        // Nếu đã gửi reminder rồi thì bỏ qua
        if ($order->review_mail_sent_at) return;

        Mail::to($order->user->email)->send(new ReviewReminderMail($order));

        $order->review_mail_sent_at = now();
        $order->save();
    }
}
