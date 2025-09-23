
@component('mail::message')
# Cảm ơn bạn, {{ $order->user->name }}

Đơn hàng **#{{ $order->id }}** của bạn đã **hoàn tất**.  
Cảm ơn bạn đã tin tưởng MyShop!

@component('mail::table')
| Sản phẩm | SL | Giá |
|:--|:--:|--:|
@foreach($order->items as $it)
| {{ $it->product->name }} | {{ $it->quantity }} | {{ number_format($it->price,0,',','.') }} đ |
@endforeach
@endcomponent

**Tổng tiền:** {{ number_format($order->total_price,0,',','.') }} đ

@component('mail::button', ['url' => route('orders.show', $order)])
Xem đơn hàng
@endcomponent

Cảm ơn,<br>
{{ config('app.name') }}
@endcomponent
