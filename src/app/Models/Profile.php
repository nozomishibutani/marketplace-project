<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Profile extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'postcode',
        'address',
        'building',
        'avatar',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    /**
     * dummy画像
     */
    public const DEFAULT_AVATAR = 'profiles/icon_dummy.png';
}