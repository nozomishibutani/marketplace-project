<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;

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
    // 購入手続きへ
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.edit');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.edit');
    Route::post('/purchase/address/{item_id}/update', [PurchaseController::class, 'updateAddress'])->name('purchase.update');
    Route::post('/purchase/{item_id}/store', [PurchaseController::class, 'store'])->name('purchase.store');

    // 商品出品
    Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
    Route::post('/sell', [ItemController::class, 'store'])->name('items.store');

    // プロフィール画面
    Route::get('/mypage', [ProfileController::class, 'index'])->name('profile.index');
    // 「プロフィールを編集」ボタン押下
    Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    // 「更新する」ボタン押下
    Route::post('/mypage/profile/store', [ProfileController::class, 'store'])->name('profile.store');
    Route::patch('/mypage/profile/store', [ProfileController::class, 'store'])->name('profile.store');

    // ログイン時のみアクション可能
    Route::post('/item/{item_id}/comment', [ItemController::class, 'comment'])->name('items.comment');
    Route::post('/item/{item_id}/favorite', [ItemController::class, 'favorite'])->name('items.favorite');
    Route::delete('/item/{item_id}/unfavorite', [ItemController::class, 'unfavorite'])->name('items.unfavorite');

});
// 未ログイン時でも利用可能
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');
Route::get('/search', [ItemController::class, 'search'])->name('search');
