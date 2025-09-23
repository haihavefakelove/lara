@extends('layouts.app')

@section('content')
    <h1 class="page-title">
        <i class="bi bi-star-half me-2"></i> Đánh giá sản phẩm
    </h1>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="mb-3">
                <div class="fw-semibold">{{ $product->name }}</div>
                @if($product->image_url)
                    <img src="{{ $product->image_url }}" alt="" class="img-fluid" style="max-height:120px">
                @endif
            </div>

            <form action="{{ route('reviews.store', [$order, $item]) }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Chấm điểm (1–5)</label>
                    <select name="rating" class="form-select @error('rating') is-invalid @enderror">
                        @for($i=5;$i>=1;$i--)
                            <option value="{{ $i }}" {{ old('rating')==$i?'selected':'' }}>{{ $i }} sao</option>
                        @endfor
                    </select>
                    @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label">Nhận xét</label>
                    <textarea name="comment" rows="4" class="form-control @error('comment') is-invalid @enderror" placeholder="Nhận xét của bạn...">{{ old('comment') }}</textarea>
                    @error('comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="d-flex gap-2">
                    <button class="btn btn-primary"><i class="bi bi-send me-1"></i> Gửi đánh giá</button>
                    <a href="{{ route('orders.show', $order) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left-short me-1"></i> Quay lại đơn hàng
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
