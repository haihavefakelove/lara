<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\RecommendationService;

class RecommendationController extends Controller
{
    public function __construct(private RecommendationService $rec) {}

    public function product(Request $request, int $productId)
    {
        // Lấy limit an toàn trong khoảng 1..24
        $limit = max(1, min(24, $request->integer('limit', 8)));

        $items = $this->rec->forProduct(Auth::id(), $productId, $limit);

        if ($request->wantsJson()) {
            return response()->json($items);
            // Hoặc dùng Resource: return ProductResource::collection($items);
        }

        return view('products.partials.recommendations', ['products' => $items]);
    }

    public function home(Request $request)
    {
        $limit = max(1, min(24, $request->integer('limit', 8)));

        $items = $this->rec->forUserOrPopular(Auth::id(), $limit);

        if ($request->wantsJson()) {
            return response()->json($items);
            // Hoặc: return ProductResource::collection($items);
        }

        return view('products.partials.recommendations', ['products' => $items]);
    }
}
