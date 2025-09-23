@extends('layouts.app')

@section('content')
<h1 class="page-title">Thêm người dùng</h1>

<form action="{{ route('admin.users.store') }}" method="POST" class="card shadow-sm p-3">
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Tên</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Mật khẩu</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Vai trò</label>
            <select name="role" class="form-select">
                <option value="customer">Khách hàng</option>
                <option value="admin">Quản trị</option>
                <option value="user">User</option>
            </select>
        </div>
    </div>
    <div class="mt-3">
        <button class="btn btn-success">Lưu</button>
        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Huỷ</a>
    </div>
</form>
@endsection
