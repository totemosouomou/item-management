<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Traits\PeriodCalculator;
use Carbon\Carbon;
use App\Models\User;
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

        // ユーザー情報を取得
        $user = Auth::user();

        // 入校日が存在する場合、それを使用
        if ($user->date_of_enrollment) {
            $enrollmentColumn = "date_of_enrollment";
            $enrollmentDate = Carbon::parse($user->date_of_enrollment);
        } else {
            $enrollmentColumn = "created_at";
            $enrollmentDate = Carbon::parse($user->created_at);
        }

        // グラフへ渡す同期ユーザーごとの記事の投稿数を取得
        $month = $enrollmentDate->month;

        $usersWithSameYearMonth = User::whereYear($enrollmentColumn, $enrollmentDate->year)
            ->whereMonth($enrollmentColumn, $month)
            ->pluck('id');

        $itemsPerUser = Item::whereIn('user_id', $usersWithSameYearMonth)
            ->select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->with('user')
            ->get();

        // コメント一覧を取得
        $posts = Post::orderBy('created_at', 'desc')->take(30)->get();

        // Trait内のメソッドを呼び出し、ユーザーのステージを取得
        $period = $this->getPeriodFromCreationDate();
        $randomItem = $this->getRandomItemByPeriod($period);

        return view('home', compact('month', 'itemsPerUser', 'posts', 'randomItem'));
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
