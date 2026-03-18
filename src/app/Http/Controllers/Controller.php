<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Http\RedirectResponse;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * 商品が存在しない場合の共通リダイレクト処理
     */
    protected function redirectItemNotAvailable(): RedirectResponse {
        return redirect()->route('items.index')
            ->with('alert','この商品は削除されたか、現在表示できません。')
            ->with('alert-type','alert-error');
    }
}
