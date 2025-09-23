<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Orders;
use App\Models\OrderItems;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RecommendationService
{
    protected int $defaultLimit = 8;

    public function forProduct(?int $userId, int $productId, int $limit = null)
    {
        $limit = $limit ?? $this->defaultLimit;

        return Cache::remember("rec:product:{$productId}:u:{$userId}:l:{$limit}", 600, function () use ($userId, $productId, $limit) {
            $product = Product::query()->find($productId);
            if (!$product) return collect();

            $pool = collect();

            // 1) TƯƠNG TỰ (cùng category, gần tầm giá)
            $priceBand = 0.2; // ±20%
            $min = ($product->price ?? 0) * (1 - $priceBand);
            $max = ($product->price ?? 0) * (1 + $priceBand);

            $similar = Product::query()
                ->where('id', '!=', $product->id)
                ->when(isset($product->category_id), fn($q) => $q->where('category_id', $product->category_id))
                ->when(isset($product->price), fn($q) => $q->whereBetween('price', [$min, $max]))
                ->take($limit * 2)
                ->get()
                ->mapWithKeys(fn($p) => [$p->id => 0.6]);

            $pool = $pool->merge($similar);

            // 2) LỊCH SỬ MUA CỦA USER
            if ($userId) {
                $userTopProducts = OrderItems::query()
                    ->select('order_items.product_id', DB::raw('COUNT(*) as cnt'))
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->where('orders.user_id', $userId)
                    ->groupBy('order_items.product_id')
                    ->orderByDesc('cnt')
                    ->take($limit * 2)
                    ->pluck('cnt', 'product_id');

                foreach ($userTopProducts as $pid => $cnt) {
                    if ($pid == $productId) continue;
                    $pool[$pid] = max($pool[$pid] ?? 0, 0.5 + min($cnt, 5) * 0.02);
                }

                $userTopCategories = OrderItems::query()
                    ->select('products.category_id', DB::raw('COUNT(*) as cnt'))
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->where('orders.user_id', $userId)
                    ->groupBy('products.category_id')
                    ->orderByDesc('cnt')
                    ->take(3)
                    ->pluck('cnt', 'category_id');

                if ($userTopCategories->isNotEmpty()) {
                    $cand = Product::query()
                        ->whereIn('category_id', $userTopCategories->keys())
                        ->where('id', '!=', $productId)
                        ->take($limit * 2)
                        ->pluck('id');

                    foreach ($cand as $pid) {
                        $pool[$pid] = max($pool[$pid] ?? 0, 0.45);
                    }
                }
            }

            // 3) ALSO-BOUGHT (đồng mua trong cùng đơn)
            $alsoBought = OrderItems::query()
                ->select('oi2.product_id', DB::raw('COUNT(*) as cnt'))
                ->from('order_items as oi1')
                ->join('order_items as oi2', function($j) {
                    $j->on('oi1.order_id', '=', 'oi2.order_id');
                })
                ->where('oi1.product_id', $productId)
                ->whereColumn('oi2.product_id', '!=', 'oi1.product_id')
                ->groupBy('oi2.product_id')
                ->orderByDesc('cnt')
                ->take($limit * 3)
                ->pluck('cnt', 'product_id');

            foreach ($alsoBought as $pid => $cnt) {
                $pool[$pid] = max($pool[$pid] ?? 0, 0.4 + min($cnt, 10) * 0.03);
            }

            $ids = collect($pool)->sortDesc()->keys()->take($limit)->values();

            return Product::query()
                ->whereIn('id', $ids)
                ->get()
                ->sortByDesc(fn($p) => $pool[$p->id] ?? 0)
                ->values();
        });
    }

    // Gợi ý cho trang home (user hoặc phổ biến)
    public function forUserOrPopular(?int $userId, int $limit = null)
    {
        $limit = $limit ?? $this->defaultLimit;

        if ($userId) {
            $topCate = OrderItems::query()
                ->select('products.category_id', DB::raw('COUNT(*) as cnt'))
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->join('products', 'products.id', '=', 'order_items.product_id')
                ->where('orders.user_id', $userId)
                ->groupBy('products.category_id')
                ->orderByDesc('cnt')
                ->value('category_id');

            if ($topCate) {
                return Product::query()
                    ->where('category_id', $topCate)
                    ->orderByDesc('sold_count') // nếu không có cột này, thay bằng join order_items
                    ->take($limit)
                    ->get();
            }
        }

        return Product::query()
            ->orderByDesc('sold_count')
            ->take($limit)
            ->get();
    }
}
