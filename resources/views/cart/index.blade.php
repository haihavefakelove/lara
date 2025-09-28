@extends('layouts.app')

@section('content')
<h1 class="page-title"><i class="bi bi-cart4 me-2"></i>Giỏ hàng của bạn</h1>

{{-- Flash message --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-2">
        <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mt-2">
        <i class="bi bi-x-circle me-2"></i> {{ session('error') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mt-2">
        <i class="bi bi-x-circle me-2"></i> {{ $errors->first() }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@php
    // Tính tổng tạm tính của giỏ
    $subtotal = 0;
    foreach ($cart as $id => $item) {
        $subtotal += (float)$item['price'] * (int)$item['quantity'];
    }

    // Lấy mã giảm giá từ session (nếu có)
    $coupon   = session('coupon');                             // ['id','code','discount']
    $discount = isset($coupon['discount']) ? (float)$coupon['discount'] : 0;
    if ($discount > $subtotal) $discount = $subtotal;

    // Tổng cần thanh toán
    $payTotal = $subtotal - $discount;
@endphp

@if(count($cart) > 0)
    <div class="card shadow-sm mt-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light">
                <tr>
                    <th>Tên sản phẩm</th>
                    <th width="130">Số lượng</th>
                    <th width="150">Giá</th>
                    <th width="150">Thành tiền</th>
                    <th width="200">Danh mục</th>
                    <th width="120">Hành động</th>
                </tr>
                </thead>
                <tbody>
                @foreach ($cart as $id => $item)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $item['name'] }}</div>
                        </td>
                        <td>
                            <form action="{{ route('cart.update', $id) }}" method="POST" class="d-flex">
                                @csrf
                                @method('PATCH')
                                <input type="number" name="quantity"
                                       class="form-control form-control-sm me-2"
                                       min="1" value="{{ $item['quantity'] }}" style="width: 80px;">
                                <button class="btn btn-sm btn-outline-secondary">Cập nhật</button>
                            </form>
                        </td>
                        <td>{{ number_format($item['price'], 0, ',', '.') }} đ</td>
                        <td>{{ number_format($item['price'] * $item['quantity'], 0, ',', '.') }} đ</td>
                        <td>{{ $item['category'] }}</td>
                        <td>
                            <form action="{{ route('cart.remove', $id) }}" method="POST"
                                  onsubmit="return confirm('Xoá sản phẩm này khỏi giỏ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i> Xoá</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        {{-- KHU VỰC ÁP MÃ GIẢM GIÁ + HIỂN THỊ TỔNG --}}
        <div class="card-body border-top">
            {{-- Áp mã giảm giá --}}
            @if(!$coupon)
                <form action="{{ route('cart.applyCoupon') }}" method="POST" class="row g-2">
                    @csrf
                    <div class="col-md-4">
                        <input type="text" name="code" class="form-control" placeholder="Nhập mã giảm giá">
                    </div>
                    <div class="col-md-auto">
                        <button class="btn btn-outline-dark"><i class="bi bi-ticket"></i> Áp dụng</button>
                    </div>
                </form>
            @else
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <span class="badge bg-success">Đã áp dụng: {{ $coupon['code'] }}</span>
                        <span class="ms-2">Giảm:
                            <strong class="text-danger">{{ number_format($discount,0,',','.') }} đ</strong>
                        </span>
                    </div>
                    <form action="{{ route('cart.removeCoupon') }}" method="POST" class="d-inline">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Huỷ mã</button>
                    </form>
                </div>
            @endif

            {{-- Tổng tiền sau khi áp mã --}}
            <div class="mt-3 text-end">
                <div>Tiền hàng:
                    <strong>{{ number_format($subtotal,0,',','.') }} đ</strong>
                </div>
                @if($discount > 0)
                    <div>Giảm giá:
                        <strong class="text-danger">-{{ number_format($discount,0,',','.') }} đ</strong>
                    </div>
                @endif
                <h4 class="mb-0">Thanh toán:
                    <span class="text-danger">{{ number_format($payTotal,0,',','.') }} đ</span>
                </h4>
            </div>
        </div>

        {{-- FORM ĐẶT HÀNG --}}
        <div class="card-body border-top">
            <form action="{{ route('order.store') }}" method="POST" class="mt-2">
                @csrf
                {{-- Controller đã tự re-calc tổng tiền --}}

                <div class="row">
                    <div class="col-md-4 mb-2">
                        <label for="phone" class="form-label">Số điện thoại</label>
                        <input type="text"
                               name="phone"
                               id="phone"
                               value="{{ old('phone') }}"
                               class="form-control @error('phone') is-invalid @enderror"
                               placeholder="VD: 0901234567" required>
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-2">
                        <label for="address" class="form-label">Địa chỉ nhận hàng</label>
                        <input type="text"
                               name="address"
                               id="address"
                               value="{{ old('address') }}"
                               class="form-control @error('address') is-invalid @enderror"
                               placeholder="Số nhà, đường, phường/xã, quận/huyện, tỉnh/thành phố" required>
                        @error('address')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row align-items-end">
                    <div class="col-md-6 mb-2">
                        <label for="payment_method" class="form-label">Phương thức thanh toán</label>
                        <select name="payment_method" id="payment_method" class="form-control" required>
                            <option value="COD"      {{ old('payment_method')==='COD'      ? 'selected' : '' }}>
                                Thanh toán khi nhận hàng (COD)
                            </option>
                            <option value="momo_qr"  {{ old('payment_method')==='momo_qr'  ? 'selected' : '' }}>
                                Thanh toán MoMo (QR)
                            </option>
                            <option value="momo_atm" {{ old('payment_method')==='momo_atm' ? 'selected' : '' }}>
                                Thanh toán Napas 247 (ATM)
                            </option>
                            <option value="bank_transfer" {{ old('payment_method')==='bank_transfer' ? 'selected' : '' }}>
                                Chuyển khoản ngân hàng (QR Code)
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-2 text-end">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-bag-check me-1"></i> Đặt hàng
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="alert alert-info mt-3">
        Giỏ hàng của bạn trống.
    </div>
@endif

{{-- Link điều hướng --}}
<a href="{{ route('shop.home') }}" class="btn btn-primary">Tiếp tục mua sắm</a>
<a href="{{ route('orders.index') }}" class="btn btn-outline-primary ms-2">
    <i class="bi bi-receipt"></i> Đơn hàng của tôi
</a>
@endsection
