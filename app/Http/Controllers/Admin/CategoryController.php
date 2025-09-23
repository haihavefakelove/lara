<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // (Tuỳ chọn) khoá controller bằng middleware ngay tại đây
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Hiển thị danh sách category
     */
    public function index()
    {
        $categories = Category::orderBy('id')->paginate(10);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Hiển thị form tạo category mới
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Lưu category mới vào DB
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Thêm danh mục thành công!');
    }

    /**
     * Hiển thị form sửa category
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Cập nhật category
     */
    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name,'.$category->id],
        ]);

        $category->update($data);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Cập nhật danh mục thành công!');
    }

    /**
     * Xoá category
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Xoá danh mục thành công!');
    }
}
