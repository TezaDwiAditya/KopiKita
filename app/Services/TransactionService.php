<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TransactionService
{
    public function pay(Transaction $transaction, string $method, int $amountPaid): Transaction
    {
        return DB::transaction(function () use ($transaction, $method, $amountPaid): Transaction {
            $transaction = Transaction::query()
                ->with(['items.variant', 'items.menu.recipe.items.ingredient', 'payment'])
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if ($transaction->status === 'paid') {
                throw new RuntimeException('Transaksi sudah dibayar.');
            }

            if ($transaction->status === 'void') {
                throw new RuntimeException('Transaksi void tidak dapat dibayar.');
            }

            if ($amountPaid < $transaction->grand_total) {
                throw new RuntimeException('Uang bayar kurang dari grand total.');
            }

            foreach ($transaction->items as $item) {
                $recipe = $item->menu->recipe;

                if (! $recipe || ! $recipe->is_active) {
                    continue;
                }

                foreach ($recipe->items as $recipeItem) {
                    $quantityUsed = $recipeItem->quantity * $item->quantity;

                    $ingredient = Ingredient::query()
                        ->lockForUpdate()
                        ->findOrFail($recipeItem->ingredient_id);

                    if ($ingredient->current_stock < $quantityUsed) {
                        throw new RuntimeException("Stock {$ingredient->name} tidak cukup.");
                    }

                    $stockBefore = $ingredient->current_stock;
                    $stockAfter = $stockBefore - $quantityUsed;

                    $ingredient->update(['current_stock' => $stockAfter]);

                    $transaction->stockMovements()->create([
                        'ingredient_id' => $ingredient->id,
                        'user_id' => auth()->id(),
                        'type' => 'sale',
                        'quantity' => -$quantityUsed,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'note' => "Pengurangan stock transaksi {$transaction->invoice_number}.",
                    ]);
                }
            }

            $transaction->update(['status' => 'paid']);

            Payment::query()->updateOrCreate(
                ['transaction_id' => $transaction->id],
                [
                    'method' => $method,
                    'amount_paid' => $amountPaid,
                    'change_amount' => $amountPaid - $transaction->grand_total,
                    'paid_at' => now(),
                    'status' => 'paid',
                ],
            );

            return $transaction->refresh();
        });
    }

    public function void(Transaction $transaction): Transaction
    {
        return DB::transaction(function () use ($transaction): Transaction {
            $transaction = Transaction::query()
                ->with(['stockMovements.ingredient', 'payment'])
                ->lockForUpdate()
                ->findOrFail($transaction->id);

            if ($transaction->status === 'void') {
                throw new RuntimeException('Transaksi sudah void.');
            }

            if ($transaction->status === 'paid') {
                foreach ($transaction->stockMovements()->where('type', 'sale')->get() as $movement) {
                    $ingredient = Ingredient::query()
                        ->lockForUpdate()
                        ->findOrFail($movement->ingredient_id);

                    $restoreQuantity = abs($movement->quantity);
                    $stockBefore = $ingredient->current_stock;
                    $stockAfter = $stockBefore + $restoreQuantity;

                    $ingredient->update(['current_stock' => $stockAfter]);

                    $transaction->stockMovements()->create([
                        'ingredient_id' => $ingredient->id,
                        'user_id' => auth()->id(),
                        'type' => 'void',
                        'quantity' => $restoreQuantity,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'note' => "Pengembalian stock void {$transaction->invoice_number}.",
                    ]);
                }
            }

            $transaction->update(['status' => 'void']);
            $transaction->payment?->update(['status' => 'failed']);

            return $transaction->refresh();
        });
    }
}
