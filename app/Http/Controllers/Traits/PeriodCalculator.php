<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

trait PeriodCalculator
{
    /**
     * ユーザーの作成日からステージを取得
     *
     * @return string
     */
    public function getPeriodFromCreationDate()
    {
        // 今日の日付
        $today = now();

        // 作成日からの経過日数を計算
        $createdAt = Auth::user()->created_at;
        $daysSinceCreation = $today->diffInDays($createdAt);

        // 経過日数に基づいて期間を設定
        if ($daysSinceCreation >= 0 && $daysSinceCreation <= 7) {
            return 'week';
        } elseif ($daysSinceCreation > 7 && $daysSinceCreation <= 45) {
            return 'month';
        } elseif ($daysSinceCreation > 45 && $daysSinceCreation <= 120) {
            return 'quarter';
        } else {
            return 'term';
        }
    }
}
