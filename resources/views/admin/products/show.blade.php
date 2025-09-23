@extends('layouts.app')

@section('content')
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="page-title mb-0">
            <i class="bi bi-bag-check me-2"></i> Chi tiết sản phẩm
        </h1>

        <div>
            <a href="{{ route('admin.products.edit', $product) }}" class="btn btn-warning">
                <i class="bi bi-pencil-square me-1"></i> Sửa
            </a>
            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left-circle me-1"></i> Quay lại
            </a>
            {{-- Nếu muốn xem trang công khai dành cho khách --}}
            @if(Route::has('products.show'))
                <a href="{{ route('products.show', $product->id) }}" class="btn btn-outline-primary" target="_blank">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Xem trang công khai
                </a>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row g-4">
                {{-- Hình ảnh sản phẩm --}}
                <div class="col-md-4">
                    @php
                        $img = $product->image_url ?: 'https://via.placeholder.com/480x320?text=No+Image';
                    @endphp
                    <img src="{{ $img }}"
                         alt="{{ $product->name }}"
                         class="img-fluid rounded border">
                </div>

                {{-- Thông tin chi tiết --}}
                <div class="col-md-8">
                    <h3 class="fw-bold mb-2">{{ $product->name }}</h3>
                    <p class="text-muted mb-3">
                        <i class="bi bi-tags me-1"></i>Danh mục:
                        <strong>{{ optional($product->category)->name }}</strong>
                    </p>

                    <div class="row">
                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <b>Thương hiệu:</b>
                                    <span class="ms-1">{{ $product->brand }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Giá:</b>
                                    <span class="ms-1 text-danger fw-semibold">
                                        {{ number_format($product->price, 0, ',', '.') }} đ
                                    </span>
                                </li>
                                <li class="list-group-item">
                                    <b>Số lượng:</b>
                                    <span class="ms-1">{{ $product->quantity }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>SKU:</b>
                                    <span class="ms-1">{{ $product->sku }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Dung tích:</b>
                                    <span class="ms-1">{{ $product->volume }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Tông màu:</b>
                                    <span class="ms-1">{{ $product->shade }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Hạn dùng:</b>
                                    <span class="ms-1">{{ $product->expiry_date }}</span>
                                </li>
                            </ul>
                        </div>

                        <div class="col-md-6">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item">
                                    <b>Xuất xứ:</b>
                                    <span class="ms-1">{{ $product->origin }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Loại da phù hợp:</b>
                                    <span class="ms-1">{{ $product->skin_type }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Đặc điểm:</b>
                                    <span class="ms-1">{{ $product->features }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Thành phần:</b>
                                    <span class="ms-1">{{ $product->ingredients }}</span>
                                </li>
                                <li class="list-group-item">
                                    <b>Hướng dẫn sử dụng:</b>
                                    <span class="ms-1">{{ $product->usage }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Mô tả chi tiết --}}
                    @if($product->description)
                        <div class="mt-4">
                            <h5 class="fw-bold"><i class="bi bi-file-earmark-text me-1"></i>Mô tả</h5>
                            <p class="mb-0">{{ $product->description }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
