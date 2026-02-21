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

    public const STATUSES = [
        self::STATUS_ON_SALE,
        self::STATUS_SOLD,
        self::STATUS_SUSPENDED,
    ];

    public const CONDITION_GOOD = 1;
    public const CONDITION_NO_DAMAGE = 2;
    public const CONDITION_SCRATCH = 3;
    public const CONDITION_BAD = 4;

    public const CONDITIONS = [
        self::CONDITION_GOOD => '良好',
        self::CONDITION_NO_DAMAGE => '目立った傷や汚れなし',
        self::CONDITION_SCRATCH => 'やや傷や汚れあり',
        self::CONDITION_BAD => '状態が悪い',
];

    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'brand_name',
        'description',
        'price',
        'condition',
        'status',
        'img',
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
