@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Chỉnh sửa sản phẩm</h1>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('products.update', $product) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Tên --}}
        <div class="mb-3">
            <label class="form-label">Tên sản phẩm</label>
            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $product->name) }}" required>
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Thương hiệu --}}
        <div class="mb-3">
            <label class="form-label">Thương hiệu</label>
            <input type="text" name="brand" class="form-control @error('brand') is-invalid @enderror"
                   value="{{ old('brand', $product->brand) }}">
            @error('brand')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Giá --}}
        <div class="mb-3">
            <label class="form-label">Giá</label>
            <input type="number" step="0.01" name="price" class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $product->price) }}" required>
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Danh mục --}}
        <div class="mb-3">
            <label for="category_id" class="form-label">Danh mục</label>
            <select name="category_id" id="category_id"
                    class="form-select @error('category_id') is-invalid @enderror">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Số lượng --}}
        <div class="mb-3">
            <label class="form-label">Số lượng</label>
            <input type="number" name="quantity" class="form-control @error('quantity') is-invalid @enderror"
                   value="{{ old('quantity', $product->quantity) }}" required>
            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- SKU --}}
        <div class="mb-3">
            <label class="form-label">Mã sản phẩm (SKU)</label>
            <input type="text" name="sku" class="form-control @error('sku') is-invalid @enderror"
                   value="{{ old('sku', $product->sku) }}">
            @error('sku')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        {{-- Các field khác (volume, shade, ...) --}}
        <div class="mb-3">
            <label class="form-label">Dung tích</label>
            <input type="text" name="volume" class="form-control"
                   value="{{ old('volume', $product->volume) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Tông màu</label>
            <input type="text" name="shade" class="form-control"
                   value="{{ old('shade', $product->shade) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Hạn dùng</label>
            <input type="date" name="expiry_date" class="form-control"
                   value="{{ old('expiry_date', optional($product->expiry_date)->format('Y-m-d')) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Xuất xứ</label>
            <input type="text" name="origin" class="form-control"
                   value="{{ old('origin', $product->origin) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Loại da phù hợp</label>
            <input type="text" name="skin_type" class="form-control"
                   value="{{ old('skin_type', $product->skin_type) }}">
        </div>

        <div class="mb-3">
            <label class="form-label">Đặc điểm nổi bật</label>
            <textarea name="features" class="form-control" rows="2">{{ old('features', $product->features) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Thành phần</label>
            <textarea name="ingredients" class="form-control" rows="2">{{ old('ingredients', $product->ingredients) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Hướng dẫn sử dụng</label>
            <textarea name="usage" class="form-control" rows="2">{{ old('usage', $product->usage) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Mô tả chi tiết</label>
            <textarea name="description" class="form-control" rows="3">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Link ảnh sản phẩm</label>
            <input type="text" name="image_url" class="form-control"
                   value="{{ old('image_url', $product->image_url) }}">
        </div>

        <button type="submit" class="btn btn-success">Cập nhật</button>
        <a href="{{ route('products.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
