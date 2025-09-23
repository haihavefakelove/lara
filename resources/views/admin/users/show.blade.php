@extends('layouts.app')

@section('content')
<h1 class="page-title">Thông tin người dùng</h1>

<div class="card shadow-sm">
    <div class="card-body">
        <p><b>ID:</b> {{ $user->id }}</p>
        <p><b>Tên:</b> {{ $user->name }}</p>
        <p><b>Email:</b> {{ $user->email }}</p>
        <p><b>Vai trò:</b> {{ $user->role }}</p>
    </div>
</div>

<a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary mt-3">← Quay lại</a>
@endsection
