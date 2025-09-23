<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        // Lấy tham số lọc từ query
        $q         = trim($request->get('q', ''));
        $catId     = $request->get('category');
        $minPrice  = $request->get('min_price');
        $maxPrice  = $request->get('max_price');
        $sort      = $request->get('sort', 'newest');   // newest|price_asc|price_desc

        $products = Product::query()
            ->with('category')

            // Tìm theo tên hoặc SKU
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('name', 'like', "%{$q}%")
                       ->orWhere('sku', 'like', "%{$q}%");
                });
            })

            // Lọc theo danh mục
            ->when(!empty($catId), function ($query) use ($catId) {
                $query->where('category_id', $catId);
            })

            // Lọc theo khoảng giá
            ->when($minPrice !== null && $minPrice !== '', function ($query) use ($minPrice) {
                $query->where('price', '>=', (float)$minPrice);
            })
            ->when($maxPrice !== null && $maxPrice !== '', function ($query) use ($maxPrice) {
                $query->where('price', '<=', (float)$maxPrice);
            });

        // Sắp xếp
        switch ($sort) {
            case 'price_asc':
                $products->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $products->orderBy('price', 'desc');
                break;
            case 'newest':
            default:
                $products->latest();
                break;
        }

        $products   = $products->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();

        return view('shop.home', compact('products', 'q', 'categories'));
    }
}
