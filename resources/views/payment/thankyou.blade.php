@extends('layouts.app')
@section('content')
<div class="alert alert-success">Cảm ơn bạn! Đơn #{{ $order->id }} đã được ghi nhận.
@if(in_array($order->status,['paid','completed'])) Thanh toán thành công. @endif
</div>
<a href="{{ route('orders.index') }}" class="btn btn-primary">Đơn hàng của tôi</a>
@endsection
