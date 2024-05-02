<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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
            DB::table('items')->insert([
                'user_id' => rand(1, 6), // ユーザーIDを1から6の範囲でランダムに設定
                'name' => Str::random(10), // 10文字のランダムな文字列
                'type' => Str::random(10), // 10文字のランダムな文字列
                'detail' => Str::random(10), // 10文字のランダムな文字列
                'created_at' => now(), // 現在の日時を設定
                'updated_at' => now(), // 現在の日時を設定
            ]);
        }
    }
}
