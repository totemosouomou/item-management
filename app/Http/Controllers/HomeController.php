<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\Detail;

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
            ->with('user')
            ->get();

        $details = Detail::orderBy('created_at', 'desc')->take(30)->get();

        // ビューにデータを渡す
        return view('home', compact('itemsPerUser', 'details'));
    }
}
