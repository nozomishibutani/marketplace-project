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

    /**
     * 支払いステータス
     */
    public const STATUS_PENDING   = 1;
    public const STATUS_PAID      = 2;
    public const STATUS_SHIPPED   = 3;
    public const STATUS_CANCELED  = 4;

    public const STATUS_LIST = [
        self::STATUS_PENDING  => '支払い待ち',
        self::STATUS_PAID     => '支払い済み',
        self::STATUS_SHIPPED  => '発送済み',
        self::STATUS_CANCELED => 'キャンセル',
    ];
}
