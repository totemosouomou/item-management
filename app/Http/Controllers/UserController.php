<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('users.add');
    }

    /**
     * Store a newly created user in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // POSTリクエストのとき
        if ($request->isMethod('post')) {

            // 日付を特定の形式でフォーマットして名前に追加
            $enrollmentDate = date('n月j日', strtotime($request->date_of_enrollment));
            $request->merge(['name' => $request->name . '/' . $enrollmentDate]);

            // バリデーションを実行
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:50',
                'email' => 'required|email|unique:users',
                'date_of_enrollment' => 'required|date',
            ]);

            // バリデーションが失敗した場合、エラーメッセージと入力データを返してリダイレクト
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // トークンを生成
            $token = Hash::make($request['name'] . $request['email']);
            $token = substr($token, 40, 60);

            // 新しいユーザーを作成
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'remember_token' => $token,
                'date_of_enrollment' => $request->date_of_enrollment,
                'password' => Hash::make('defaultpassword'), // 初期パスワードを設定
            ]);

            // ユーザー追加ページにリダイレクトし、生成された登録URLと名前をセッションに保存
            return redirect('/users/add')->with('success', [
                'message' => '正常に登録完了しました。',
                'name' => explode('/', $request->name)[0],
                'email' => $request->email,
                'url' => url('register') . '/' . urlencode($token)
            ]);
        }

        return abort(404)->with('error', 'エラーで登録完了しませんでした。');
    }
}
