@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="page-title mb-0">
            <i class="bi bi-receipt me-2"></i>Quản lý đơn hàng
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-x-circle me-1"></i> {{ session('error') }}
            <button class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($orders->count())
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                    <tr>
                        <th width="60">#</th>
                        <th width="90">Mã đơn</th>
                        <th>Khách hàng</th>
                        <th width="140">SĐT</th>
                        <th>Địa chỉ</th>
                        <th width="140">Tổng tiền</th>
                        <th width="140">Thanh toán</th>
                        <th width="240">Giao hàng</th>
                        <th width="170">Ngày đặt</th>
                        <th width="70"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($orders as $i => $order)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>#{{ $order->id }}</td>
                            <td>{{ $order->name }}</td>
                            <td>{{ $order->phone }}</td>
                            <td>{{ $order->address }}</td>

                            <td class="fw-semibold text-danger">
                                {{ number_format($order->total_price, 0, ',', '.') }} đ
                            </td>

                            {{-- Cột thanh toán (đọc từ payment_status nếu có, fallback về status) --}}
                            <td>
                                @php
                                    $pm = $order->payment_status ?? $order->status;
                                @endphp

                                @switch($pm)
                                    @case('paid')
                                        <span class="badge bg-success text-uppercase">paid</span>
                                        @break
                                    @case('cancelled')
                                        <span class="badge bg-danger text-uppercase">cancelled</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger text-uppercase">failed</span>
                                        @break
                                    @case('unpaid')
                                        <span class="badge bg-secondary text-uppercase">unpaid</span>
                                        @break
                                    @default
                                        <span class="badge bg-secondary text-uppercase">{{ $pm }}</span>
                                @endswitch
                            </td>

                            {{-- Cột giao hàng: select cập nhật --}}
                            <td style="min-width: 240px">
                                <form action="{{ route('admin.orders.update', $order) }}"
                                      method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')

                                    <select name="shipping_status"
                                            class="form-select form-select-sm"
                                            onchange="this.form.submit()">
                                        @foreach([
                                            'not_shipped' => 'Chưa giao',
                                            'packaged'    => 'Đã đóng gói',
                                            'shipping'    => 'Đang giao',
                                            'completed'   => 'Hoàn tất',
                                            'cancelled'   => 'Huỷ',
                                        ] as $key => $label)
                                            <option value="{{ $key }}" @selected($order->shipping_status == $key)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>

                            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>

                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-info"
                                   href="{{ route('admin.orders.show', $order) }}"
                                   title="Xem chi tiết">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @else
        <p>Không có đơn hàng nào.</p>
    @endif
@endsection
