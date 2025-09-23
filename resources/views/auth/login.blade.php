@extends('layouts.app')

@section('content')
<div class="container mt-4" style="max-width: 520px">
    <h3 class="mb-3">Đăng nhập</h3>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
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

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('register') }}" class="btn btn-outline-primary">Chưa có tài khoản? Đăng ký</a>
            <button class="btn btn-primary" type="submit">Đăng nhập</button>
        </div>
    </form>
</div>
@endsection
