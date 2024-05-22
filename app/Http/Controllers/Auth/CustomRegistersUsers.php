<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\Session;
use App\Models\User;

trait CustomRegistersUsers
{
    use RedirectsUsers;

    /**
     * Show the application registration form.
     *
     * @param  string|null  $token
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm($token = null)
    {
        // セッションからエラーメッセージを削除
        Session::forget('error');

        // トークンがない場合の処理
        if (!$token) {
            return view('auth.register');
        }

        // トークンに対応するユーザーを検索
        $user = User::where('remember_token', $token)->first();

        // ユーザーが見つからない場合の処理
        if (!$user) {
            Session::flash('error', 'トークンが無効です。');
        }

        // トークンが有効な場合の処理
        return view('auth.registerUser', compact('token'));
    }

    /**
     * Handle a registration request for the application.
     *
     * @param \Illuminate\Http\Request $request
     * @param string|null $token
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request, $token = null)
    {
        // トークンがある場合の処理
        if ($token) {
            // バリデーションを実行
            $validator = $this->passwordValidator($request->all());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // ユーザー情報を更新
            $user = $this->update($request->all(), $token);
        }

        // トークンがない場合の処理
        else {
            // バリデーションを実行
            $validator = $this->validator($request->all());
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            // 新しいユーザーを作成
            $user = $this->create($request->all());
        }

        // 登録完了イベントを発行
        event(new Registered($user));

        // ユーザーをログインさせる
        $this->guard()->login($user);

        // 登録後のリダイレクト先を指定
        return $this->registered($request, $user) ?: redirect($this->redirectPath());
    }

    /**
     * バリデーションルール（パスワード変更用）
     *
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function passwordValidator(array $data)
    {
        return Validator::make($data, [
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * ユーザー情報の更新（トークンを使用）
     *
     * @param array $data
     * @param string $token
     * @return \App\Models\User
     */
    protected function update(array $data, $token)
    {
        $user = User::where('remember_token', $token)->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->remember_token = null;
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    /**
     * ガードの取得
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Auth::guard();
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function registered(Request $request, $user)
    {
        //
    }
}
