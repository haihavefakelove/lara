<?php
// app/Http/Controllers/Admin/PageController.php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::orderByDesc('updated_at')->paginate(20);
        return view('admin.pages.index', compact('pages'));
    }

    public function create()
    {
        $page = new Page(['status' => 'draft']);
        return view('admin.pages.create', compact('page'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'            => ['required','string','max:255'],
            'slug'             => ['nullable','string','max:255','unique:pages,slug'],
            'content'          => ['nullable','string'],
            'status'           => ['required','in:draft,published'],
            'meta_title'       => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string','max:500'],
            'published_at'     => ['nullable','date'],
        ]);

        $page = Page::create($data);
        return redirect()->route('admin.pages.edit', $page)->with('success','Đã tạo trang.');
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $data = $request->validate([
            'title'            => ['required','string','max:255'],
            'slug'             => ['nullable','string','max:255','unique:pages,slug,'.$page->id],
            'content'          => ['nullable','string'],
            'status'           => ['required','in:draft,published'],
            'meta_title'       => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string','max:500'],
            'published_at'     => ['nullable','date'],
        ]);

        $page->update($data);
        return back()->with('success','Đã lưu thay đổi.');
    }

    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('admin.pages.index')->with('success','Đã xoá trang.');
    }
}
