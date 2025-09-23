<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    protected $fillable = [
        'user_id','order_id','order_item_id','product_id','rating','comment','status',
    ];

    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function order(): BelongsTo   { return $this->belongsTo(Orders::class, 'order_id'); }
    public function item(): BelongsTo    { return $this->belongsTo(OrderItems::class, 'order_item_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
