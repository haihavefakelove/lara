{{-- resources/views/products/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-5">
        <div class="card shadow-sm">
            @if($product->image_url)
                <img src="{{ $product->image_url }}"
                     alt="{{ $product->name }}"
                     class="card-img-top"
                     style="object-fit:cover;max-height:420px">
            @else
                <img src="https://via.placeholder.com/600x420?text=No+Image"
                     alt="{{ $product->name }}"
                     class="card-img-top">
            @endif

            <div class="card-body">
                <h3 class="card-title mb-1">{{ $product->name }}</h3>

                <p class="text-muted mb-1">
                    Danh mục:
                    <span class="badge bg-light text-dark">
                        {{ optional($product->category)->name ?? 'Chưa phân loại' }}
                    </span>
                </p>

                <p class="fw-bold fs-5 text-danger mb-2">
                    {{ number_format($product->price, 0, ',', '.') }} đ
                </p>

                {{-- Form thêm vào giỏ hàng --}}
                @auth
                    <form method="POST" action="{{ route('cart.add', $product) }}" class="d-flex">
                        @csrf
                        <input type="number" name="quantity" value="1" min="1" class="form-control me-2" style="max-width:120px">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-cart-plus me-1"></i>Thêm vào giỏ
                        </button>
                    </form>
                @else
                    <div class="alert alert-info mb-0">
                        Vui lòng <a href="{{ route('login') }}" class="alert-link">đăng nhập</a> để thêm sản phẩm vào giỏ hàng.
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="mb-3">Thông tin chi tiết</h5>

                <dl class="row mb-0">
                    <dt class="col-sm-4">Thương hiệu</dt>
                    <dd class="col-sm-8">{{ $product->brand }}</dd>

                    <dt class="col-sm-4">Số lượng còn</dt>
                    <dd class="col-sm-8">{{ $product->quantity }}</dd>

                    <dt class="col-sm-4">Mã SKU</dt>
                    <dd class="col-sm-8">{{ $product->sku }}</dd>

                    <dt class="col-sm-4">Dung tích</dt>
                    <dd class="col-sm-8">{{ $product->volume }}</dd>

                    <dt class="col-sm-4">Tông màu</dt>
                    <dd class="col-sm-8">{{ $product->shade }}</dd>

                    <dt class="col-sm-4">Hạn dùng</dt>
                    <dd class="col-sm-8">{{ $product->expiry_date }}</dd>

                    <dt class="col-sm-4">Xuất xứ</dt>
                    <dd class="col-sm-8">{{ $product->origin }}</dd>

                    <dt class="col-sm-4">Loại da phù hợp</dt>
                    <dd class="col-sm-8">{{ $product->skin_type }}</dd>

                    <dt class="col-sm-4">Đặc điểm nổi bật</dt>
                    <dd class="col-sm-8">{{ $product->features }}</dd>

                    <dt class="col-sm-4">Thành phần</dt>
                    <dd class="col-sm-8">{{ $product->ingredients }}</dd>

                    <dt class="col-sm-4">Hướng dẫn sử dụng</dt>
                    <dd class="col-sm-8">{{ $product->usage }}</dd>

                    <dt class="col-sm-4">Mô tả</dt>
                    <dd class="col-sm-8">{{ $product->description }}</dd>
                </dl>

                <a href="{{ route('shop.home') }}" class="btn btn-outline-secondary mt-3">
                    <i class="bi bi-arrow-left"></i> Tiếp tục mua sắm
                </a>

                <div class="mb-2 mt-3">
                    <strong>Đánh giá trung bình:</strong> {{ $product->avgRating() }} / 5
                    ({{ $product->reviews()->count() }} lượt)
                </div>

                <hr/>
                <h5>Phản hồi</h5>
                @forelse($product->reviews()->latest()->get() as $rv)
                    <div class="mb-3">
                        <div class="small text-muted">
                            {{ $rv->user->name }} • {{ $rv->created_at->format('d/m/Y H:i') }}
                        </div>
                        <div>
                            @for($i=1;$i<=5;$i++)
                                <i class="bi {{ $i <= $rv->rating ? 'bi-star-fill text-warning' : 'bi-star text-secondary' }}"></i>
                            @endfor
                        </div>
                        <div>{{ $rv->comment }}</div>
                    </div>
                @empty
                    <div class="text-muted">Chưa có đánh giá nào.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- Khối gợi ý sản phẩm (không ảnh hưởng khi controller chưa truyền biến) --}}
@isset($recommendations)
    @include('products.partials.recommendations', ['products' => $recommendations])
@endisset
@endsection
