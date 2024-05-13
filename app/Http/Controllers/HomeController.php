<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\PeriodCalculator;
use App\Models\Item;
use App\Models\Post;

class HomeController extends Controller
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
     * ダッシュボードを表示
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        // 検索機能
        if ($request->isMethod('post')) {
            $requestSearch = $request->input('search');
            $request->session()->put('requestSearch', $requestSearch);
            return redirect()->route('index');
        }

        // グラフへ渡すユーザーごとの記事の投稿数を取得
        $itemsPerUser = Item::select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $posts = Post::orderBy('created_at', 'desc')->take(30)->get();

        // Trait内のメソッドを呼び出し、ユーザーのステージを取得
        $period = $this->getPeriodFromCreationDate();
        $randomItem = $this->getRandomItemByPeriod($period);

        return view('home', compact('itemsPerUser', 'posts', 'randomItem'));
    }

    /**
     * ステージに基づく記事をランダムでひとつ取得
     *
     * @param string $period
     * @return mixed
     */
    public function getRandomItemByPeriod($period)
    {
        return Item::with('posts')->where('stage', $period)->inRandomOrder()->first();
    }
}
