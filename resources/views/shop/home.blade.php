@extends('layouts.app')

@section('content')
<h1 class="page-title">
    <i class="bi bi-bag me-2"></i>Chào mừng đến cửa hàng
</h1>

{{-- BỘ LỌC / TÌM KIẾM --}}
<form class="row g-2 mb-3" method="GET" action="{{ route('shop.home') }}">
    <div class="col-md-3">
        <input type="text" name="q"
               value="{{ request('q', $q ?? '') }}"
               class="form-control"
               placeholder="Tìm theo tên hoặc mã SKU...">
    </div>

    <div class="col-md-3">
        <select name="category" class="form-select">
            <option value="">-- Tất cả danh mục --</option>
            @forelse($categories ?? [] as $c)
                <option value="{{ $c->id }}" {{ request('category') == $c->id ? 'selected' : '' }}>
                    {{ $c->name }}
                </option>
            @empty
            @endforelse
        </select>
    </div>

    <div class="col-md-2">
        <input type="number" name="min_price" class="form-control"
               value="{{ request('min_price') }}"
               placeholder="Giá từ">
    </div>

    <div class="col-md-2">
        <input type="number" name="max_price" class="form-control"
               value="{{ request('max_price') }}"
               placeholder="Đến">
    </div>

    <div class="col-md-2">
        <select name="sort" class="form-select">
            <option value="newest"    {{ request('sort','newest')=='newest' ? 'selected' : '' }}>Mới nhất</option>
            <option value="price_asc" {{ request('sort')=='price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
            <option value="price_desc"{{ request('sort')=='price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
        </select>
    </div>

    <div class="col-12 d-flex gap-2">
        <button class="btn btn-outline-secondary">
            <i class="bi bi-search"></i> Tìm / Lọc
        </button>
        <a href="{{ route('shop.home') }}" class="btn btn-outline-dark">
            Xoá bộ lọc
        </a>
    </div>
</form>

{{-- DANH SÁCH SẢN PHẨM --}}
@if($products->count() > 0)
<div class="row g-3">
    @foreach($products as $p)
        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
            <div class="card h-100 shadow-sm">
                @if($p->image_url)
                    <img src="{{ $p->image_url }}" class="card-img-top" alt="{{ $p->name }}">
                @else
                    <img src="https://placehold.co/600x400?text=No+Image"
                         class="card-img-top" alt="{{ $p->name }}">
                @endif

                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-1">{{ $p->name }}</h5>
                    <div class="text-muted small mb-2">
                        Danh mục: {{ optional($p->category)->name }}
                    </div>
                    <div class="fw-bold text-danger mb-3">
                        {{ number_format($p->price,0,',','.') }} đ
                    </div>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('products.show', $p) }}"
                           class="btn btn-outline-primary flex-fill">
                            Xem chi tiết
                        </a>
                        {{-- Nút thêm vào giỏ (không chuyển trang) --}}
                        <button type="button"
                                class="btn btn-outline-success add-to-cart"
                                data-id="{{ $p->id }}"
                                title="Thêm vào giỏ">
                            <i class="bi bi-cart-plus"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="mt-3">
    {{ $products->withQueryString()->links() }}
</div>
@else
    <div class="alert alert-info">Chưa có sản phẩm nào.</div>
@endif

{{-- Toast thông báo --}}
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080">
    <div id="toastAdded" class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body">
                Đã thêm sản phẩm vào giỏ hàng!
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- Script thêm vào giỏ bằng AJAX --}}
<script>
    (function() {
        const CSRF = '{{ csrf_token() }}';

        document.querySelectorAll('.add-to-cart').forEach(function(btn) {
            btn.addEventListener('click', function () {
                const id = this.dataset.id;

                fetch("{{ url('/cart/add') }}/" + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json, text/html'
                    }
                }).then(function(res) {
                    if (res.status === 401) {
                        // chưa đăng nhập -> đưa về trang login
                        window.location.href = "{{ route('login') }}";
                        return;
                    }
                    // Hiển thị toast thông báo
                    const t = new bootstrap.Toast(document.getElementById('toastAdded'));
                    t.show();
                }).catch(function() {
                    alert('Không thể thêm vào giỏ, vui lòng thử lại!');
                });
            });
        });
    })();
</script>
@endsection
