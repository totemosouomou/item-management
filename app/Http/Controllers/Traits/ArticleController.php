<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Http;

trait ArticleController
{
    /**
     * 指定された検索語に基づくQiitaの記事を取得
     *
     * @param array $searchWords
     * @return array
     */
    public function getQiitaArticles(array $searchWords)
    {
        $articles = [];

        // アクセストークン
        $accessToken = env('QIITA_API_KEY');

        // 検索クエリの生成
        $query = 'title%3aLaravel';
        if ($searchWords && ($searchWords[0] !== '')) {
            $query .= '%2b' . implode('%2b', $searchWords);
        }

        // cURLセッションを初期化
        $ch = curl_init();

        // オプションを設定
        curl_setopt($ch, CURLOPT_URL, 'https://qiita.com/api/v2/items?page=1&per_page=10&query=' . $query);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // curl_setopt($ch, CURLOPT_HTTPHEADER, [
        //     'Authorization: Bearer ' . $accessToken,
        // ]);

        // リクエストを実行してレスポンスを取得
        $response = curl_exec($ch);

        // エラーチェック
        if ($response === false) {
            die(curl_error($ch));
        }

        // cURLセッションを閉じる
        curl_close($ch);

        // レスポンスを処理する
        $articles = json_decode($response, true);
        $filteredArticles = array_map(function($article) {
            return [
                'url' => $this->secure($article['url']),
                'title' => $this->secure($article['title']),
            ];
        }, $articles);
        return $filteredArticles;
    }
}
