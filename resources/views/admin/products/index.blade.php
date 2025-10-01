@extends('layouts.app')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <h1 class="page-title mb-0">
        <i class="bi bi-bag me-2"></i>Danh sách sản phẩm
    </h1>

    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Thêm sản phẩm
    </a>
</div>

{{-- Tìm kiếm: giữ nguyên hành vi cũ --}}
<form class="row g-2 mb-3" method="GET" action="">
    <div class="col-md-5 col-lg-4">
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-search"></i></span>
            <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                   placeholder="Tìm theo tên hoặc mã SKU…">
            @if(request()->filled('q'))
                <a class="btn btn-outline-secondary" href="{{ url()->current() }}"><i class="bi bi-x-lg"></i></a>
            @endif
        </div>
    </div>
    <div class="col-md-auto">
        <button class="btn btn-dark"><i class="bi bi-filter"></i> Tìm</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-nowrap">
                    <th width="70">ID</th>
                    <th>Sản phẩm</th>
                    <th>Danh mục</th>
                    <th>Thương hiệu</th>
                    <th class="text-end" width="140">Giá</th>
                    <th class="text-center" width="90">SL</th>
                    <th class="text-center" width="110">Thao tác</th>
                </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                @php $qty = (int) $p->quantity; @endphp
                <tr>
                    <td class="fw-semibold">{{ $p->id }}</td>

                    {{-- Tên + thumbnail từ accessor image_url + SKU chip --}}
                    <td>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('admin.products.edit', $p) }}" class="me-3 d-inline-block">
                                <div class="ratio ratio-1x1 rounded border overflow-hidden" style="width:52px;">
                                    @if(!empty($p->image_url))
                                        <img src="{{ $p->image_url }}" class="w-100 h-100 object-fit-cover" alt="{{ $p->name }}">
                                    @else
                                        <div class="bg-light d-flex h-100 align-items-center justify-content-center">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    @endif
                                </div>
                            </a>
                            <div class="min-w-0">
                                <a href="{{ route('admin.products.edit', $p) }}"
                                   class="fw-semibold text-decoration-none d-block text-truncate"
                                   style="max-width:420px">
                                    {{ $p->name }}
                                </a>
                                @if($p->sku)
                                    <span class="badge rounded-pill text-bg-light border mt-1">
                                        SKU: {{ $p->sku }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </td>

                    <td class="text-nowrap">{{ optional($p->category)->name ?: '—' }}</td>
                    <td class="text-nowrap">{{ $p->brand ?: '—' }}</td>

                    <td class="text-end">{{ number_format($p->price, 0, ',', '.') }} đ</td>

                    {{-- SL: badge màu cho dễ nhìn --}}
                    <td class="text-center">
                        @if($qty === 0)
                            <span class="badge text-bg-danger">Hết</span>
                        @elseif($qty <= 10)
                            <span class="badge text-bg-warning">{{ $qty }}</span>
                        @else
                            <span class="badge text-bg-success">{{ $qty }}</span>
                        @endif
                    </td>

                    {{-- Actions: dropdown gọn gàng (không đổi logic) --}}
                    <td class="text-center">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-three-dots-vertical"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.products.show', $p) }}">
                                        <i class="bi bi-eye me-2"></i> Xem
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('admin.products.edit', $p) }}">
                                        <i class="bi bi-pencil-square me-2"></i> Sửa
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('admin.products.destroy', $p) }}" method="POST"
                                          onsubmit="return confirm('Xoá sản phẩm \"{{ $p->name }}\"?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger">
                                            <i class="bi bi-trash me-2"></i> Xoá
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center py-5">
                        <i class="bi bi-inboxes fs-2 d-block mb-2 text-muted"></i>
                        <div class="text-muted">Chưa có sản phẩm nào.</div>
                        <a class="btn btn-sm btn-primary mt-2" href="{{ route('admin.products.create') }}">
                            <i class="bi bi-plus-circle me-1"></i> Thêm sản phẩm đầu tiên
                        </a>
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Phân trang: giữ nguyên cơ chế hiện tại --}}
@if(method_exists($products,'links'))
    <div class="mt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="small text-muted">
            @php
                $from  = $products->firstItem() ?? 0;
                $to    = $products->lastItem() ?? ($products->count() ?: 0);
                $total = method_exists($products,'total') ? $products->total() : $products->count();
            @endphp
            Hiển thị {{ $from }}–{{ $to }} / {{ number_format($total) }} sản phẩm
        </div>
        {{ $products->withQueryString()->links() }}
    </div>
@endif
@endsection
