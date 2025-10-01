@extends('layouts.app')

@section('title','Sửa đánh giá')

@section('content')
<div class="container">
  <h4 class="mb-3">Sửa đánh giá sản phẩm</h4>

  <div class="card mb-3">
    <div class="card-body d-flex">
      @if($product?->image)
        <img src="{{ asset($product->image) }}" class="me-3" style="width:80px;height:80px;object-fit:cover">
      @endif
      <div>
        <div class="fw-semibold">{{ $product->name ?? 'Sản phẩm' }}</div>
        <div class="text-muted small">Mã đánh giá #{{ $review->id }}</div>
      </div>
    </div>
  </div>

  <form method="POST" action="{{ route('reviews.update', $review) }}">
    @csrf
    @method('PUT')

    <div class="mb-3">
      <label class="form-label">Chấm điểm (1–5)</label>
      <select name="rating" class="form-select @error('rating') is-invalid @enderror">
        @for($i=5;$i>=1;$i--)
          <option value="{{ $i }}" @selected(old('rating',$review->rating)==$i)>{{ $i }} sao</option>
        @endfor
      </select>
      @error('rating')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="mb-3">
      <label class="form-label">Nhận xét</label>
      <textarea name="comment" rows="4" class="form-control @error('comment') is-invalid @enderror"
        placeholder="Cảm nhận của bạn…">{{ old('comment',$review->comment) }}</textarea>
      @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <button class="btn btn-primary">Lưu thay đổi</button>
    <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">Hủy</a>
  </form>
</div>
@endsection
