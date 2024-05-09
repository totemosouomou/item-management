<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\PeriodCalculator;
use App\Models\User;
use App\Models\Item;
use App\Models\Post;

class ItemController extends Controller
{
    use PeriodCalculator;

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
     * ページネーションに使用する数字を返すメソッド
     *
     * @return int
     */
    private function pagination(): int
    {
        return 10;
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
            'title' => 'required|string|max:100',
            'post' => 'nullable|string|max:200',
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
            'title.required' => '見出しは必須項目です。',
            'title.string' => '見出しは文字列で入力してください。',
            'title.max' => '見出しは100文字以内で入力してください。',
            'post.string' => 'コメントは文字列で入力してください。',
            'post.max' => 'コメントは255文字以内で入力してください。',
        ];
    }

    /**
     * 記事一覧
     *
     * @param int|null $user_id
     * @return \Illuminate\Contracts\View\View
     */
    public function index($user_id = null)
    {
        if ($user_id == "admin") {
            return redirect()->route('user', ['user_id' => auth()->id()]);
        }

        // バリデーション: ユーザーIDが指定されている場合は存在するかチェックする
        if ($user_id) {
            $user = User::find($user_id);
            if (!$user) {
                return redirect('/items')->with('error', '指定されたユーザーが見つかりませんでした。');
            }
        }

        $stage = request()->route()->getName();
        $titleNames = [
            'week' => '1週間以内の記事',
            'month' => '基礎課題の記事',
            'quarter' => '応用課題の記事',
            'term' => '開発課題の記事',
        ];

        // $titleNames 内に指定されたステージが存在するかチェックし、存在しない場合はデフォルトのタイトルを設定
        $title_name = isset($titleNames[$stage]) ? $titleNames[$stage] : "全記事";

        if ($user_id) {
            $title_name = $user->name . "さんの記事";
            $items = Item::with('posts')->where('user_id', $user_id)->orderBy('created_at', 'desc')->paginate($this->pagination());
        } elseif ($title_name !== '全記事') {
            $items = Item::with('posts')->where('stage', $stage)->orderBy('created_at', 'desc')->paginate($this->pagination());
        } else {
            $items = Item::with('posts')->orderBy('created_at', 'desc')->paginate($this->pagination());
        }

        // Trait内のメソッドを呼び出し、ユーザーの作成日から期間を取得する
        $createdAt = Auth::user()->created_at;
        $period = $this->getPeriodFromCreationDate($createdAt);

        return view('item.index', compact('stage', 'titleNames', 'items', 'title_name', 'period'));
    }

    /**
     * 記事登録
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        // Trait内のメソッドを呼び出し、ユーザーの作成日から期間を取得する
        $createdAt = Auth::user()->created_at;
        $period = $this->getPeriodFromCreationDate($createdAt);

        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // バリデーションを実行
            $request->validate($this->validationRules(), $this->validationMessages());

            // リクエストの値を period に再代入する
            if ($request->has('period')) {
                $period = $request->input('period');
            }

            // 記事登録
            $item = Item::create([
                'user_id' => Auth::user()->id,
                'title' => $request->input('title'),
                'url' => $request->input('url'),
                'stage' => $period,
            ]);

            // コメント登録
            $post = $request->input('post');
            if (!empty($post)) { // コメントが空でない場合のみ登録
                Post::create([
                    'user_id' => Auth::id(),
                    'item_id' => $item->id,
                    'post' => $post . " by " . Auth::user()->name,
                ]);
            }

            return redirect("/items/{$period}")->with('success', '記事が登録されました。');
        }

        return redirect("/items/{$period}")->with('add', '記事登録');
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

            if ($itemChanged || $postChanged) {

                // バリデーションを実行
                $request->validate($this->validationRules(), $this->validationMessages());

                // 記事更新
                Item::where('id', $request->id)->update([
                    'title' => $request->title,
                    'url' => $request->url,
                ]);

                // コメント更新
                if ($postBeforeUpdate) {
                    if ($request->post) {
                        $postBeforeUpdate->update([
                            'post' => $request->post . " by " . Auth::user()->name,
                        ]);
                    } else {
                        $postBeforeUpdate->delete();
                    }
                } elseif ($request->post) {
                    Post::create([
                        'user_id' => Auth::id(),
                        'item_id' => $request->id,
                        'post' => $request->post . " by " . Auth::user()->name,
                    ]);
                }

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

                return back()->with('success', '記事を削除しました。');
            } else {
                return back()->with('error', '指定された記事が見つかりませんでした。');
            }
        }

        return redirect('/items')->with('error', '指定された記事が見つかりませんでした。');
    }
}
