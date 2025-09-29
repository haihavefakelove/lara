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

    public function forProduct(?int $userId, int $productId, ?int $limit = null)
    {
        $limit = $limit ?? $this->defaultLimit;

        return Cache::remember("rec:product:{$productId}:u:{$userId}:l:{$limit}", 600, function () use ($userId, $productId, $limit) {
            $product = Product::query()->find($productId);
            if (!$product) return collect();

            $orderedIds = [];

            // 1) SIMILAR
            $priceBand = 0.2; 
            $min = ($product->price ?? 0) * (1 - $priceBand);
            $max = ($product->price ?? 0) * (1 + $priceBand);

            $candidates = Product::query()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->orderByDesc('created_at') // hoặc ->latest() / ->orderByDesc('id') / ->orderByDesc('views')
            ->get();


            $pickedSimilar = 0;
            foreach ($candidates as $p) {
                if (isset($p->price) && $p->price >= $min && $p->price <= $max) {
                    $orderedIds[] = (int)$p->id;
                    $pickedSimilar++;
                    if ($pickedSimilar >= 2) break;
                }
            }

            // 2) ALSO-BOUGHT
            $alsoBoughtRows = OrderItems::query()
                ->select('oi2.product_id', DB::raw('COUNT(*) as cnt'))
                ->from('order_items as oi1')
                ->join('order_items as oi2', 'oi1.order_id', '=', 'oi2.order_id')
                ->where('oi1.product_id', $productId)
                ->whereColumn('oi2.product_id', '!=', 'oi1.product_id')
                ->groupBy('oi2.product_id')
                ->orderByDesc('cnt')
                ->limit($limit * 2)
                ->get();

            foreach ($alsoBoughtRows as $row) {
                $pid = (int)$row->product_id;
                if (!in_array($pid, $orderedIds, true)) {
                    $orderedIds[] = $pid;
                }
                if (count($orderedIds) >= $limit) break;
            }

            // 3) USER HISTORY
            if (count($orderedIds) < $limit && $userId) {
                $topCategoryRow = DB::table('order_items')
                    ->join('orders', 'orders.id', '=', 'order_items.order_id')
                    ->join('products', 'products.id', '=', 'order_items.product_id')
                    ->where('orders.user_id', $userId)
                    ->select('products.category_id', DB::raw('COUNT(*) as cnt'))
                    ->groupBy('products.category_id')
                    ->orderByDesc('cnt')
                    ->first();

                $topCategoryId = $topCategoryRow->category_id ?? null;

                if ($topCategoryId) {
                    $rand = Product::query()
                        ->where('category_id', $topCategoryId)
                        ->whereNotIn('id', $orderedIds)
                        ->where('id', '!=', $productId)
                        ->inRandomOrder()
                        ->first();

                    if ($rand) {
                        $orderedIds[] = (int)$rand->id;
                    }
                }
            }
            if (count($orderedIds) < $limit) {
                foreach ($candidates as $p) {
                    if (in_array((int)$p->id, $orderedIds, true)) continue;
                    if (isset($p->price) && $p->price >= $min && $p->price <= $max) {
                        $orderedIds[] = (int)$p->id;
                        if (count($orderedIds) >= $limit) break;
                    }
                }
                if (count($orderedIds) < $limit) {
                    foreach ($candidates as $p) {
                        if (in_array((int)$p->id, $orderedIds, true)) continue;
                        $orderedIds[] = (int)$p->id;
                        if (count($orderedIds) >= $limit) break;
                    }
                }
            }
            $orderedIds = array_values(array_unique($orderedIds));
            if (empty($orderedIds)) return collect();
            if (count($orderedIds) > $limit) {
                $orderedIds = array_slice($orderedIds, 0, $limit);
            }

            $productsById = Product::whereIn('id', $orderedIds)->get()->keyBy('id');

            $result = collect();
            foreach ($orderedIds as $id) {
                if (isset($productsById[$id])) {
                    $result->push($productsById[$id]);
                }
            }

            return $result;

            
        });
    }

    public function forUserOrPopular(?int $userId, ?int $limit = null)
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
                    ->orderByDesc('sold_count')
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
