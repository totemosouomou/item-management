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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        if ($request->isMethod('post')) {
            $requestSearch = $request->input('search');
            $request->session()->put('requestSearch', $requestSearch);
            return redirect()->route('index');
        }

        $itemsPerUser = Item::select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        $posts = Post::orderBy('created_at', 'desc')->take(30)->get();

        // Trait内のメソッドを呼び出し、ユーザーの作成日から期間を取得する
        $createdAt = Auth::user()->created_at;
        $period = $this->getPeriodFromCreationDate($createdAt);
        $randomItem = $this->getRandomItemByPeriod($period);

        // ビューにデータを渡す
        return view('home', compact('itemsPerUser', 'posts', 'randomItem'));
    }

    /**
     * ランダムな期間に基づくアイテムを取得する
     *
     * @param string $period
     * @return mixed
     */
    public function getRandomItemByPeriod($period)
    {
        return Item::with('posts')->where('stage', $period)->inRandomOrder()->first();
    }
}
