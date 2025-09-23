@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chi tiết Category</h1>
    <p><strong>ID:</strong> {{ $category->id }}</p>
    <p><strong>Tên:</strong> {{ $category->name }}</p>
    <p><strong>Ngày tạo:</strong> {{ $category->created_at->format('d/m/Y H:i') }}</p>
    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Quay lại</a>
</div>
@endsection
