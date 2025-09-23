@extends('layouts.app')

@section('content')
    <h1 class="page-title">
        <i class="bi bi-pencil-square me-2"></i> Sửa mã giảm giá
    </h1>

    <form action="{{ route('admin.coupons.update', $coupon) }}" method="POST" class="card p-3 shadow-sm">
        @csrf
        @method('PUT')

        {{-- Mã giảm giá --}}
        <div class="mb-3">
            <label for="code" class="form-label">Mã</label>
            <input type="text" id="code" name="code" class="form-control @error('code') is-invalid @enderror"
                   value="{{ old('code', $coupon->code) }}" placeholder="VD: SALE10" required>
            @error('code')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Kiểu giảm giá: percent/fixed --}}
        <div class="mb-3">
            <label for="type" class="form-label">Kiểu giảm</label>
            <select id="type" name="type" class="form-select @error('type') is-invalid @enderror">
                <option value="percent" {{ old('type', $coupon->type) == 'percent' ? 'selected' : '' }}>Theo %</option>
                <option value="fixed"   {{ old('type', $coupon->type) == 'fixed'   ? 'selected' : '' }}>Số tiền cố định</option>
            </select>
            @error('type')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Giá trị giảm --}}
        <div class="mb-3">
            <label for="value" class="form-label">Giá trị</label>
            <input type="number" id="value" name="value" class="form-control @error('value') is-invalid @enderror"
                   value="{{ old('value', $coupon->value) }}" min="0" step="1" required>
            <div class="form-text">Nếu chọn theo %, nhập 1~100. Nếu chọn số tiền, nhập số tiền giảm.</div>
            @error('value')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Số lần dùng tối đa --}}
        <div class="mb-3">
            <label for="max_uses" class="form-label">Số lần sử dụng tối đa</label>
            <input type="number" id="max_uses" name="max_uses"
                   class="form-control @error('max_uses') is-invalid @enderror"
                   value="{{ old('max_uses', $coupon->max_uses) }}" min="0" step="1">
            <div class="form-text">Để 0 nếu không giới hạn.</div>
            @error('max_uses')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Ngày bắt đầu/kết thúc --}}
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="starts_at" class="form-label">Bắt đầu</label>
                <input type="date" id="starts_at" name="starts_at"
                       class="form-control @error('starts_at') is-invalid @enderror"
                       value="{{ old('starts_at', optional($coupon->starts_at)->format('Y-m-d')) }}">
                @error('starts_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-6 mb-3">
                <label for="expires_at" class="form-label">Kết thúc</label>
                <input type="date" id="expires_at" name="expires_at"
                       class="form-control @error('expires_at') is-invalid @enderror"
                       value="{{ old('expires_at', optional($coupon->expires_at)->format('Y-m-d')) }}">
                @error('expires_at')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        {{-- Trạng thái --}}
        <div class="mb-3">
            <label for="is_active" class="form-label">Trạng thái</label>
            <select id="is_active" name="is_active" class="form-select @error('is_active') is-invalid @enderror">
                <option value="1" {{ old('is_active', $coupon->is_active) ? 'selected' : '' }}>Kích hoạt</option>
                <option value="0" {{ old('is_active', $coupon->is_active) ? '' : 'selected' }}>Tạm tắt</option>
            </select>
            @error('is_active')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Nút --}}
        <div class="mt-3 d-flex gap-2">
            <button class="btn btn-primary"><i class="bi bi-check2-circle me-1"></i> Cập nhật</button>
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left-short me-1"></i> Quay lại danh sách
            </a>
        </div>
    </form>
@endsection
