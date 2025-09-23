@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width: 520px">
    <h3 class="mb-3">Đăng ký</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf  {{-- BẮT BUỘC --}}
        <div class="form-group mb-3">
            <label for="name">Họ tên</label>
            <input required type="text" name="name" id="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}">
            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group mb-3">
            <label for="email">Email</label>
            <input required type="email" name="email" id="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email') }}">
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group mb-3">
            <label for="password">Mật khẩu</label>
            <input required type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror">
            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="form-group mb-3">
            <label for="password_confirmation">Xác nhận mật khẩu</label>
            <input required type="password" name="password_confirmation" id="password_confirmation" class="form-control">
        </div>

        <button class="btn btn-primary">Đăng ký</button>
    </form>
</div>
@endsection
