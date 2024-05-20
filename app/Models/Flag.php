<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Flag extends Model
{
    /**
     * フラッグを所有するユーザーを取得します。
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * フラッグを所有するアイテムを取得します。
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    protected $fillable = [
        'user_id',
        'item_id',
        'flag',
    ];
}
