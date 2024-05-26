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
            return view('item.bookmark', compact('stage', 'titleNames', 'items', 'title_name', 'period', 'articles'))->with('requestSearch', $requestSearch)->with('urlInput', session('urlInput'));
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

            // スクリーンショットを生成
            $screenshotPath = $this->generateScreenshot($item->url, $item->id, 'bookmarks');

            if ($screenshotPath) {
                // Base64 エンコード
                $imageData = file_get_contents($screenshotPath);
                $base64Image = base64_encode($imageData);
                $mimeType = 'image/png';

                // ブックマーク登録
                Bookmark::create([
                    'user_id' => Auth::id(),
                    'item_id' => $item->id,
                    'thumbnail' => 'data:' . $mimeType . ';base64,' . $base64Image,
                ]);

            // スクリーンショットの生成に失敗した場合の処理
            } else {
                // エラーに関するログを出力
                Log::error('Failed to generate screenshot for item ID: ' . $item->id);

                // ブックマーク登録
                Bookmark::create([
                    'user_id' => Auth::id(),
                    'item_id' => $item->id,
                    'thumbnail' => null,
                ]);
            }

            return redirect("/items/{$period}")->with('success', '記事が登録されました。');
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
            $item = Item::findOrFail($request->id);
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
     * 記事削除
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

            // 記事が存在するか確認
            if ($item) {

                // 記事と関連するコメントを削除
                $item->posts()->delete();
                $item->delete();

                // スクリーンショットを削除
                $thumbnailPath = storage_path('app/public/bookmarks/' . $item->id . '.png');
                if (file_exists($thumbnailPath)) {
                    unlink($thumbnailPath);
                }

                return back()->with('success', '記事を削除しました。');
            } else {
                return back()->with('error', '指定された記事が見つかりませんでした。');
            }
        }

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

            // アイテムのURLを取得
            $url = $request->input('url');

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
     * スクリーンショットを生成
     *
     * @param string $url
     * @param string $name
     * @param string $dirname
     * @return string
     */
    public function generateScreenshot($url, $name, $dirname)
    {
        // フォルダが存在しない場合は作成
        $storagePath = '/tmp/' . $dirname;
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }
        Log::info('storagePath: ' . $storagePath);

        $filename = $name . '.txt';
        $filePath = $storagePath . '/' . $filename;
        Log::info('filePath: ' . $filePath);

        $content = 'Hello, Heroku!';

        // Node.js スクリプトを実行してファイルに書き込む
        $process = new Process(['node', base_path('testfile.js'), $storagePath, $filename, $content]);
        $process->run(function ($type, $buffer) {
            Log::info('Process output: ' . $buffer);
        });

        if (!$process->isSuccessful()) {
            Log::error('Process failed: ' . $process->getErrorOutput());
            return null;
        } else {
            Log::info('process: clear');
            return $filePath;
        }
    }
}
