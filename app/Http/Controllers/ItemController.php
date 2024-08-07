<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PeriodCalculator;
use App\Http\Controllers\Traits\ArticleController;
use App\Models\User;
use App\Models\Item;
use App\Models\Post;
use App\Models\Flag;
use App\Models\Bookmark;

class ItemController extends Controller
{
    use PeriodCalculator, ArticleController;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * ページネーションに使用する数字を返す
     *
     * @return int
     */
    private function pagination($user_id): int
    {
        return $user_id == Auth::id() ? 50 : 10;
    }

    /**
     * タイトルに使用できる最大文字数を返す
     *
     * @return int
     */
    private function title(): int
    {
        return 100;
    }

    /**
     * コメントに使用できる最大文字数を返す
     *
     * @return int
     */
    private function comme(): int
    {
        return 200;
    }

    /**
     * バリデーションルールの定義
     *
     * @return array
     */
    private function validationRules()
    {
        return [
            'url' => 'required|url|starts_with:http,https',
            'title' => 'required|string|max_mb_str:' . $this->title() . '|ng_words',
            'post' => 'nullable|string|max_mb_str:' . $this->comme() . '|ng_words',
        ];
    }

    /**
     * バリデーションメッセージの定義
     *
     * @return array
     */
    private function validationMessages()
    {
        return [
            'url.required' => 'URLは必須項目です。',
            'url.url' => '有効なURLを入力してください。',
            'url.starts_with' => '有効なHTTPまたはHTTPSのURLを入力してください。',
            'title.required' => 'タイトルは必須項目です。',
            'title.string' => 'タイトルは文字列で入力してください。',
            'title.max_mb_str' => 'タイトルは' . $this->title() . '文字以内で入力してください。',
            'title.ng_words' => 'タイトルに相手を傷つける表現は含まれていませんか？',
            'post.string' => 'コメントは文字列で入力してください。',
            'post.max_mb_str' => 'コメントは' . $this->comme() . '文字以内で入力してください。',
            'post.ng_words' => 'コメントに相手を傷つける表現は含まれていませんか？',
        ];
    }

    /**
     * セキュリティ対策を施す処理
     *
     * @param string $word
     * @return string
     */
    private function secure($word)
    {
        // 値をサニタイズして返す
        return htmlspecialchars($word, ENT_QUOTES, 'UTF-8');
    }

    /**
     * 記事一覧
     *
     * @param \Illuminate\Http\Request  $request
     * @param int|null $user_id
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request, $user_id = null)
    {
        // ユーザーIDが指定されている場合のリダイレクト処理
        if ($user_id) {
            if ($user_id == "admin") {
                return redirect()->route('user', ['user_id' => Auth::id()]);
            }
            $user = User::find($user_id);
            if (!$user) {
                return redirect('/items')->with('error', '指定されたユーザーが見つかりませんでした。');
            }
        }

        // 検索機能
        if ($request->filled('search')) {
            $requestSearch = explode(' ', $request->input('search'));
            $requestSearch = array_filter($requestSearch, fn($value) => $value !== "");
            $request->session()->put('requestSearch', $requestSearch);
        } else {
            $requestSearch = $request->session()->get('requestSearch', []);
            if (!is_array($requestSearch)) {
                $requestSearch = explode(' ', $requestSearch);
            }
            $requestSearch = array_filter($requestSearch, fn($value) => $value !== "");
            if ($request->filled('clear')) {
                $clearValue = $request->input('clear');
                $requestSearch = array_values(array_diff($requestSearch, [$clearValue]));
            }
            $request->session()->put('requestSearch', $requestSearch);
        }

        // 記事のステージが指定されている場合
        $stage = request()->route()->getName();
        $titleNames = [
            'week' => '1週間以内の記事',
            'month' => '基礎課題の記事',
            'quarter' => '応用課題の記事',
            'term' => '開発課題の記事',
        ];
        $title_name = isset($titleNames[$stage]) ? $titleNames[$stage] : "全記事";

        // クエリビルダーの初期化
        $query = Item::with('posts')->with('bookmarks');

        // 各検索ワードに対して条件を追加
        foreach ($requestSearch as $word) {
            if (!empty($word)) {
                $query->where('title', 'like', '%' . $this->secure($word) . '%');
            }
        }

        // ユーザーIDが自身に指定されている場合の処理
        if ($user_id && $user == Auth::user()) {

            // ユーザーがブックマークしたアイテムIDを取得
            $bookmarkedItemIds = Bookmark::where('user_id', Auth::id())->pluck('item_id')->toArray();
            $query->where(function ($query) use ($user_id, $bookmarkedItemIds) {
                $query->where('user_id', $user_id)
                    ->orWhereIn('id', $bookmarkedItemIds); // ブックマークされたアイテムIDを含む
            });
            $title_name = '最大4件（ピン付き）';

        // ユーザーIDが指定されている場合の処理
        } elseif ($user_id) {
            $query->where('user_id', $user_id);

        // 記事のステージが指定されている場合の処理
        } elseif ($title_name !== '全記事') {
            $query->where('stage', $stage);
        }

        // 表示させないアイテムを設定
        $flaggedItemIds = Flag::where('user_id', Auth::id())->pluck('item_id')->toArray();
        $query->where('stage', '!=', 'inactive'); // ステージが inactive でない場合の処理
        $query->whereNotIn('id', $flaggedItemIds); // フラグがつけられたアイテムIDを除外する処理

        // クエリの結果を取得
        $items = $query->orderBy('created_at', 'desc')->paginate($this->pagination($user_id));

        // Trait 内のメソッドを呼び出し、ユーザーのステージを取得
        $period = $this->getPeriodFromCreationDate();

        // Trait 内のメソッドを呼び出し、指定された検索語に基づく Qiita の記事を取得
        $articles = $this->getQiitaArticles($requestSearch);

        // ユーザーIDが指定されている場合の処理
        if ($user_id && $user == Auth::user()) {

            // ピンが付いた記事を取得
            $pinnedItems = $items->filter(function($item) {
                return $item->bookmarks->contains(function($bookmark) {
                    return !is_null($bookmark->pinned_at);
                });
            });

            // ピンは最大4つまでの処理
            if (!$request->session()->get('success') && $pinnedItems->count() > 4) {
                $pinnedItems = $pinnedItems->sortByDesc(function($item) {
                    return $item->bookmarks->whereNotNull('pinned_at')->first()->pinned_at;
                });

                // 最新の4つ以外の記事からピンを外す
                $itemsToUnpin = $pinnedItems->slice(4);
                foreach ($itemsToUnpin as $item) {
                    $bookmark = $item->bookmarks->whereNotNull('pinned_at')->first();
                    if ($bookmark) {
                        $bookmark->pinned_at = null;
                        $bookmark->save();
                    }
                }

                // ピンが付いた記事を再取得
                $pinnedItems = $pinnedItems->take(4);

            }

            // ピンが付いていない記事を取得
            $unpinnedItems = $items->diff($pinnedItems);

            return view('item.bookmark', compact('stage', 'titleNames', 'items', 'title_name', 'period', 'articles', 'pinnedItems', 'unpinnedItems'))->with('requestSearch', $requestSearch)->with('urlInput', session('urlInput'));
        }

        return view('item.index', compact('stage', 'titleNames', 'items', 'title_name', 'period', 'articles'))->with('requestSearch', $requestSearch)->with('urlInput', session('urlInput'));
    }

    /**
     * 記事登録
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request, $urlInput = null)
    {
        // Trait 内のメソッドを呼び出し、ユーザーのステージを取得
        $period = $this->getPeriodFromCreationDate();

        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // バリデーションを実行
            $request->validate($this->validationRules(), $this->validationMessages());

            // 入力データをサニタイズ
            $secureTitle = $this->secure($request->input('title'));
            $secureUrl = $this->secure($request->input('url'));
            $securePost = $this->secure($request->input('post'));

            // サニタイズ後のデータの文字数をチェック
            if (mb_strlen($secureTitle) > $this->title() || mb_strlen($securePost) > $this->comme()) {
                return back()->with('error', '特殊文字が多いため登録できませんでした。');
            }

            $request->merge([
                'title' => $secureTitle,
                'url' => $secureUrl,
                'post' => $securePost,
            ]);

            // リクエストの値を period に再代入する
            if ($request->has('period')) {
                $period = $request->input('period');
            }

            // 記事登録
            $item = Item::create([
                'user_id' => Auth::id(),
                'title' => $secureTitle,
                'url' => $secureUrl,
                'stage' => $period,
            ]);

            // コメント登録
            if ($securePost) {
                Post::create([
                    'user_id' => Auth::id(),
                    'item_id' => $item->id,
                    'post' => $securePost . " by " . Auth::user()->name,
                ]);
            }

            // ブックマーク登録
            // $filePath = $this->generateScreenshot($item->url, $item->id);
            // if ($filePath) {
            //     $imageData = file_get_contents($filePath);
            //     $base64Image = base64_encode($imageData);
            //     $mimeType = 'image/png';
            //     Bookmark::create([
            //         'user_id' => Auth::id(),
            //         'item_id' => $item->id,
            //         'thumbnail' => 'data:' . $mimeType . ';base64,' . $base64Image,
            //     ]);
            // } else {
                Bookmark::create([
                    'user_id' => Auth::id(),
                    'item_id' => $item->id,
                    'thumbnail' => null,
                ]);
            // }

            // 記事一覧ページへ遷移
            if (strpos($request->headers->get('referer'), 'items/user/') !== false) {
                return back()->with('success', '記事が登録されました。');
            } else {
                return redirect("/items/{$period}")->with('success', '記事が登録されました。');
            }
        }

        return redirect("/items/{$period}")->with('add', "記事登録")->with('urlInput', $this->secure($urlInput));
    }

    /**
     * 記事更新
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // 更新前の記事とコメントを取得
            $item = Item::find($request->id);
            $postBeforeUpdate = Post::where('user_id', Auth::id())
                ->where('item_id', $request->id)
                ->first();
            $postBeforeUpdateComment = $postBeforeUpdate ? str_replace(" by " . Auth::user()->name, "", $postBeforeUpdate->post) : '';

            // 記事とコメントの変更があるかどうかを判定
            $itemChanged = $item->title !== $request->title || $item->url !== $request->url;
            $postChanged = (!$postBeforeUpdate && $request->post) || ($postBeforeUpdate && $request->post !== $postBeforeUpdateComment);

            // 更新データを準備
            $updateData = [];

            // 記事更新
            if ($itemChanged) {

                // 入力データをサニタイズ
                if ($item->title !== $request->title) {
                    $secureTitle = $this->secure($request->input('title'));
                    $updateData['title'] = $secureTitle;
                }
                if ($item->url !== $request->url) {
                    $secureUrl = $this->secure($request->input('url'));
                    $updateData['url'] = $secureUrl;
                }

                // バリデーションを実行
                $request->merge($updateData);
                $request->validate($this->validationRules(), $this->validationMessages());

                // 必要なフィールドのみ更新
                if (!empty($updateData)) {
                    $item->update($updateData);
                }
            }

            // コメント更新
            if ($postChanged) {

                // 入力データをサニタイズ
                $securePost = $this->secure($request->input('post'));

                // バリデーションを実行
                $request->merge([$securePost]);
                $request->validate($this->validationRules(), $this->validationMessages());

                if ($postBeforeUpdate) {
                    if ($request->post) {
                        $postBeforeUpdate->update([
                            'post' => $securePost . " by " . Auth::user()->name,
                        ]);
                    } else {
                        $postBeforeUpdate->delete();
                    }
                } elseif ($securePost) {
                    Post::create([
                        'user_id' => Auth::id(),
                        'item_id' => $request->id,
                        'post' => $securePost . " by " . Auth::user()->name,
                    ]);
                }
            }

            if ($itemChanged || $postChanged) {
                return back()->with('success', '記事が更新されました。');

            // 変更がない場合はback
            } else {
                return back()->with('success', '記事の更新はありません。');
            }
        }

        return redirect('/items')->with('error', '指定された記事が見つかりませんでした。');
    }

    /**
     * 記事削除（ブックマーク削除）
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function delete(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // 削除する記事を取得
            $item = Item::find($request->id);

            if ($item) {

                // ユーザーが記事の所有者であれば、記事を削除
                if ($item->user_id == Auth::id()) {
                    $item->posts()->delete();
                    $item->bookmarks()->delete();
                    $item->delete();

                    return back()->with('success', '記事を削除しました。');

                // 記事のブックマークを削除
                } else {
                    $item->bookmarks()->delete();

                    return back()->with('success', 'ブックマークを削除しました。');
                }
            }

            // 記事が存在しない場合
            return back()->with('error', '指定された記事が見つかりませんでした。');
        }

        // POSTリクエストでない場合、リダイレクト
        return redirect('/items')->with('error', '指定された記事が見つかりませんでした。');
    }

    /**
     * 記事への通報（フラグを付けたり削除したりする）
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function flagItem(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // アイテムIDを取得
            $itemId = $request->input('item_id');

            // ユーザーが既にこのアイテムにフラグを付けているか確認
            $flag = Flag::where('user_id', Auth::id())->where('item_id', $itemId)->first();

            // 既にフラグが付いている場合、そのフラグを削除
            if ($flag) {

                // このアイテムに対するフラグの総数をカウント
                $flagCount = Flag::where('item_id', $itemId)->count();

                // 3つ以上になった場合、アイテムのステージを更新
                if ($flagCount >= 3) {
                    Item::where('id', $itemId)->update(['stage' => $request->input('stage')]);
                }

                $flag->delete();
                return response()->json(['status' => '通報を取り消しました。']);

            // フラグが付いていない場合、新しいフラグを作成
            } else {
                Flag::create([
                    'user_id' => Auth::id(),
                    'item_id' => $itemId,
                    'flag' => now(),
                ]);

                // このアイテムに対するフラグの総数をカウント
                $flagCount = Flag::where('item_id', $itemId)->count();

                // 3つ以上になった場合、アイテムを inactive に更新
                if ($flagCount >= 3) {
                    Item::where('id', $itemId)->update(['stage' => 'inactive']);
                }

                return response()->json(['status' => '通報処理が完了しました。']);
            }
        }

        return abort(404)->with('error', 'エラーが発生し処理が完了しませんでした。');
    }

    /**
     * 記事のブックマーク
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bookmarkItem(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // アイテムIDを取得
            $itemId = $request->input('itemId');

            // ユーザーが既にこのアイテムにブックマークを付けているか確認
            $bookmark = Bookmark::where('user_id', Auth::id())->where('item_id', $itemId)->first();

            // 既にブックマークが付いている場合は削除
            if ($bookmark) {
                $bookmark->delete();  //  ファイルは削除しない
                return response()->json(['status' => 'ブックマークを取り消しました。']);

            // ブックマークが付いていない場合は作成
            } else {
                Bookmark::create([
                    'user_id' => Auth::id(),
                    'item_id' => $itemId,
                ]);

                return response()->json(['status' => 'ブックマークしました。']);
            }
        }

        return abort(404)->with('error', 'エラーが発生し処理が完了しませんでした。');
    }

    /**
     * 記事のピン
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pinItem(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // ユーザーが既にピンを付けている記事をカウントする
            $pinnedCount = Bookmark::where('user_id', Auth::id())->whereNotNull('pinned_at')->count();

            // ブックマークを取得
            $bookmarkId = $request->input('bookmarkId');
            $bookmark = Bookmark::find($bookmarkId);

            if (!$bookmark || $bookmark->user_id !== Auth::id()) {
                return back()->with('error', '指定された記事が見つかりませんでした。');
            }

            // ピンは最大4つまでの処理
            if ($pinnedCount < 4) {

                // ピンを外す
                if ($bookmark->pinned_at) {
                    $bookmark->pinned_at = null;
                    $bookmark->save();
                    return back()->with('success', 'ピンを外しました。');

                // ピンを付ける
                } else {
                    $bookmark->pinned_at = now();
                    $bookmark->save();
                    return back()->with('success', 'ピンを付けました。');
                }

            // ピンが5つを超えないようにする処理
            } else {

                // ピンを外す
                if ($bookmark->pinned_at) {
                    $bookmark->pinned_at = null;
                    $bookmark->save();
                    return back()->with('success', 'ピンを外しました。');

                // ピンを付ける
                } else {
                    $bookmark->pinned_at = now();
                    $bookmark->save();

                    return back()->with('success', 'ピンが付けられる最大4件の上限に達しました。ピンを外す記事をクリックしてください。');
                }
            }
        }

        return abort(404)->with('error', 'エラーが発生し処理が完了しませんでした。');
    }

    /**
     * 古いピンを保持して新しいピンを無視する
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function keepOldPin(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // 処理を続けるためのロジック
            $bookmarkId = $request->input('bookmarkId');

            return response()->json(['status' => '過去に付けたピンを保持しました。']);
        }

        return abort(404)->with('error', 'エラーが発生し処理が完了しませんでした。');
    }

    /**
     * 古いピンを外して新しいピンを付ける
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function replaceOldPin(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            $oldBookmarkId = $request->input('old_bookmarkId');
            $newBookmarkId = $request->input('new_bookmarkId');

            // 古いピンを外す
            $oldBookmark = Bookmark::find($oldBookmarkId);
            if ($oldBookmark) {
                $oldBookmark->pinned_at = null;
                $oldBookmark->save();
            }

            // 新しいピンを付ける
            $newBookmark = Bookmark::find($newBookmarkId);
            if ($newBookmark) {
                $newBookmark->pinned_at = now();
                $newBookmark->save();
            }

            return response()->json(['status' => '過去に付けたピンを外し、新しい記事にピンを付けなおしました。']);
        }

    return abort(404)->with('error', 'エラーが発生し処理が完了しませんでした。');
}

    /**
     * スクリーンショットを生成
     *
     * @param string $url
     * @param string $itemId
     * @return string
     */
    public function generateScreenshot($url, $itemId)
    {
        // スクリーンショットの保存先パスを設定
        $path = base_path('tmp/bookmarks/' . $itemId . '.png');

        // ストレージパスを設定
        $storagePath = base_path('tmp/bookmarks');

        // screenshot.jsのパスを設定
        $screenshotScriptPath = base_path('screenshot.js');

        // screenshot.jsを起動するコマンドを生成
        $command = "node $screenshotScriptPath $url $path $storagePath";
        // dd($command);

        // コマンドを実行
        exec($command, $output, $return);

        // 出力をスペースで分割し、ファイルパスの部分を取得
        $filePath = explode(' ', $output[0])[1];

        // コマンドの実行結果をログに記録
        if ($return === 0) {
            \Log::info('Screenshot saved successfully.');
        } else {
            \Log::error('Failed to generate screenshot.');
        }

        return $filePath;
    }

    /**
     * 管理用スクリーンショット生成プロセス
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function handleBookmarkChecked()
    {
        // スクリーンショットが未生成のブックマークを取得
        $bookmarks = Bookmark::with('item')->whereNull('thumbnail')->get();

        // 各ブックマークに対してスクリーンショットを生成
        foreach ($bookmarks as $bookmark) {
            $filePath = $this->generateScreenshot($bookmark->item->url, $bookmark->item->id);

            // 画像データの読み込みとBase64エンコード
            $imageData = file_get_contents($filePath);
            $base64Image = base64_encode($imageData);
            $mimeType = 'image/png';

            // ブックマークを更新してスクリーンショットを保存
            Bookmark::where('item_id', $bookmark->item->id)->update([
                'thumbnail' => 'data:' . $mimeType . ';base64,' . $base64Image,
            ]);
        }

        return response()->json(['message' => 'Bookmark screenshot generation process started.']);
    }

// public function generateScreenshot($url, $itemId)
// {
//     // フォルダが存在しない場合は作成
//     $storagePath = '/tmp/bookmarks';
//     if (!file_exists($storagePath)) {
//         mkdir($storagePath, 0755, true);
//     }

//         // ファイルパスの生成
//         $filename = $itemId . '.png';
//         $path = $storagePath . '/' . $filename;

//         // プロセスの実行
//         $nodeScript = base_path('screenshot.js');
//         $nodePath = env('NODE_PATH', 'node'); // デフォルトは 'node'

//         // Node.js スクリプトを実行するためのプロセスを作成
//         $process = new Process([$nodePath, $nodeScript, $url, $path, $storagePath]);
//         $process->run();

//         // プロセスの実行結果を確認
//         if ($process->isSuccessful()) {
//             // 成功時の処理
//             return $path; // 成功した場合、生成されたファイルのパスを返す
//         } else {
//             // 失敗時の処理
//             $errorOutput = $process->getErrorOutput(); // エラー出力を取得

//             if (strpos($errorOutput, 'TimeoutError') !== false) {
//                 // タイムアウトエラーが発生した場合
//                 throw new \RuntimeException('Timeout error occurred while generating screenshot.');
//             } else {
//                 // その他のエラーが発生した場合
//                 $errorMessage = 'Failed to generate screenshot: ' . $errorOutput;

//                 // エラーメッセージを詳細にデバッグするための条件分岐
//                 if (strpos($errorOutput, 'ENOTCONN') !== false) {
//                     $errorMessage .= ' (ENOTCONN error occurred)';
//                 }
//                 if (strpos($errorOutput, 'ECONNRESET') !== false) {
//                     $errorMessage .= ' (ECONNRESET error occurred)';
//                 }

//                 throw new \RuntimeException($errorMessage);
//             }
//         }
//         dd($nodePath, $nodeScript, $url, $path);


//         // 画像データの読み込みとBase64エンコード
//         $imageData = file_get_contents($path);
//         $base64Image = base64_encode($imageData);
//         $mimeType = 'image/png';

//         // ブックマーク更新
//         Bookmark::where('id', $itemId)->update([
//             'thumbnail' => 'data:' . $mimeType . ';base64,' . $base64Image,
//         ]);

//         // ログの出力
//         Log::info('Screenshot generated successfully for item ' . $itemId . ' at path: ' . $path);

//         // 作成されたファイルのパスを返す
//         return $path;
// }

}
