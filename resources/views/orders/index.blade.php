@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="page-title mb-0">
            <i class="bi bi-receipt-cutoff me-2"></i>Đơn hàng của bạn
        </h1>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if ($orders->count() > 0)
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="90">Mã đơn</th>
                            <th width="160" class="text-end">Tổng tiền</th>
                            <th width="160">Phương thức</th>
                            <th width="140">Trạng thái</th>
                            <th width="180">Ngày tạo</th>
                            <th width="100" class="text-center">Xem</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr>
                                <td>#{{ $order->id }}</td>
                                <td class="text-end">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                                <td>{{ $order->payment_method === 'COD' ? 'COD' : 'Online' }}</td>
                                <td>
                                    @php
                                        $map = [
                                            'processing' => 'warning',
                                            'paid'       => 'success',
                                            'cancelled'  => 'danger',
                                        ];
                                        $cls = $map[$order->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $cls }}">{{ ucfirst($order->status) }}</span>
                                </td>
                                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('orders.show', $order) }}"
                                       class="btn btn-sm btn-outline-primary">
                                       <i class="bi bi-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if (method_exists($orders, 'links'))
            <div class="mt-3">
                {{ $orders->links('vendor.pagination.simple-bootstrap-5') }}
            </div>
        @endif
    @else
        <div class="text-center empty">
            <i class="bi bi-inboxes fs-2 d-block"></i>
            Bạn chưa có đơn hàng nào.
            <div class="mt-2">
                <a href="{{ route('shop.home') }}" class="btn btn-primary">
                    <i class="bi bi-cart-plus"></i> Tiếp tục mua sắm
                </a>
            </div>
        </div>
    @endif
@endsection
