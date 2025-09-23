@extends('layouts.app')

@section('content')
<h1 class="page-title"><i class="bi bi-receipt-cutoff me-2"></i>Chi tiết đơn #{{ $order->id }}</h1>

<div class="card shadow-sm mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <div><b>Khách:</b> {{ $order->name }}</div>
                <div><b>Điện thoại:</b> {{ $order->phone }}</div>
                <div><b>Địa chỉ:</b> {{ $order->address }}</div>
            </div>
            <div class="col-md-4">
                <div><b>Tổng tiền:</b> {{ number_format($order->total_price,0,',','.') }} đ</div>
                <div><b>Thanh toán:</b> {{ $order->status }}</div>
                <div><b>Giao hàng:</b> {{ $order->shipping_status }}</div>
            </div>
            <div class="col-md-4">
                <div><b>Ngày tạo:</b> {{ $order->created_at->format('d/m/Y H:i') }}</div>
                @if($order->momo_order_id)
                    <div><b>MoMo order:</b> {{ $order->momo_order_id }}</div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Sản phẩm</th>
                    <th>SL</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $k => $it)
                    <tr>
                        <td>{{ $k+1 }}</td>
                        <td>{{ $it->product->name ?? ('#'.$it->product_id) }}</td>
                        <td>{{ $it->quantity }}</td>
                        <td>{{ number_format($it->price,0,',','.') }} đ</td>
                        <td>{{ number_format($it->price * $it->quantity,0,',','.') }} đ</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
