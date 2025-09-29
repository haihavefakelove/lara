<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\LLMClient;
use App\Models\Product;

class ChatbotController extends Controller
{
    public function index() {
        return view('chat');
    }

    public function message(Request $req, LLMClient $cb)
    {
        try {
            $history = $req->input('history', []);
            $userMsg = $req->input('message', '');

            $system = require base_path('app/Prompts/Prompt.php');
            $messages = array_merge(
                [['role' => 'system', 'content' => $system]],
                $history,
                [['role' => 'user', 'content' => $userMsg]]
            );

            $resp = $cb->chat($messages, [
                'temperature' => 0.2,
                'max_tokens' => 800,
            ]);

            $content = $resp['choices'][0]['message']['content'] ?? '{}';
            $advisor = json_decode($content, true);
            if (!is_array($advisor)) {
                $advisor = ['message' => $content, 'filters' => []];
            }

            $filters = $advisor['filters'] ?? [];

            
            $products = $this->filterProducts($filters);

            return response()->json([
                'advisor'  => $advisor,
                'products' => $products,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'server_error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function filterProducts(array $filters)
    {
        $query = Product::query()->with('category');
        $filtersApplied = [];

        if (!empty($filters['category_ids']) && is_array($filters['category_ids'])) {
            $query->whereIn('category_id', $filters['category_ids']);
            $filtersApplied[] = 'category_ids: ' . implode(',', $filters['category_ids']);
        }

        if (!empty($filters['skin_type'])) {
            $skinType = strtolower($filters['skin_type']);
            $query->where(function($q) use ($skinType) {
                $q->where('skin_type', 'like', '%' . $skinType . '%')
                  ->orWhere('skin_type', 'like', '%all%') 
                  ->orWhere('skin_type', 'like', '%mọi loại da%')
                  ->orWhereNull('skin_type');
            });
            $filtersApplied[] = 'skin_type: ' . $filters['skin_type'];
        }

        if (isset($filters['price_min']) && $filters['price_min'] > 0) {
            $query->where('price', '>=', $filters['price_min']);
            $filtersApplied[] = 'price_min: ' . number_format($filters['price_min']) . ' VND';
        }
        if (isset($filters['price_max']) && $filters['price_max'] > 0) {
            $query->where('price', '<=', $filters['price_max']);
            $filtersApplied[] = 'price_max: ' . number_format($filters['price_max']) . ' VND';
        }

        if (!empty($filters['brand_in']) && is_array($filters['brand_in'])) {
            $query->whereIn('brand', $filters['brand_in']);
        }

        if (!empty($filters['origin_in']) && is_array($filters['origin_in'])) {
            $query->whereIn('origin', $filters['origin_in']);
        }

        if (!empty($filters['shade_like'])) {
            $query->where('shade', 'like', '%' . $filters['shade_like'] . '%');
        }

        if (!empty($filters['must_have_keywords']) && is_array($filters['must_have_keywords'])) {
            foreach ($filters['must_have_keywords'] as $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('features', 'like', '%' . $keyword . '%')
                      ->orWhere('ingredients', 'like', '%' . $keyword . '%')
                      ->orWhere('usage', 'like', '%' . $keyword . '%')
                      ->orWhere('description', 'like', '%' . $keyword . '%')
                      ->orWhere('name', 'like', '%' . $keyword . '%');
                });
            }
        }

        if (!empty($filters['avoid_keywords']) && is_array($filters['avoid_keywords'])) {
            foreach ($filters['avoid_keywords'] as $keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('features', 'not like', '%' . $keyword . '%')
                      ->where('ingredients', 'not like', '%' . $keyword . '%')
                      ->where('usage', 'not like', '%' . $keyword . '%')
                      ->where('description', 'not like', '%' . $keyword . '%')
                      ->where('name', 'not like', '%' . $keyword . '%');
                });
            }
        }

        if (!empty($filters['hard_constraints'])) {
            $constraints = $filters['hard_constraints'];

            if (isset($constraints['budget_vnd'])) {
                $query->where('price', '<=', $constraints['budget_vnd']);
            }

            if (!empty($constraints['avoid_ingredients']) && is_array($constraints['avoid_ingredients'])) {
                foreach ($constraints['avoid_ingredients'] as $ingredient) {
                    $query->where('ingredients', 'not like', '%' . $ingredient . '%');
                }
            }
        }

        if (isset($filters['price_min']) || isset($filters['price_max'])) {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderByDesc('created_at');
        }

        $products = $query->limit(8)->get();


        if ($products->isEmpty() && !empty($filters)) {
            
            $relaxedQuery = Product::query()->with('category');

            if (!empty($filters['category_ids'])) {
                $relaxedQuery->whereIn('category_id', $filters['category_ids']);
            }
            if (isset($filters['price_max'])) {
                $relaxedQuery->where('price', '<=', $filters['price_max']);
            }
            
            $products = $relaxedQuery->orderBy('price')->limit(8)->get();

        }

        if ($products->isEmpty()) {
            $products = Product::query()
                ->with('category')
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get();
        }

        return $products;
    }
}
