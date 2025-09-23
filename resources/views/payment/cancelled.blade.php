@extends('layouts.app')
@section('content')
<div class="alert alert-warning">Bạn đã huỷ thanh toán cho đơn #{{ $order->id }}.</div>
<a href="{{ route('orders.show',$order) }}" class="btn btn-outline-primary">Xem đơn</a>
@endsection
