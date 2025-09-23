<?php

// app/Http/Controllers/PageController.php
namespace App\Http\Controllers;

use App\Models\Page;

class PageController extends Controller
{
    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)
            ->when(app()->environment('production'), fn($q) => $q->where('status','published'))
            ->firstOrFail();

        return view('pages.show', compact('page'));
    }
}
