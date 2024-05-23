<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use App\Models\User;
use Exception;

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
            [
                'title' => 'Laravelで2段階認証（2FA）を実装する',
                'url' => 'https://qiita.com/fakefurcoronet/items/17323a2e11d3eb11c445',
            ],
            [
                'title' => 'laravel 11.xでのブロードキャストメモ',
                'url' => 'https://qiita.com/morohoshi/items/461f1e54e22bce990697',
            ],
            [
                'title' => 'Laravelのメールをキュー投入してバックグラウンドで処理する',
                'url' => 'https://qiita.com/fakefurcoronet/items/6f9bc55617a8b085ba61',
            ],
            [
                'title' => 'Next.jsからDockerで起動しているLaravelのAPIを叩いたらCORSエラーが出て困った',
                'url' => 'https://qiita.com/keitaMax/items/3538df4ea664a0d1dc0c',
            ],
            [
                'title' => 'Laravelのキャッシュについて',
                'url' => 'https://qiita.com/dorayaki_9696/items/14b5611cabea9eae374e',
            ],
            [
                'title' => 'Laravel フラッシュメッセージを実装してみる🧑‍💻',
                'url' => 'https://qiita.com/tokec/items/fdc3d28a6f0f8ae1b83f',
            ],
            [
                'title' => 'laravel postされた値が指定されたテーブルのカラムに存在するか確認するバリデーションルール',
                'url' => 'https://qiita.com/miriwo/items/cb81505bcb3101cc9d61',
            ],
            [
                'title' => 'LaravelでVSCodeがよしなにメソッドを読み込んでくれない',
                'url' => 'https://qiita.com/hakkin/items/78c7825c123bffe1d8db',
            ],
            [
                'title' => 'Laravel + nextjsのdocker環境構築・プロジェクト立ち上げ',
                'url' => 'https://qiita.com/oohasi/items/602601b9abeb2eaa44b5',
            ],
            [
                'title' => 'Laravel SanctumのSPA認証 × Next.js(React) Axios で Request failed with status code 419, エラー解決',
                'url' => 'https://qiita.com/hikagami/items/da055860df931c30820b',
            ],
        ];

        foreach ($articles as $article) {
            $user_id = rand(1, User::count());

            // ステージと作成日を取得
            list($stage, $created_at) = $this->getStage($user_id);

            DB::table('items')->insert([
                'user_id' => $user_id,
                'title' => $article['title'],
                'url' => $article['url'],
                'stage' => $stage,
                'created_at' => $created_at,
                'updated_at' => $created_at,
            ]);
        }
    }

    /**
     * 作成日からステージを取得するメソッド
     *
     * @param int $user_id
     * @return array
     */
    private function getStage($user_id)
    {
        // ユーザーをデータベースから取得
        $user = User::find($user_id);
        if (!$user) {
            throw new Exception('User not found');
        }

        // ユーザー作成日
        $createdAt = $user->created_at;
        // 現在時刻とユーザー作成日との間の日数を計算
        $daysDiff = now()->diffInDays($createdAt);

        // 日数差が0の場合、1日を追加する
        $daysDiff = max($daysDiff, 1);

        // 投稿日は、ユーザー作成日からランダムな日数後（ただし、現在時刻を超えないようにする）
        $submitDays = (clone $createdAt)->addDays(rand(1, $daysDiff));

        // 投稿日とユーザー作成日が7以下のときweek、45以下の時monthなど、返す値を変更する
        $submitDiff = $submitDays->diffInDays(clone $createdAt);
        if ($submitDiff <= 7) {
            return ['week', $submitDays];
        } elseif ($submitDiff <= 45) {
            return ['month', $submitDays];
        } elseif ($submitDiff <= 120) {
            return ['quarter', $submitDays];
        } else {
            return ['term', $submitDays];
        }
    }
}