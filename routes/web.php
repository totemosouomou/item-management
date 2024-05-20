<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('register/{token?}', [App\Http\Controllers\Auth\RegisterController::class, 'showRegistrationForm'])->where('token', '(.*)');
Route::post('register/{token?}', [App\Http\Controllers\Auth\RegisterController::class, 'register'])->where('token', '(.*)')->name('register.token');
Route::get('/users/add', [App\Http\Controllers\UserController::class, 'create']);
Route::match(['get', 'post'], '/users/store', [App\Http\Controllers\UserController::class, 'store']);

Auth::routes();

Route::match(['get', 'post'], '/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::prefix('items')->group(function () {
    // 記事一覧を表示するルート
    Route::match(['get', 'post'], '/', [App\Http\Controllers\ItemController::class, 'index'])->name('index');
    // 特定のユーザーに関連付けられた記事一覧を表示するルート
    Route::match(['get', 'post'], '/user/{user_id}', [App\Http\Controllers\ItemController::class, 'index'])->name('user');
    // 期間に応じた記事一覧を表示するルート
    Route::match(['get', 'post'], '/term', [App\Http\Controllers\ItemController::class, 'index'])->name('term');
    Route::match(['get', 'post'], '/quarter', [App\Http\Controllers\ItemController::class, 'index'])->name('quarter');
    Route::match(['get', 'post'], '/month', [App\Http\Controllers\ItemController::class, 'index'])->name('month');
    Route::match(['get', 'post'], '/week', [App\Http\Controllers\ItemController::class, 'index'])->name('week');

    Route::match(['get', 'post'], '/add/{urlInput?}', [App\Http\Controllers\ItemController::class, 'add'])->where('urlInput', '(.*)');
    Route::match(['get', 'post'], '/update', [App\Http\Controllers\ItemController::class, 'update']);
    Route::match(['get', 'post'], '/delete', [App\Http\Controllers\ItemController::class, 'delete']);
});
