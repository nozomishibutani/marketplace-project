<?php

namespace App\Services;

use App\Services\OrderService;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Stripe\PaymentIntent;
use Stripe\Checkout\Session;

class PurchaseService
{
    private OrderService $orderService;

    public function __construct(OrderService $orderService) {
        $this->orderService = $orderService;
    }

    public function pay(array $data) {
        switch ($data['payment_method']) {

            case Order::PAYMENT_CONVENIENCE:
                // ------------------------------------------------------------------
                // NOTE:コンビニ払いの許容範囲（120円〜300,000円）
                // 範囲外の値をそのままStripeに渡すとエラーになり
                // 別箇所でのシステムエラーなのかどうかが分かりづらくなる。
                // 仕様にはないため暫定的にここで弾いている
                // ------------------------------------------------------------------
                if ($data['price'] < 120 || $data['price'] > 300000) {
                    throw new \RuntimeException('販売価格がstrip決済の対応外です');
                }
                return $this->konbiniPay($data);

            case Order::PAYMENT_CARD:
                // ------------------------------------------------------------------
                // NOTE:カード払いの許容範囲（50円〜）
                // 範囲外の値をそのままStripeに渡すとエラーになり
                // 別箇所でのシステムエラーなのかどうかが分かりづらくなる。
                // 仕様にはないため暫定的にここで弾いている
                // ------------------------------------------------------------------
                if ($data['price'] < 50) {
                    throw new \RuntimeException('販売価格がstrip決済の対応外です');
                }
                return $this->credit($data);

            default:
                throw new \Exception();
        }
    }

        public function konbiniPay(array $data)
        {
            /** @var \App\Models\User|null $user */
            $user = Auth::user();

            $intent = PaymentIntent::create([
                'amount' => $data['price'],
                'currency' => 'jpy',
                'payment_method_types' => ['konbini'],
                'confirm' => true,
                'payment_method_data' => [
                    'type' => 'konbini',
                    'billing_details' => [
                        'name' => $user->username,
                        'email' => $user->email,
                    ],
                ],
            ]);

            $paymentData = $this->createOrderFromKonbiniPay($intent);
            $this->orderService->createOrder(
                array_merge($data, $paymentData)
            );

            return ($paymentData['voucher_url']);
        }

        private function createOrderFromKonbiniPay($intent): array
        {
            $konbini = $intent->next_action->konbini_display_details;

            return [
                'payment_id' => $intent->id,
                'payment_status' => $intent->status,
                'payment_method' => Order::PAYMENT_CONVENIENCE,
                'payment_expires_at' => date('Y-m-d H:i:s', $konbini->expires_at),
                'voucher_url' => $konbini->hosted_voucher_url,
            ];
        }

    public function credit(array $data)
    {
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'jpy',
                    'product_data' => [
                        'name' => $data['name'],
                    ],
                    'unit_amount' => $data['price'],
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',

            'metadata' => [
                            'item_id' => $data['item_id'],
                            'user_id' => Auth::id(),
                            'postcode' => $data['postcode'],
                            'address' => $data['address'],
                            'building' => $data['building'],
                        ],

            'success_url' => route('purchase.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('purchase.cancel'),
        ]);

        return ($session->url);
    }

    public function handleSuccess(string $sessionId) {
        $session = Session::retrieve($sessionId);

        $this->createOrderFromCredit($session);
    }

    private function createOrderFromCredit(Session $session)
    {
        return $this->orderService->createOrder([
            'user_id' => $session->metadata->user_id,
            'item_id' => $session->metadata->item_id,
            'postcode' => $session->metadata->postcode,
            'address' => $session->metadata->address,
            'building' => $session->metadata->building,
            'payment_id' => $session->payment_intent,
            'payment_method' => Order::PAYMENT_CARD,
            'payment_status' => $session->payment_status,
            'payment_expires_at' => null,
        ]);
    }
}

