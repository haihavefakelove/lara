@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Thêm danh mục</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST" class="mt-3">
        @csrf
        <div class="mb-3">
            <label class="form-label">Tên danh mục</label>
            <input type="text" name="name" value="{{ old('name') }}" class="form-control" required>
        </div>

        <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">Quay lại</a>
        <button type="submit" class="btn btn-primary">Lưu</button>
    </form>
</div>
@endsection
