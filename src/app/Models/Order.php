<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'item_id',
        'payment_method',
        'postcode',
        'address',
        'building',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * 支払い方法ID
     */
    public const PAYMENT_CONVENIENCE = 1;
    public const PAYMENT_CARD = 2;
    public const PAYMENT_HIDDEN = '選択してください';

    /**
     * 支払い方法
     */
    public const PAYMENT_METHODS = [
    self::PAYMENT_CONVENIENCE => 'コンビニ支払い',
    self::PAYMENT_CARD => 'カード支払い',
];
}
