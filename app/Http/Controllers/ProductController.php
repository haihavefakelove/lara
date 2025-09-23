<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;              // <- thêm
use App\Services\RecommendationService;          // <- thêm

class ProductController extends Controller
{
    // ====== ADMIN ======
    public function index()
    {
        // Giữ nguyên hành vi cũ (không thay đổi tham số truyền ra view)
        $products = Product::all();
        return view('admin.products.index');
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'brand'       => ['nullable','string','max:255'],
            'quantity'    => ['required','integer','min:0'],
            'price'       => ['required','numeric','min:0'],
            'sku'         => ['nullable','string','max:100','unique:products,sku'],
            'shade'       => ['nullable','string','max:255'],
            'volume'      => ['nullable','string','max:255'],
            'expiry_date' => ['nullable','date'],
            'origin'      => ['nullable','string','max:255'],
            'skin_type'   => ['nullable','string','max:255'],
            'features'    => ['nullable','string'],
            'ingredients' => ['nullable','string'],
            'usage'       => ['nullable','string'],
            'description' => ['nullable','string'],
            'image_url'   => ['nullable','string','max:255'],
        ]);

        Product::create($data);
        return redirect()->route('admin.products.index')->with('success','Tạo sản phẩm thành công.');
    }

    public function show(Product $product) // show trong ADMIN
    {
        $product->load('category');
        return view('admin.products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.products.edit', compact('product','categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'category_id' => ['required','exists:categories,id'],
            'name'        => ['required','string','max:255'],
            'brand'       => ['nullable','string','max:255'],
            'quantity'    => ['required','integer','min:0'],
            'price'       => ['required','numeric','min:0'],
            'sku'         => ['nullable','string','max:100','unique:products,sku,'.$product->id],
            'shade'       => ['nullable','string','max:255'],
            'volume'      => ['nullable','string','max:255'],
            'expiry_date' => ['nullable','date'],
            'origin'      => ['nullable','string','max:255'],
            'skin_type'   => ['nullable','string','max:255'],
            'features'    => ['nullable','string'],
            'ingredients' => ['nullable','string'],
            'usage'       => ['nullable','string'],
            'description' => ['nullable','string'],
            'image_url'   => ['nullable','string','max:255'],
        ]);

        $product->update($data);
        return redirect()->route('admin.products.index')->with('success','Cập nhật thành công.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('admin.products.index')->with('success','Đã xóa sản phẩm.');
    }

    // ====== PUBLIC (customer) ======
    public function show_normal(Product $product)
    {
        // Nạp quan hệ cần thiết
        $product->load('category');

        // Gọi service gợi ý (ưu tiên theo user nếu đã đăng nhập)
        $recommendations = app(RecommendationService::class)
            ->forProduct(Auth::id(), $product->id, 8);

        // Trả về view kèm danh sách gợi ý
        return view('products.show', [
            'product'         => $product,
            'recommendations' => $recommendations,
        ]);
    }
}
