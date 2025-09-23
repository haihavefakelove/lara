@extends('layouts.app')
@section('content')
<h1 class="page-title"><i class="bi bi-plus-lg me-2"></i>Tạo mã giảm giá</h1>
<form action="{{ route('admin.coupons.store') }}" method="POST" class="card p-3 shadow-sm">
@csrf
<div class="row g-3">
    <div class="col-md-3">
        <label class="form-label">Mã</label>
        <input type="text" name="code" value="{{ old('code') }}" class="form-control" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Loại</label>
        <select name="type" class="form-select">
            <option value="percent">Phần trăm</option>
            <option value="fixed">Số tiền</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Giá trị</label>
        <input type="number" step="0.01" min="0" name="value" class="form-control" value="{{ old('value',0) }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Đơn tối thiểu</label>
        <input type="number" step="0.01" min="0" name="min_order" value="{{ old('min_order') }}" class="form-control">
    </div>

    <div class="col-md-3">
        <label class="form-label">Giới hạn lượt dùng</label>
        <input type="number" min="1" name="max_uses" value="{{ old('max_uses') }}" class="form-control">
    </div>
    <div class="col-md-3">
        <label class="form-label">Bắt đầu</label>
        <input type="date" name="start_at" class="form-control" value="{{ old('start_at') }}">
    </div>
    <div class="col-md-3">
        <label class="form-label">Kết thúc</label>
        <input type="date" name="end_at" class="form-control" value="{{ old('end_at') }}">
    </div>
    <div class="col-md-3 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="is_active" value="1" checked id="is_active">
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
