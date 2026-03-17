<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\SoldOutException;

class OrderService
{
    public function createOrder(array $data)
    {
        DB::transaction(function () use ($data) {
            $item = Item::lockForUpdate()->find($data['item_id']);

            if (!$item) {
                throw new \Exception();
            }

            if ($item->isSold() === true) {
                throw new SoldOutException();
            }

            Order::create([
                'user_id' => Auth::id(),
                'item_id' => $data['item_id'],
                'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
                'address' => $data['address'],
                'building' => $data['building'] ?? null,
                'payment_id' => $data['payment_id'],
                'payment_method' => $data['payment_method'],
                'payment_status' => $data['payment_status'],
                'payment_expires_at' => $data['payment_expires_at'],
            ]);

            $item->update(['status' => Item::STATUS_SOLD]);
        });
    }
}