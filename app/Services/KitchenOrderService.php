<?php

namespace App\Services;

use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class KitchenOrderService
{
    public function markPreparing(TransactionItem $item): TransactionItem
    {
        return $this->updateStatus($item, 'preparing');
    }

    public function markReady(TransactionItem $item): TransactionItem
    {
        return $this->updateStatus($item, 'ready');
    }

    public function markServed(TransactionItem $item): TransactionItem
    {
        return $this->updateStatus($item, 'served');
    }

    private function updateStatus(TransactionItem $item, string $status): TransactionItem
    {
        if (! in_array($status, ['pending', 'preparing', 'ready', 'served'], true)) {
            throw new InvalidArgumentException('Status dapur tidak valid.');
        }

        return DB::transaction(function () use ($item, $status): TransactionItem {
            $item = TransactionItem::query()
                ->lockForUpdate()
                ->findOrFail($item->id);

            $data = ['kitchen_status' => $status];

            if ($status === 'preparing') {
                $data['preparing_at'] = $item->preparing_at ?? now();
            }

            if ($status === 'ready') {
                $data['preparing_at'] = $item->preparing_at ?? now();
                $data['ready_at'] = $item->ready_at ?? now();
            }

            if ($status === 'served') {
                $data['preparing_at'] = $item->preparing_at ?? now();
                $data['ready_at'] = $item->ready_at ?? now();
                $data['served_at'] = $item->served_at ?? now();
            }

            $item->update($data);

            return $item->refresh();
        });
    }
}
