@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Quản lý danh mục</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('categories.create') }}" class="btn btn-primary mb-3">+ Thêm mới</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th width="60">#</th>
                <th>Tên</th>
                <th width="180">Thao tác</th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>{{ $category->name }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-sm btn-warning">Sửa</a>

                        <form action="{{ route('categories.destroy', $category) }}" method="POST"
                              style="display:inline-block"
                              onsubmit="return confirm('Xóa danh mục này?');">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-danger" type="submit">Xóa</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center">Chưa có danh mục.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Hiển thị phân trang nếu có --}}
    <div class="d-flex justify-content-center">
        {{ $categories->links() }}
    </div>
</div>
@endsection
