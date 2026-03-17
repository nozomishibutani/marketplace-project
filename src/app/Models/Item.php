<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    public const STATUS_ON_SALE = 1;
    public const STATUS_SOLD = 2;

    /**
     * 商品ステータス
     */
    public const STATUSES = [
        self::STATUS_ON_SALE,
        self::STATUS_SOLD,
    ];

    public const CONDITION_GOOD = 1;
    public const CONDITION_NO_DAMAGE = 2;
    public const CONDITION_SCRATCH = 3;
    public const CONDITION_BAD = 4;
    public const CONDITION_HIDDEN = '選択してください';

    /**
     * 商品の状態
     */
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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_item')->withTimestamps();
    }

    public function favorites()
    {
        return $this->belongsToMany(Item::class, 'favorites')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->latest();
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /**
     * 売り切れ判定
     */
    public function isSold(): bool
{
    return $this->status === self::STATUS_SOLD;
}

}
