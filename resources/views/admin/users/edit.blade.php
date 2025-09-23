@extends('layouts.app')

@section('content')
<h1 class="page-title">Chỉnh sửa người dùng</h1>

<form action="{{ route('admin.users.update',$user) }}" method="POST" class="card shadow-sm p-3">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Tên</label>
            <input type="text" name="name" value="{{ $user->name }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" value="{{ $user->email }}" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select">
                <option value="customer" @selected($user->role === 'customer')>Khách hàng</option>
                <option value="admin" @selected($user->role === 'admin')>Quản trị</option>
                <option value="user" @selected($user->role === 'user')>User</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Quay lại</a>
    </div>
</form>
@endsection
