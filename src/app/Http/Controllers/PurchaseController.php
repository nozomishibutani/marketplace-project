<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Profile;
use App\Exceptions\SoldOutException;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AddressRequest;
use App\Http\Requests\PurchaseRequest;
use Stripe\Stripe;
use App\Services\PurchaseService;
use App\Http\Controllers\Controller;

class PurchaseController extends Controller
{
    private PurchaseService $purchaseService;

    public function __construct(PurchaseService $purchaseService) {
        $this->purchaseService = $purchaseService;
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function confirm(Request $request, $item_id) {
        $item = Item::select('id', 'user_id', 'name', 'img', 'price', 'status')->find($item_id);
        if (!$item) {
            return $this->redirectItemNotAvailable();
        }
        // お支払方法
        $paymentMethod = $request->input('payment_method');
        // 値段にコンマ追加
        $item->price = number_format($item->price);

        // 配送先変更からのリダイレクト
        if (session()->hasOldInput()) {
            $address['postcode'] = $request->old('postcode');
            $address['address']  = $request->old('address');
            $address['building'] = $request->old('building');
            $paymentMethod = $request->old('paymentMethod');

            return view('confirm', compact('item', 'address', 'paymentMethod'));
        }

        // 最初の遷移
        if (!$paymentMethod) {
            $address = Profile::where('user_id', Auth::id())
                ->select('postcode', 'address', 'building')
                ->first();

            if($address){
                // プロフィール登録あり
                $address['postcode'] = substr($address['postcode'], 0, 3) . '-' . substr($address['postcode'], 3);
                $address['address'] = $address['address'];
                $address['building'] = $address['building'] ?? null;
            }else{
                $address['postcode'] = null;
                $address['address'] = null;
                $address['building'] = null;
            }

            return view('confirm', compact('item', 'address', 'paymentMethod'));
        }

        // 支払い方法変更時の遷移
        // フォーム内容を表示
        $address = $request->only(['postcode', 'address', 'building']);

        return view('confirm', compact('item', 'address', 'paymentMethod'));
    }

    public function editAddress(Request $request, $item_id) {
        // 支払い方法
        $paymentMethod = $request->input('payment_method');

        return view('edit_address', compact('item_id', 'paymentMethod'));
    }

    public function updateAddress(AddressRequest $request, $item_id) {
        // 支払い方法
        $paymentMethod = $request->input('payment_method');
        $address = $request->only(['postcode', 'address', 'building']);

        $inputData = array_merge($address, ['paymentMethod' => $paymentMethod]);

        return redirect()->route('purchase.confirm', ['item_id' => $item_id])->withInput($inputData);
    }

    public function store(PurchaseRequest $request, $item_id) {
        try {
            $item = Item::find($item_id);

            if ($item->isSold()) {
                throw new SoldOutException();
            }

            $data = $request->validated();
            $data['payment_method'] = (int)$data['payment_method'];
            $data['item_id'] = $item_id;
            $data['name'] = $item->name;
            $data['price'] = $item->price;

            $url = $this->purchaseService->pay($data);

            // stripの決済画面にリダイレクト
            return redirect($url);

        } catch (SoldOutException) {
            return redirect()
                ->route('items.show', $item_id)
                ->with('alert', 'この商品は売り切れました')
                ->with('alert-type', 'alert-error');
        } catch (\RuntimeException $e) {
            return redirect()
                ->route('items.show', $item_id)
                ->with('alert', $e->getMessage())
                ->with('alert-type', 'alert-error');

        } catch (\Exception $e) {
            Log::error($e);
            return redirect()
                ->route('items.index')
                ->with('alert', 'システムエラーが発生しました')
                ->with('alert-type', 'alert-error');
        }
    }

    public function success(Request $request)
    {
        // Stripeセッション取得 & 注文作成
        // ※Stripe APIやDBで例外が発生する可能性があるため、このメソッド内で捕まえる
        try {

            $this->purchaseService->handleSuccess($request->session_id);

            return redirect()->route('items.index')
                    ->with('alert', 'ご購入ありがとうございました。')
                    ->with('alert-type', 'alert-success');

        } catch (SoldOutException) {
            /** @var \Stripe\Checkout\Session $session */
            return redirect()
                ->route('items.show', $session->metadata->item_id)
                ->with('alert', 'この商品は売り切れのため、現在購入できません。<br>返金処理しました')
                ->with('alert-type', 'alert-error');
        } catch (\Exception $e) {
            Log::error($e);
            return redirect()
                ->route('items.index')
                ->with('alert', 'システムエラーが発生しました')
                ->with('alert-type', 'alert-error');
            }
    }

    public function cancel(){
        return redirect()
            ->route('items.index')
            ->with('alert', '決済エラーが発生しました、もう一度やり直してください')
            ->with('alert-type', 'alert-error');
    }
}