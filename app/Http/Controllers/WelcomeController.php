<?php

namespace App\Http\Controllers;

use App\Models\Product;

class WelcomeController extends Controller
{
    public function index()
    {
        // Lấy danh sách sản phẩm để hiển thị ngoài trang chủ
        $products = Product::with('category')->latest()->paginate(12);

        return view('welcome', compact('products'));
    }
}
