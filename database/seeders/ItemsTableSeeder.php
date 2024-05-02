<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class ItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 20件のダミーデータを生成
        for ($i = 0; $i < 20; $i++) {
            $createdAt = Carbon::now()->subDays(rand(0, 210));

            DB::table('items')->insert([
                'user_id' => rand(1, 6), // ユーザーIDを1から6の範囲でランダムに設定
                'name' => Str::random(10), // 10文字のランダムな文字列
                'type' => Str::random(10), // 10文字のランダムな文字列
                'detail' => Str::random(10), // 10文字のランダムな文字列
                'created_at' => $createdAt, // 0〜210日前のランダムな日時を設定
                'updated_at' => $createdAt, // created_atと同じ日時を設定
            ]);
        }
    }
}
