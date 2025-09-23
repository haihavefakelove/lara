<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * (Tuỳ chọn) Khoá controller bằng middleware ngay tại đây.
     * Bạn có thể bỏ __construct nếu đã gán middleware trong routes.
     */
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Hiển thị danh sách category (ADMIN)
     */
    public function index()
    {
        $categories = Category::orderBy('name')->paginate(10);
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Hiển thị form tạo category (ADMIN)
     */
    public function create()
    {
        return view('admin.categories.create');
    }

    /**
     * Lưu category mới vào DB (ADMIN)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:categories,name'],
        ]);

        Category::create($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Thêm category thành công!');
    }

    /**
     * Hiển thị form sửa category (ADMIN)
     */
    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    /**
     * Cập nhật category (ADMIN)
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->ignore($category->id),
            ],
        ]);

        $category->update($validated);

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Cập nhật category thành công!');
    }

    /**
     * Xoá category (ADMIN)
     * Lưu ý: nếu FK products.category_id có onDelete('cascade')
     * thì khi xoá category sẽ xoá kèm products liên quan.
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return redirect()
            ->route('admin.categories.index')
            ->with('success', 'Xoá category thành công!');
    }
}
