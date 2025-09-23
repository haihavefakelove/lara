<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * Thuộc tính cho phép gán hàng loạt.
     */
    protected $fillable = [
        'name', 'brand', 'price', 'quantity', 'sku',
        'volume', 'shade', 'expiry_date', 'origin',
        'skin_type', 'features', 'ingredients', 'usage',
        'description', 'image_url', 'category_id',
    ];

    /**
     * Quan hệ: sản phẩm thuộc về một danh mục.
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Quan hệ: các đánh giá đã được duyệt của sản phẩm.
     */
    public function reviews()
    {
        return $this->hasMany(Review::class)->where('status', 'approved');
    }

    /**
     * Quan hệ: các dòng hàng trong đơn có tham chiếu tới sản phẩm này.
     * (Hữu ích cho thống kê / gợi ý sản phẩm)
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItems::class, 'product_id');
    }

    /**
     * Điểm đánh giá trung bình (làm tròn 1 chữ số thập phân).
     */
    public function avgRating(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
    }
}
