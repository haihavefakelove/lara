<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'name', 'address', 'phone',
        'total_price', 'payment_method', 'payment_status',
        'status', 'momo_request_id', 'momo_order_id', 'shipping_status',
        // === thêm cho PayOS (không ảnh hưởng phần khác) ===
        'payos_order_code', 'payos_checkout_url', 'paid_at',
        // tùy DB của bạn đã có hay chưa:
        'discount', 'coupon_id', // nếu có hai cột này thì để, không có cũng không sao
    ];

    // Quan hệ 1-n với OrderItems
    public function items()
    {
        return $this->hasMany(OrderItems::class, 'order_id');
    }

    // Người tạo đơn
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
