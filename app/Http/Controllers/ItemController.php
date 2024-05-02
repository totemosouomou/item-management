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
     * 商品一覧
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

        // 商品一覧取得
        if ($user_id) {
            $user_name = $user->name . "さんの商品";
            $items = Item::where('user_id', $user_id)->get();
        } else {
            $user_name = "全商品";
            $items = Item::all();
        }

    return view('item.index', compact('items', 'user_name'));

    }

    /**
     * 四半期中の商品一覧
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function quarterItems()
    {
        $startOfQuarter = Carbon::now()->startOfQuarter();
        $endOfQuarter = Carbon::now()->endOfQuarter();
        $items = Item::whereBetween('created_at', [$startOfQuarter, $endOfQuarter])->get();
        $user_name = "四半期中の商品";
        return view('item.index', compact('items', 'user_name'));
    }

    /**
     * 30日以内の商品一覧
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function last30DaysItems()
    {
        $startOfLast30Days = Carbon::now()->subDays(30)->startOfDay();
        $items = Item::where('created_at', '>=', $startOfLast30Days)->get();
        $user_name = "30日以内の商品";
        return view('item.index', compact('items', 'user_name'));
    }

    /**
     * 1週間以内の商品一覧
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function lastWeekItems()
    {
        $startOfLastWeek = Carbon::now()->subWeek()->startOfWeek();
        $items = Item::where('created_at', '>=', $startOfLastWeek)->get();
        $user_name = "1週間以内の商品";
        return view('item.index', compact('items', 'user_name'));
    }

    /**
     * 商品登録
     */
    public function add(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {
            // バリデーション
            $this->validate($request, [
                'name' => 'required|max:100',
            ]);

            // 商品登録
            Item::create([
                'user_id' => Auth::user()->id,
                'name' => $request->name,
                'type' => $request->type,
                'detail' => $request->detail,
            ]);

            return redirect('/items');
        }

        return view('item.add');
    }

    /**
     * 商品削除
     */
    public function delete(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {
            // 削除する商品を取得
            $item = Item::find($request->id);

            // 商品が存在するか確認
            if ($item) {
                // 商品を削除
                $item->delete();

                return redirect('/items')->with('success', '商品を削除しました');
            } else {
                return redirect('/items')->with('error', '指定された商品が見つかりませんでした');
            }
        }

        return redirect('/items')->with('error', '指定された商品が見つかりませんでした');
    }
}
