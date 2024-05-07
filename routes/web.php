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
    // 記事一覧を表示するルート
    Route::get('/', [App\Http\Controllers\ItemController::class, 'index']);
    // 特定のユーザーに関連付けられた記事一覧を表示するルート
    Route::get('/user/{user_id}', [App\Http\Controllers\ItemController::class, 'index'])->name('index.user');
    // 期間に応じた記事一覧を表示するルート
    Route::get('/term', [App\Http\Controllers\ItemController::class, 'stageItems'])->name('term');
    Route::get('/quarter', [App\Http\Controllers\ItemController::class, 'stageItems'])->name('quarter');
    Route::get('/month', [App\Http\Controllers\ItemController::class, 'stageItems'])->name('month');
    Route::get('/week', [App\Http\Controllers\ItemController::class, 'stageItems'])->name('week');

    Route::get('/add', [App\Http\Controllers\ItemController::class, 'add']);
    Route::post('/add', [App\Http\Controllers\ItemController::class, 'add']);
    Route::get('/update', [App\Http\Controllers\ItemController::class, 'update']);
    Route::post('/update', [App\Http\Controllers\ItemController::class, 'update']);
    Route::get('/delete', [App\Http\Controllers\ItemController::class, 'delete']);
    Route::post('/delete', [App\Http\Controllers\ItemController::class, 'delete']);
});
