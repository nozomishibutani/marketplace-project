<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\SearchController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::middleware('auth')->group(function () {
    // ログイン時のみ遷移可能
    Route::get('/purchase/{item_id}', [ItemController::class, 'confirm'])->name('purchase.confirm');
    Route::get('/purchase/address/{item_id}', [ItemController::class, 'editAddress'])->name('purchase.edit');
    Route::post('/purchase/address/update', [ItemController::class, 'updateAddress'])->name('purchase.update');
    Route::post('/purchase/{item_id}/store', [ItemController::class, 'store'])->name('purchase.store');
    // 未ログイン時はログイン画面に遷移する
    Route::post('/item/{item_id}/comment', [ItemController::class, 'comment'])->name('items.comment');
    Route::get('/item/{item_id}/favorite', [ItemController::class, 'favorite'])->name('items.favorite');
    Route::get('/item/{item_id}/unfavorite', [ItemController::class, 'unfavorite'])->name('items.unfavorite');

});
// 未ログイン時でも閲覧可能
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');
Route::get('/search', [SearchController::class, 'index'])->name('search');
