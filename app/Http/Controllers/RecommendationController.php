<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RecommendationService;

class RecommendationController extends Controller
{
    public function product(Request $request, int $productId)
    {
        $items = app(RecommendationService::class)->forProduct(Auth::id(), $productId, (int)$request->get('limit', 8));
        if ($request->wantsJson()) return response()->json($items);
        return view('products.partials.recommendations', ['products' => $items]);
    }

    public function home(Request $request)
    {
        $items = app(RecommendationService::class)->forUserOrPopular(Auth::id(), (int)$request->get('limit', 8));
        if ($request->wantsJson()) return response()->json($items);
        return view('products.partials.recommendations', ['products' => $items]);
    }
}
