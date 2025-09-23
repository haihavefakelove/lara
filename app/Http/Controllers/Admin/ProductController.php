<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;   // import Controller gốc
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Áp dụng middleware cho toàn bộ controller
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Danh sách sản phẩm (ADMIN)
     */
    public function index()
    {
        // có thể áp dụng filter ở đây nếu muốn
        $products = Product::with('category')->latest()->paginate(10);

        return view('admin.products.index', compact('products'));
    }

    /**
     * Hiển thị form tạo sản phẩm (ADMIN)
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.create', compact('categories'));
    }

    /**
     * Lưu sản phẩm mới vào DB (ADMIN)
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'  => ['required', 'exists:categories,id'],
            'name'         => ['required', 'string', 'max:255'],
            'brand'        => ['nullable', 'string', 'max:255'],
            'price'        => ['required', 'numeric', 'min:0'],
            'quantity'     => ['required', 'integer', 'min:0'],
            'sku'          => ['nullable', 'string', 'max:255'],
            'volume'       => ['nullable', 'string', 'max:255'],
            'shade'        => ['nullable', 'string', 'max:255'],
            'expiry_date'  => ['nullable', 'date'],
            'origin'       => ['nullable', 'string', 'max:255'],
            'ingredients'  => ['nullable', 'string'],
            'skin_type'    => ['nullable', 'string', 'max:255'],
            'features'     => ['nullable', 'string'],
            'usage'        => ['nullable', 'string'],
            'description'  => ['nullable', 'string'],
            'image_url'    => ['nullable', 'string', 'max:255'],
        ]);

        Product::create($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Thêm sản phẩm thành công!');
    }

    /**
     * Hiển thị chi tiết sản phẩm (ADMIN) – tùy chọn
     */
    public function show(Product $product)
    {
        $product->load('category');

        return view('admin.products.show', compact('product'));
    }

    /**
     * Hiển thị form sửa sản phẩm (ADMIN)
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    /**
     * Cập nhật sản phẩm (ADMIN)
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
             'category_id'  => ['required', 'exists:categories,id'],
            'name'         => ['required','string','max:255'],
            'brand'        => ['nullable','string','max:255'],
            'price'        => ['required','numeric'],
            'quantity'     => ['required','integer'],
            'sku'          => ['nullable','string','max:255'],
            'volume'       => ['nullable','string','max:255'],
            'shade'        => ['nullable','string','max:255'],
            'expiry_date'  => ['nullable','date'],
            'origin'       => ['nullable','string','max:255'],
            'skin_type'    => ['nullable','string','max:255'],
            'features'     => ['nullable','string'],
            'ingredients'  => ['nullable','string'],
            'usage'        => ['nullable','string'],
            'description'  => ['nullable','string'],
            'image_url'    => ['nullable','string','max:255'],
        ]);

        $product->update($data);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Cập nhật sản phẩm thành công!');
    }

    /**
     * Xoá sản phẩm (ADMIN)
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã xoá sản phẩm!');
    }
}
