<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    /**
     * dummy画像
     */
    public const DEFAULT_AVATAR = 'profiles/icon_dummy.png';

    protected $fillable = [
        'user_id',
        'postcode',
        'address',
        'building',
        'avatar',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}