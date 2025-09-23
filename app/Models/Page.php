<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Page extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title','slug','content','status','meta_title','meta_description','published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function (Page $page) {
            // Tự tạo/normalize slug nếu trống
            $page->slug = $page->slug ? Str::slug($page->slug) : Str::slug($page->title);
        });
    }
}