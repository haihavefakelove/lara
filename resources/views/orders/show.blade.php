@extends('layouts.app')

@section('content')
<h1 class="page-title"><i class="bi bi-receipt me-2"></i>Chi tiết đơn #{{ $order->id }}</h1>

{{-- Thông tin đơn & người nhận --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Thông tin đơn hàng</h5>
                <p class="mb-1"><b>Mã đơn:</b> #{{ $order->id }}</p>
                <p class="mb-1"><b>Tổng tiền:</b> {{ number_format($order->total_price, 0, ',', '.') }} đ</p>
                <p class="mb-1"><b>Thanh toán:</b> {{ $order->payment_method }}</p>
                <p class="mb-1"><b>Trạng thái:</b>
                    @php
                        $map = ['processing'=>'warning','paid'=>'success','cancelled'=>'danger'];
                        $cls = $map[$order->status] ?? 'secondary';
                    @endphp
                    <span class="badge bg-{{ $cls }}">{{ ucfirst($order->status) }}</span>
                </p>
                <p class="mb-1"><b>Ngày tạo:</b> {{ $order->created_at->format('d/m/Y H:i') }}</p>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card h-100 shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Thông tin người nhận</h5>
                <p class="mb-1"><b>Số điện thoại:</b> {{ $order->phone }}</p>
                <p class="mb-1"><b>Địa chỉ:</b> {{ $order->address }}</p>
            </div>
        </div>
    </div>
</div>

{{-- Danh sách sản phẩm --}}
<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-striped align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Sản phẩm</th>
                    <th class="text-center" width="90">SL</th>
                    <th class="text-end" width="140">Đơn giá</th>
                    <th class="text-end" width="160">Thành tiền</th>
                    <th class="text-center" width="220">Đánh giá</th>
                </tr>
            </thead>
            <tbody>
                @php $sum = 0; @endphp
                @foreach($order->items as $it)
                    @php
                        $line = $it->price * $it->quantity; $sum += $line;

                        // Tìm review của user hiện tại cho item này (nếu có)
                        $review = \App\Models\Review::query()
                            ->where([
                                'order_id'      => $order->id,
                                'order_item_id' => $it->id,
                                'product_id'    => $it->product_id,
                                'user_id'       => auth()->id(),
                            ])
                            ->first();

                        $canReview = ($order->shipping_status === 'completed' || $order->payment_status === 'paid');
                    @endphp

                    <tr>
                        <td>
                            {{ optional($it->product)->name ?? 'Sản phẩm' }}
                        </td>
                        <td class="text-center">{{ $it->quantity }}</td>
                        <td class="text-end">{{ number_format($it->price, 0, ',', '.') }} đ</td>
                        <td class="text-end">{{ number_format($line, 0, ',', '.') }} đ</td>
                        <td class="text-center">
                            @if($review)
                                {{-- Đã có đánh giá -> cho phép sửa --}}
                                <a class="btn btn-sm btn-outline-primary"
                                   href="{{ route('reviews.edit', $review) }}">
                                    <i class="bi bi-pencil-square me-1"></i> Sửa đánh giá
                                </a>
                                <span class="badge bg-success ms-1">Đã đánh giá</span>
                            @else
                                @if($canReview)
                                    {{-- Chưa có đánh giá và đủ điều kiện -> tạo đánh giá --}}
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('reviews.create', [$order, $it]) }}">
                                        <i class="bi bi-star me-1"></i> Đánh giá
                                    </a>
                                @else
                                    <span class="text-muted small">Chờ hoàn tất giao hàng / thanh toán</span>
                                @endif
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-end">Tổng:</th>
                    <th class="text-end">{{ number_format($sum, 0, ',', '.') }} đ</th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

{{-- Hành động với đơn --}}
<div class="mt-3 d-flex flex-wrap gap-2">
    <a class="btn btn-outline-secondary" href="{{ route('orders.index') }}">
        <i class="bi bi-arrow-left"></i> Quay lại danh sách
    </a>

    @if($order->status === 'processing')
        <form action="{{ route('orders.cancel', $order) }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn hủy đơn này không?')">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="bi bi-x-circle"></i> Hủy đơn
            </button>
        </form>

        @if($order->payment_method === 'momo')
            <a href="{{ route('orders.momo.pay', $order) }}" class="btn btn-sm btn-success">
                💳 Thanh toán lại MoMo
            </a>
        @endif

        @if($order->payment_method === 'bank_transfer')
            <a href="{{ route('orders.bank_transfer', $order) }}" class="btn btn-sm btn-success">
                💳 Thanh toán lại
            </a>
        @endif
    @endif
</div>
@endsection
