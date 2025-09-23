@extends('layouts.app')

@section('content')
<div class="container mt-3" style="max-width: 850px">
    <h3 class="mb-3"><i class="bi bi-credit-card me-1"></i>Thanh toán đơn hàng</h3>

    <form action="{{ route('payment.process') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Họ tên</label>
                <input name="name" class="form-control" required value="{{ old('name', auth()->user()->name) }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Số điện thoại</label>
                <input name="phone" class="form-control" required value="{{ old('phone') }}">
            </div>
            <div class="col-12">
                <label class="form-label">Địa chỉ giao hàng</label>
                <input name="address" class="form-control" required value="{{ old('address') }}">
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header"><b>Giỏ hàng</b></div>
            <ul class="list-group list-group-flush">
                @php $total = 0; @endphp
                @foreach($cart as $item)
                    @php $line = $item['price'] * $item['quantity']; $total += $line; @endphp
                    <li class="list-group-item d-flex justify-content-between">
                        <div>{{ $item['name'] }} <small class="text-muted">(x{{ $item['quantity'] }})</small></div>
                        <div>{{ number_format($line,0,',','.') }} đ</div>
                    </li>
                @endforeach
                <li class="list-group-item text-end">
                    <strong>Tổng cộng: {{ number_format($total,0,',','.') }} đ</strong>
                </li>
            </ul>
            <input type="hidden" name="total_price" value="{{ $total }}">
        </div>

        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-success" name="payment_method" value="momo">
                <i class="bi bi-phone me-1"></i>Thanh toán MoMo
            </button>
            <button class="btn btn-secondary" name="payment_method" value="cod">
                <i class="bi bi-cash-coin me-1"></i>Thanh toán COD
            </button>
        </div>
    </form>
</div>
@endsection
