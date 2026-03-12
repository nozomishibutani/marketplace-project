<?php

namespace App\Services;

use App\Models\Item;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\SoldOutException;
use App\Exceptions\SuspendedException;

class OrderService
{
    public function createOrder(array $data)
    {
        DB::transaction(function () use ($data) {
            $item = Item::lockForUpdate()->find($data['item_id']);

            if (!$item) {
                throw new \Exception();
            }

            $itemStatuses = $item->itemStatus();
            if ($itemStatuses['sold'] == true) {
                throw new SoldOutException();
            }

            if ($itemStatuses['suspended'] == true) {
                throw new SuspendedException();
            }

            Order::create([
                'user_id' => Auth::id(),
                'item_id' => $data['item_id'],
                'payment_id' => $data['payment_id'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'postcode' => preg_replace('/[^0-9]/', '', $data['postcode']),
                'address' => $data['address'],
                'building' => $data['building'] ?? null,
            ]);

            $item->update(['status' => Item::STATUS_SOLD]);
        });
    }
}