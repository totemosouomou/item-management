<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PeriodCalculator;
use App\Http\Controllers\Traits\ArticleController;
use App\Models\User;
use App\Models\Item;
use App\Models\Post;

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
    private function pagination(): int
    {
        return 10;
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
        // 検索機能
        if ($request->clear) {
            $requestSearch = "";
        } elseif ($request->input('search')) {
            $requestSearch = $request->input('search');
        } else {
            $requestSearch = $request->session()->get('requestSearch', '');
        }
        $request->session()->put('requestSearch', $requestSearch);

        // ユーザーIDが指定されている場合の処理
        if ($user_id == "admin") {
            return redirect()->route('user', ['user_id' => auth()->id()]);
        }

        if ($user_id) {
            $user = User::find($user_id);
            if (!$user) {
                return redirect('/items')->with('error', '指定されたユーザーが見つかりませんでした。');
            }
        }

        // 記事のステージが指定されている場合の処理
        $stage = request()->route()->getName();
        $titleNames = [
            'week' => '1週間以内の記事',
            'month' => '基礎課題の記事',
            'quarter' => '応用課題の記事',
            'term' => '開発課題の記事',
        ];

        $title_name = isset($titleNames[$stage]) ? $titleNames[$stage] : "全記事";

        if ($user_id) {
            $title_name = $user->name . "さんの記事";
            $items = Item::with('posts')->where('user_id', $user_id)->where('title', 'like', '%' . $this->secure($requestSearch) . '%')->orderBy('created_at', 'desc')->paginate($this->pagination());
        } elseif ($title_name !== '全記事') {
            $items = Item::with('posts')->where('stage', $stage)->where('title', 'like', '%' . $this->secure($requestSearch) . '%')->orderBy('created_at', 'desc')->paginate($this->pagination());
        } else {
            $items = Item::with('posts')->where('title', 'like', '%' . $this->secure($requestSearch) . '%')->orderBy('created_at', 'desc')->paginate($this->pagination());
        }

        // Trait内のメソッドを呼び出し、ユーザーのステージを取得
        $period = $this->getPeriodFromCreationDate();

        // Trait内のメソッドを呼び出し、指定された検索語に基づくQiitaの記事を取得
        $articles = $this->getQiitaArticles($requestSearch);

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
        // Trait内のメソッドを呼び出し、ユーザーのステージを取得
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
                'user_id' => Auth::user()->id,
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
                $securePost = $this->secure($request->input('post'));

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
            } else {

                // 変更がない場合はback
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

                return back()->with('success', '記事を削除しました。');
            } else {
                return back()->with('error', '指定された記事が見つかりませんでした。');
            }
        }

        return redirect('/items')->with('error', '指定された記事が見つかりませんでした。');
    }
}
