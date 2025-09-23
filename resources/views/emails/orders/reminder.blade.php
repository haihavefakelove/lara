@component('mail::message')
# Nhắc đánh giá đơn hàng #{{ $order->id }}

Chúng tôi rất mong nhận được phản hồi từ bạn về các sản phẩm trong đơn hàng.

@component('mail::button', ['url' => route('orders.show', $order)])
Đánh giá ngay
@endcomponent

Cảm ơn bạn đã mua sắm tại {{ config('app.name') }}!
@endcomponent
