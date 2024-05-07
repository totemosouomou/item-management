<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
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
        $articles = [
            [
                'title' => '完全未経験が半年で個人サービスをリリースした話',
                'url' => 'https://qiita.com/3y9Mz/items/a6cfb2fba87058f02b83',
            ],
            [
                'title' => '駆け出しエンジニアが目指すジュニアレベルのエンジニアとは',
                'url' => 'https://qiita.com/mamimami0709/items/fd6556707e4b924c65ab',
            ],
            [
                'title' => '文系未経験出身が考える 要となるコンピュータの知識と知見',
                'url' => 'https://qiita.com/mikey_117/items/d2de10a889bcecf9370b',
            ],
            [
                'title' => '初めての個人開発 ①要件定義・設計編',
                'url' => 'https://qiita.com/mosyaneko/items/8a084443ea60d8da9d53',
            ],
            [
                'title' => 'LaravelでのMVCを理解する',
                'url' => 'https://qiita.com/si_hlaingzar/items/05c066fbc3ed689c3a50',
            ],
            [
                'title' => 'メンバー全員でリファクタリング戦略会議をした話',
                'url' => 'https://qiita.com/Taishikun0721/items/9811c857935a077f2aff',
            ],
            [
                'title' => 'エンジニアになって2ヶ月経って感じた事と必要だと思った知識',
                'url' => 'https://qiita.com/ruitomo/items/74bbfd62e4c1c9333bd0',
            ],
            [
                'title' => 'Webサイトの表示画像をWebPにする方法, 検証',
                'url' => 'https://qiita.com/tatsukoni/items/dbaa3619a5aedf1f2daf',
            ],
            [
                'title' => 'Bladeでの処理はこれで完璧！',
                'url' => 'https://qiita.com/shimada_slj/items/1d978277d035e77911a5',
            ],
            [
                'title' => 'JavaScriptを使った非同期通信について',
                'url' => 'https://qiita.com/uchiyama-t/items/2a3a2d99007a2cbcfe96',
            ],
            [
                'title' => '商品管理システムの作成する（まとめ）',
                'url' => 'https://qiita.com/EasyCoder/items/3e9c99bf96df7f1788eb',
            ],
            [
                'title' => 'Laravel 11 新機能・変更点',
                'url' => 'https://qiita.com/7mpy/items/4f4f7608c5fe44226d3c',
            ],
            [
                'title' => 'MVC+Sモデルに基づくAxiosを用いたAPI通信の流れ（簡単な実装例あり）',
                'url' => 'https://zenn.dev/sdb_blog/articles/kenshin-blog-001',
            ],
            [
                'title' => 'Laravel超超超初心者の入門書選びと学んだこと',
                'url' => 'https://zenn.dev/sdb_blog/articles/a979f330b49b89',
            ],
            [
                'title' => 'Laravelの教科書 バージョン10対応',
                'url' => 'https://amzn.asia/d/g2oYlje',
            ],
        ];

        foreach ($articles as $article) {
            $createdAt = Carbon::now()->subDays(rand(0, 180));

            DB::table('items')->insert([
                'user_id' => rand(1, 4),
                'title' => $article['title'],
                'url' => $article['url'],
                'stage' => $this->getStage($createdAt),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    /**
     * 作成日からステージを取得するメソッド
     *
     * @param \DateTimeInterface $createdAt
     * @return string
     */
    private function getStage($createdAt): string
    {
        // 今からの日数を計算
        $daysDiff = now()->diffInDays($createdAt);

        // 日数に応じてステージを返す
        if ($daysDiff <= 7) {
            return 'week';
        } elseif ($daysDiff <= 30) {
            return 'month';
        } elseif ($daysDiff <= 120) {
            return 'quarter';
        } else {
            return 'term';
        }
    }
}
