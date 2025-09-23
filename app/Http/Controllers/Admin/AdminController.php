<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// nếu muốn reuse index của 2 controller con:
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;

class AdminController extends Controller
{
    /**
     * Trang Dashboard của Admin.
     * GET /admin/dashboard
     */
    public function dashboard()
    {
        $stats = [
    'products'   => \App\Models\Product::count(),
    'categories' => \App\Models\Category::count(),
    'orders'     => \App\Models\Orders::count(),
    'users'      => \App\Models\User::count(),
];
return view('admin.dashboard', compact('stats'));

    }

    /**
     * Điều hướng tới trang quản lý Sản phẩm.
     * GET /admin/products
     */
    public function products()
    {
        // gọi lại index() của Admin\ProductController
        return app(AdminProductController::class)->index();
    }

    /**
     * Điều hướng tới trang quản lý Danh mục.
     * GET /admin/categories
     */
    public function categories()
    {
        // gọi lại index() của Admin\CategoryController
        return app(AdminCategoryController::class)->index();
    }
}
