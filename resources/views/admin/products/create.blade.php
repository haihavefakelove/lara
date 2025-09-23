@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Thêm sản phẩm mới</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.products.store') }}" method="POST">
        @csrf

        <div class="form-group">
            <label>Tên sản phẩm</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Thương hiệu</label>
            <input type="text" name="brand" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Giá</label>
            <input type="number" step="0.01" name="price" class="form-control" required>
        </div>

     <div class="mb-3">
    <label for="category_id" class="form-label">Danh mục</label>
    <select name="category_id" id="category_id" class="form-control">
        @foreach($categories as $category)
            <option value="{{ $category->id }}">{{ $category->name }}</option>
        @endforeach
    </select>
</div>


        <div class="form-group">
            <label>Số lượng</label>
            <input type="number" name="quantity" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Mã sản phẩm(SKU)</label>
            <input type="text" name="sku" class="form-control" required>
        </div>

        <div class="form-group">
            <label>Dung tích</label>
            <input type="text" name="volume" class="form-control">
        </div>

        <div class="form-group">
            <label>Tông màu</label>
            <input type="text" name="shade" class="form-control">
        </div>

        <div class="form-group">
            <label>Hạn dùng</label>
            <input type="date" name="expiry_date" class="form-control">
        </div>

        <div class="form-group">
            <label>Xuất xứ</label>
            <input type="text" name="origin" class="form-control">
        </div>

        <div class="form-group">
            <label>Loại da phù hợp</label>
            <input type="text" name="skin_type" class="form-control">
        </div>

        <div class="form-group">
            <label>Đặc điểm nổi bật</label>
            <textarea name="features" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Thành phần</label>
            <textarea name="ingredients" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Hướng dẫn sử dụng</label>
            <textarea name="usage" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Mô tả chi tiết</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <div class="form-group">
            <label>Link ảnh sản phẩm</label>
            <input type="text" name="image_url" class="form-control">
        </div>

        <button type="submit" class="btn btn-success">Lưu</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
