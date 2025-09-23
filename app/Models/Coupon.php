<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'type', 'value', 'max_uses', 'used', 'min_order',
        'start_at', 'end_at', 'is_active'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at'   => 'datetime',
        'is_active'=> 'boolean',
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
        if ($this->type === 'percent') {
            return round($cartTotal * ($this->value / 100), 2);
        }
        return round($this->value, 2);
    }
}
