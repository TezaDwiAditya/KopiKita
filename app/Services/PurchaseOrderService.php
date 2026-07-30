<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class PurchaseOrderService
{
    public function receive(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder): PurchaseOrder {
            $purchaseOrder = PurchaseOrder::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->id);

            if ($purchaseOrder->status === 'received') {
                throw new RuntimeException('Purchase order sudah diterima.');
            }

            if ($purchaseOrder->status === 'cancelled') {
                throw new RuntimeException('Purchase order yang dibatalkan tidak dapat diterima.');
            }

            if ($purchaseOrder->items->isEmpty()) {
                throw new RuntimeException('Purchase order belum memiliki item.');
            }

            foreach ($purchaseOrder->items as $item) {
                $ingredient = Ingredient::query()
                    ->lockForUpdate()
                    ->findOrFail($item->ingredient_id);

                $stockBefore = $ingredient->current_stock;
                $stockAfter = $stockBefore + $item->quantity;

                $ingredient->update([
                    'current_stock' => $stockAfter,
                    'price' => $item->unit_price,
                ]);

                $purchaseOrder->stockMovements()->create([
                    'ingredient_id' => $ingredient->id,
                    'user_id' => auth()->id(),
                    'type' => 'purchase',
                    'quantity' => $item->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'note' => "Penambahan stock dari {$purchaseOrder->po_number}.",
                ]);
            }

            $purchaseOrder->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            return $purchaseOrder->refresh();
        });
    }
}
