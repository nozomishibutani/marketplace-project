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

            if (!$item || !$item->isSale()) {
                throw new SoldOutException();
            }

            Order::create([
                'user_id' => Auth::id(),
                'item_id' => $data['item_id'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
                'address' => $data['address'],
                'building' => $data['building'] ?: null,
            ]);

            $item->update(['status' => Item::STATUS_SOLD]);
        });
    }
}