@extends('layouts.app')

@section('content')
<div class="d-flex align-items-center justify-content-between mb-3">
    <h1 class="page-title mb-0"><i class="bi bi-bag me-2"></i>Danh sách sản phẩm</h1>

    <div>
        <a href="{{ route('products.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Thêm sản phẩm
        </a>
    </div>
</div>

{{-- Thanh tìm kiếm (tuỳ chọn, giữ nguyên để không ảnh hưởng các chức năng khác) --}}
<form class="row g-2 mb-3" method="GET" action="">
    <div class="col-md-4">
        <input type="text" name="q" value="{{ request('q') }}" class="form-control"
               placeholder="Tìm theo tên hoặc mã SKU…">
    </div>
    <div class="col-md-auto">
        <button class="btn btn-outline-secondary"><i class="bi bi-search"></i> Tìm</button>
    </div>
</form>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th width="60">ID</th>
                <th>Tên</th>
                <th>Danh mục</th>
                <th>Thương hiệu</th>
                <th class="text-end" width="140">Giá</th>
                <th class="text-center" width="60">SL</th>
                <th class="text-center" width="200">Thao tác</th>
            </tr>
            </thead>
            <tbody>
            @forelse($products as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>
                        {{-- giữ nguyên link tên để chỉnh sửa nhanh --}}
                        <a href="{{ route('products.edit', $p) }}" class="fw-semibold text-decoration-none">
                            {{ $p->name }}
                        </a>
                        @if($p->sku)
                            <span class="badge badge-soft ms-1">{{ $p->sku }}</span>
                        @endif
                    </td>
                    <td>{{ optional($p->category)->name }}</td>
                    <td>{{ $p->brand }}</td>
                    <td class="text-end">{{ number_format($p->price,0,',','.') }} đ</td>
                    <td class="text-center">{{ $p->quantity }}</td>
                    <td class="text-center">
                        {{-- Nút XEM mới thêm --}}
                        <a href="{{ route('products.show', $p) }}" class="btn btn-info btn-sm me-1">
                            <i class="bi bi-eye"></i> Xem
                        </a>

                        {{-- Sửa --}}
                        <a href="{{ route('products.edit', $p) }}" class="btn btn-warning btn-sm me-1">
                            <i class="bi bi-pencil-square"></i> Sửa
                        </a>

                        {{-- Xoá --}}
                        <form action="{{ route('products.destroy', $p) }}"
                              method="POST" class="d-inline"
                              onsubmit="return confirm('Xoá sản phẩm này?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm">
                                <i class="bi bi-trash"></i> Xoá
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center empty">
                        <i class="bi bi-inboxes fs-2 d-block"></i>
                        Chưa có sản phẩm nào.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Phân trang (nếu có) --}}
@if(method_exists($products,'links'))
    <div class="mt-3">
        {{ $products->withQueryString()->links() }}
    </div>
@endif
@endsection
