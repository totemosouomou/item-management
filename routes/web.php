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

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::prefix('items')->group(function () {
    // 商品一覧を表示するルート
    Route::get('/', [App\Http\Controllers\ItemController::class, 'index']);
    // 特定のユーザーに関連付けられた商品一覧を表示するルート
    Route::get('/user/{user_id}', [App\Http\Controllers\ItemController::class, 'index'])->name('index.user');
    // 四半期中の商品一覧を表示するルート
    Route::get('/quarter', [App\Http\Controllers\ItemController::class, 'quarterItems'])->name('items.quarter');
    // 30日以内の商品一覧を表示するルート
    Route::get('/30days', [App\Http\Controllers\ItemController::class, 'last30DaysItems'])->name('items.last30days');
    // 1週間以内の商品一覧を表示するルート
    Route::get('/week', [App\Http\Controllers\ItemController::class, 'lastWeekItems'])->name('items.lastweek');

    Route::get('/add', [App\Http\Controllers\ItemController::class, 'add']);
    Route::post('/add', [App\Http\Controllers\ItemController::class, 'add']);
    Route::get('/delete', [App\Http\Controllers\ItemController::class, 'delete']);
    Route::post('/delete', [App\Http\Controllers\ItemController::class, 'delete']);
});
