<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class AdminController extends Controller
{
    /**
     * GET /admin/dashboard
     */
    public function dashboard()
    {$stats = [
    'products'   => \App\Models\Product::count(),
    'categories' => \App\Models\Category::count(),
    'orders'     => \App\Models\Orders::count(),
    'users'      => \App\Models\User::count(),
];
return view('admin.dashboard', compact('stats'));

    }

    /**
     * Nếu muốn điều hướng từ Dashboard tới trang quản lý sản phẩm
     * (Không bắt buộc nếu đã dùng Admin\ProductController@index)
     */
    public function products()
    {
        return view('admin.products.index');
    }

    /**
     * Nếu muốn điều hướng từ Dashboard tới trang quản lý danh mục
     * (Không bắt buộc nếu đã dùng Admin\CategoryController@index)
     */
    public function categories()
    {
        return view('admin.categories.index');
    }
}
