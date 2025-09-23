@extends('layouts.app')

@section('content')
<h1 class="page-title">👥 Quản lý người dùng</h1>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-2">
        {{ session('success') }}
        <button class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<a href="{{ route('admin.users.create') }}" class="btn btn-success mb-3">+ Thêm người dùng</a>

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th>ID</th><th>Tên</th><th>Email</th><th>Vai trò</th><th></th></tr>
            </thead>
            <tbody>
            @forelse($users as $u)
                <tr>
                    <td>{{ $u->id }}</td>
                    <td>{{ $u->name }}</td>
                    <td>{{ $u->email }}</td>
                    <td>{{ $u->role }}</td>
                    <td class="text-nowrap">
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('admin.users.edit',$u) }}">Sửa</a>
                        <a class="btn btn-sm btn-outline-info" href="{{ route('admin.users.show',$u) }}">Xem</a>
                        <form action="{{ route('admin.users.destroy',$u) }}" method="POST" class="d-inline">
                            @csrf @method('DELETE')
                            <button onclick="return confirm('Xoá người dùng này?')" class="btn btn-sm btn-outline-danger">
                                Xoá
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Không có người dùng nào.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
