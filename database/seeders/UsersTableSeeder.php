<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [];

        $month = now()->month;
        $day = now()->day;
        $users = [];
        $lastName = [
            "佐藤",
            "鈴木",
            "高橋",
            "田中",
            "伊藤",
            "渡辺",
            "山本",
            "中村",
            "小林",
            "加藤",
            "吉田",
            "山田",
            "佐々木",
            "山口",
            "松本",
            "井上",
            "木村",
            "林",
            "斎藤",
            "清水",
            "山崎",
            "森",
            "池田",
            "橋本",
            "阿部",
            "石川",
            "中島",
            "前田",
            "藤田",
            "小川",
            "後藤",
            "岡田"
        ];

        for ($i = 0; $i < 16; $i++) {
            // name のインデックスに基づいて day を設定
            for ($j = 0; $j < 2; $j++) {
                if ($i % 3 == 0) {
                    $day = rand(1, 10);
                } elseif ($i % 3 == 1) {
                    $day = rand(11, 20);
                } else {
                    $day = rand(21, 30);
                }

                // month を設定 (12から始まり、3回ごとに1増加)
                if ($i < 3) {
                    $month = '12';
                    $created_at = '2023-' . $month . '-' . $day;
                } else {
                    $month = floor($i / 3);
                    $created_at = '2024-' . $month . '-' . $day;
                }

                $name = $lastName[$i * 2 + $j] . ($i * 2 + $j + 1) . '郎/' . $month . '月' . $day . '日';

                $users[] = [
                    'name' => $name,
                    'email' => $i*2 + $j + 1 . 'ro@techis.jp',
                    'password' => Hash::make('password'),
                    'created_at' => $created_at,
                    'updated_at' => $created_at,
                ];
            }
        }

        DB::table('users')->insert($users);
        DB::table('users')->where('id', 1)->update([
            'name' => '丸岡太郎/12月3日',
            'email' => 'taro@techis.jp',
            'created_at' => '2023-12-03',
            'updated_at' => '2023-12-03',
        ]);
    }
}
