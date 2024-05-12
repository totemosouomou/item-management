<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Support\Facades\Http;

trait ArticleController
{
    public function getQiitaArticles($requestSearch)
    {
        $articles = [];
        $page = 1;

        // cURLセッションを初期化
        $ch = curl_init();
        // オプションを設定
        if ($requestSearch) {
            curl_setopt($ch, CURLOPT_URL, 'https://qiita.com/api/v2/items?page=1&per_page=10&query=title:Laravel+title:' . $requestSearch);
        } else {
            curl_setopt($ch, CURLOPT_URL, 'https://qiita.com/api/v2/items?page=1&per_page=10&query=title:Laravel');
        }
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

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
        return $articles;
    }
}
