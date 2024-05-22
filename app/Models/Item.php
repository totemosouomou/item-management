<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * アイテムを所有するユーザーを取得します。
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * このアイテムに関連するコメントを取得
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * このアイテムに関連するフラッグを取得
     */
    public function flags()
    {
        return $this->hasMany(Flag::class);
    }

    /**
     * このアイテムに関連する bookmark を取得
     */
    public function bookmarks()
    {
        return $this->hasMany(Bookmark::class);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'stage',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
    ];
}
