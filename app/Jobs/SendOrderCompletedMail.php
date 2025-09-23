<?php
namespace App\Jobs;

use App\Mail\OrderCompletedMail;
use App\Models\Orders;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendOrderCompletedMail implements ShouldQueue
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

        Mail::to($order->user->email)->send(new OrderCompletedMail($order));

        // nếu bạn muốn đánh dấu thời điểm gửi cám ơn luôn
        if (is_null($order->completed_at)) {
            $order->completed_at = now();
            $order->save();
        }
    }
}
