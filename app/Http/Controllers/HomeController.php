<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
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
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $itemsPerUser = Item::select('user_id', DB::raw('count(*) as total'))
            ->groupBy('user_id')
            ->with('user') // 'user' はItemモデルに定義されたユーザーリレーションを指します
            ->get();

        // ビューにデータを渡す
        return view('home', compact('itemsPerUser'));
    }
}
