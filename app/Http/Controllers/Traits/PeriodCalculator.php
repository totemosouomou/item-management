<?php

namespace App\Http\Controllers\Traits;

use Carbon\Carbon;

trait PeriodCalculator
{
    /**
     * ユーザーの作成日から期間を取得する
     *
     * @param Carbon $createdAt
     * @return string
     */
    public function getPeriodFromCreationDate(Carbon $createdAt): string
    {
        // 今日の日付
        $today = now();

        // 作成日からの経過日数を計算
        $daysSinceCreation = $today->diffInDays($createdAt);

        // 経過日数に基づいて期間を設定
        if ($daysSinceCreation >= 0 && $daysSinceCreation <= 7) {
            return 'week';
        } elseif ($daysSinceCreation > 7 && $daysSinceCreation <= 30) {
            return 'month';
        } elseif ($daysSinceCreation > 30 && $daysSinceCreation <= 120) {
            return 'quarter';
        } else {
            return 'term';
        }
    }
}
