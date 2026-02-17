<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory;
    use SoftDeletes;

    public const STATUS_ON_SALE = 1;
    public const STATUS_SOLD = 2;
    public const STATUS_SUSPENDED = 3;

    public const CONDITIONS = [
        1 => '良好',
        2 => '目立った傷や汚れなし',
        3 => 'やや傷や汚れあり',
        4 => '状態が悪い',
    ];

    protected $fillable = [
        'category_id',
        'name',
        'brand_name',
        'description',
        'price',
        'condition',
        'status',
        'image',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 出品停止中
     */
    public function scopeNotSuspended($query)
    {
        return $query->where('status','!=', self::STATUS_SUSPENDED);
    }

}
