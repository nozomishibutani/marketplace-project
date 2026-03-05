<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\MustVerifyEmail;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * User削除時にProfileも連動削除する
     *
     * ・通常削除 → ProfileもSoftDelete
     * ・完全削除 → ProfileもforceDelete
     *
     * @return void
     */
    /* 現状ユーザーの削除は実装想定外
    protected static function booted()
    {
        static::deleting(function ($user) {

            // 関連モデルの配列
            $relations = ['profile', 'items', 'favorites', 'orders', 'comments'];

            foreach ($relations as $relation) {
                if ($user->$relation) {

                    // コレクションか単体かで処理を分ける
                    if ($user->$relation instanceof \Illuminate\Database\Eloquent\Collection) {
                        foreach ($user->$relation as $related) {
                            $user->isForceDeleting() ? $related->forceDelete() : $related->delete();
                        }
                    } else {
                        $user->isForceDeleting() ? $user->$relation->forceDelete() : $user->$relation->delete();
                    }
                }
            }

        });
    }
    */

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

}
