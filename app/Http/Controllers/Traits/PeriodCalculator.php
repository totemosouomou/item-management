<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

trait PeriodCalculator
{
    /**
     * ユーザーの作成日または入校日からステージを取得
     *
     * @return string
     */
    public function getPeriodFromCreationDate()
    {
        // 今日の日付
        $today = now();

        // ユーザー情報を取得
        $user = Auth::user();

        // 入校日が存在する場合、それを使用
        if ($user->date_of_enrollment) {
            $enrollmentDate = Carbon::parse($user->date_of_enrollment);

            // 今日の日付が入校日より前の場合
            if ($today->lt($enrollmentDate)) {
                return 'week';
            }

            // 入校日からの経過日数を計算
            $daysSinceEnrollment = $today->diffInDays($enrollmentDate);

            // 経過日数に基づいて期間を設定
            if ($daysSinceEnrollment >= 0 && $daysSinceEnrollment <= 7) {
                return 'week';
            } elseif ($daysSinceEnrollment > 7 && $daysSinceEnrollment <= 45) {
                return 'month';
            } elseif ($daysSinceEnrollment > 45 && $daysSinceEnrollment <= 120) {
                return 'quarter';
            } else {
                return 'term';
            }
        }

        // 入校日が存在しない場合、作成日からの経過日数を使用
        $createdAt = $user->created_at;
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
