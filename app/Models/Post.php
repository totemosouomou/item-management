<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * コメントを所有するユーザーを取得します。
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * コメントを所有するアイテムを取得します。
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    protected $fillable = [
        'user_id',
        'item_id',
        'post',
    ];
}
