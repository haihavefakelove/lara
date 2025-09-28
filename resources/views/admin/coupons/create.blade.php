@extends('layouts.app')
@section('content')
<h1 class="page-title"><i class="bi bi-plus-lg me-2"></i>Tạo mã giảm giá</h1>

<form action="{{ route('admin.coupons.store') }}" method="POST" class="card p-3 shadow-sm">
@csrf
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Mã</label>
        <input type="text" name="code"
               value="{{ old('code') }}"
               class="form-control @error('code') is-invalid @enderror" required>
        @error('code')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Loại</label>
        <select name="type" class="form-select @error('type') is-invalid @enderror">
            <option value="percent" {{ old('type')==='percent' ? 'selected' : '' }}>Phần trăm</option>
            <option value="fixed"   {{ old('type')==='fixed' ? 'selected' : '' }}>Số tiền</option>
        </select>
        @error('type')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Giá trị</label>
        <input type="number" step="0.01" min="0"
               name="value" id="value"
               class="form-control @error('value') is-invalid @enderror"
               value="{{ old('value', 0) }}">
        @error('value')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Đơn tối thiểu</label>
        <input type="number" step="0.01" min="0"
               name="min_order" id="min_order"
               value="{{ old('min_order') }}"
               class="form-control @error('min_order') is-invalid @enderror"
               placeholder="VND">
        @error('min_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Đơn tối đa</label>
        <input type="number" step="0.01" min="0"
               name="max_order" id="max_order"
               value="{{ old('max_order') }}"
               class="form-control @error('max_order') is-invalid @enderror"
               placeholder="VND">
        @error('max_order')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Giới hạn lượt dùng</label>
        <input type="number" min="1"
               name="max_uses"
               value="{{ old('max_uses') }}"
               class="form-control @error('max_uses') is-invalid @enderror">
        @error('max_uses')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Bắt đầu</label>
        <input type="date" name="start_at"
               class="form-control @error('start_at') is-invalid @enderror"
               value="{{ old('start_at') }}">
        @error('start_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3">
        <label class="form-label">Kết thúc</label>
        <input type="date" name="end_at"
               class="form-control @error('end_at') is-invalid @enderror"
               value="{{ old('end_at') }}">
        @error('end_at')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1"
                   {{ old('is_active', true) ? 'checked' : '' }} id="is_active">
            <label class="form-check-label" for="is_active">Kích hoạt</label>
        </div>
    </div>
</div>

<div class="mt-3">
    <button class="btn btn-primary">Lưu</button>
    <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">Huỷ</a>
</div>
</form>
@endsection
