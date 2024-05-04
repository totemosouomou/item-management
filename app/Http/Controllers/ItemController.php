<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Item;
use Carbon\Carbon;

class ItemController extends Controller
{
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
     * バリデーションルールの定義
     *
     * @return array
     */
    private function validationRules()
    {
        return [
            'url' => 'required|url|starts_with:http,https',
            'name' => 'required|string|max:100',
            'detail' => 'nullable|string|max:500',
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
            'name.required' => '見出しは必須項目です。',
            'name.string' => '見出しは文字列で入力してください。',
            'name.max' => '見出しは100文字以内で入力してください。',
            'detail.string' => '詳細は文字列で入力してください。',
            'detail.max' => '詳細は500文字以内で入力してください。',
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
            return redirect()->route('index.user', ['user_id' => auth()->id()]);
        }

        // バリデーション: ユーザーIDが指定されている場合は存在するかチェックする
        if ($user_id) {
            $user = User::find($user_id);
            if (!$user) {
                return redirect('/items')->with('error', '指定されたユーザーが見つかりませんでした');
            }
        }

        // 記事一覧取得
        if ($user_id) {
            $user_name = $user->name . "さんの記事";
            $items = Item::where('user_id', $user_id)->get();
        } else {
            $user_name = "全記事";
            $items = Item::all();
        }

    return view('item.index', compact('items', 'user_name'));

    }

    /**
     * 四半期中の記事一覧
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function quarterItems()
    {
        $startOfQuarter = Carbon::now()->startOfQuarter();
        $endOfQuarter = Carbon::now()->endOfQuarter();
        $items = Item::whereBetween('created_at', [$startOfQuarter, $endOfQuarter])->get();
        $user_name = "四半期中の記事";
        return view('item.index', compact('items', 'user_name'));
    }

    /**
     * 30日以内の記事一覧
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function last30DaysItems()
    {
        $startOfLast30Days = Carbon::now()->subDays(30)->startOfDay();
        $items = Item::where('created_at', '>=', $startOfLast30Days)->get();
        $user_name = "30日以内の記事";
        return view('item.index', compact('items', 'user_name'));
    }

    /**
     * 1週間以内の記事一覧
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function lastWeekItems()
    {
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $items = Item::where('created_at', '>=', $startOfLastWeek)->get();
        $user_name = "1週間以内の記事";
        return view('item.index', compact('items', 'user_name'));
    }

    /**
     * 記事登録
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function add(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // バリデーションを実行
            $request->validate($this->validationRules(), $this->validationMessages());

            // 記事登録
            Item::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'url' => $request->url,
                'detail' => $request->detail,
            ]);

            return redirect('/items');
        }

        return view('item.add');
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

            // バリデーションを実行
            $request->validate($this->validationRules(), $this->validationMessages());

            // 記事更新
            Item::where('id', $request->id)->update([
                'name' => $request->name,
                'url' => $request->url,
                'detail' => $request->detail,
            ]);

            return redirect('/items')->with('success', '記事が更新されました。');
        }

        return view('item.index');
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
                // 記事を削除
                $item->delete();

                return redirect('/items')->with('success', '記事を削除しました');
            } else {
                return redirect('/items')->with('error', '指定された記事が見つかりませんでした');
            }
        }

        return redirect('/items')->with('error', '指定された記事が見つかりませんでした');
    }
}
