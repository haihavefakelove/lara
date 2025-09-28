<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'max_uses', 'used', 'min_order', 'max_order',
        'start_at', 'end_at', 'is_active'
    ];

    protected $casts = [
        'value'      => 'float',
        'min_order'  => 'float',
        'max_order'  => 'float',
        'max_uses'   => 'int',
        'used'       => 'int',
        'start_at'   => 'datetime',
        'end_at'     => 'datetime',
        'is_active'  => 'boolean',
    ];

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function($q){
                $q->whereNull('start_at')->orWhere('start_at','<=', now());
            })
            ->where(function($q){
                $q->whereNull('end_at')->orWhere('end_at','>=', now());
            })
            ->where(function($q){
                $q->whereNull('max_uses')->orWhereColumn('used','<','max_uses');
            });
    }

   public function calcDiscount(float $cartTotal): float
{
    if ($cartTotal <= 0) return 0.0;

    if (!is_null($this->min_order) && $cartTotal < (float)$this->min_order) {
        return 0.0;
    }
    
    $discount = 0.0;

    if ($this->type === 'percent') {
        $discount = $cartTotal * ((float)$this->value / 100.0);
    } else { 
        $discount = (float)$this->value;
    }
      
    if (!is_null($this->max_order) && $discount > (float)$this->max_order) {
        $discount = (float)$this->max_order;
    }

    return round(max(0.0, min($discount, $cartTotal)), 2);
}

}
