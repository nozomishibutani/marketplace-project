<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\EmailVerificationController;

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
// メール認証誘導画面
Route::get('/verify/notice', [EmailVerificationController::class, 'notice'])
    ->middleware('auth')
    ->name('verification.notice');

// メール認証
Route::get('/email/verify/', [EmailVerificationController::class, 'verify'])
    ->middleware('auth')
    ->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
    $request->user()->sendEmailVerificationNotification();
    return back()->with('message', 'Verification link sent!');
})->middleware(['auth', 'throttle:6,1'])->name('verification.resend');



Route::middleware('auth','verified')->group(function () {
    // 購入手続きへ
    Route::get('/purchase/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm')->whereNumber('item_id');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'confirm'])->name('purchase.confirm')->whereNumber('item_id');
    Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.edit');
    Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'editAddress'])->name('purchase.edit');
    Route::post('/purchase/address/{item_id}/update', [PurchaseController::class, 'updateAddress'])->name('purchase.update');
    Route::post('/purchase/{item_id}/store', [PurchaseController::class, 'store'])->name('purchase.store');

    // stripe決済
    Route::get('/purchase/success', [PurchaseController::class, 'success'])->name('purchase.success');
    Route::get('/purchase/cancel', [PurchaseController::class, 'cancel'])->name('purchase.cancel');

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

    // コメント
    Route::post('/item/{item_id}/comment', [ItemController::class, 'comment'])->name('items.comment');
    // いいね
    Route::post('/item/{item_id}/favorite', [ItemController::class, 'favorite'])->name('items.favorite');
    Route::delete('/item/{item_id}/unfavorite', [ItemController::class, 'unfavorite'])->name('items.unfavorite');
});

Route::middleware('verified')->group(function () {
// 未ログイン時でも利用可能
Route::get('/', [ItemController::class, 'index'])->name('items.index');
Route::get('/item/{item_id}', [ItemController::class, 'show'])->name('items.show');
Route::get('/search', [ItemController::class, 'search'])->name('search');
});

