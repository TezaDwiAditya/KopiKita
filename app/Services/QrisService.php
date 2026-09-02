<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;

class QrisService
{
    private const DEFAULT_QRIS_IMAGE = 'QRIS_KopitKita.jpeg';

    public function imagePath(Transaction $transaction): ?string
    {
        if (Storage::disk('local')->exists(self::DEFAULT_QRIS_IMAGE)) {
            return self::DEFAULT_QRIS_IMAGE;
        }

        return null;
    }

    public function exists(Transaction $transaction): bool
    {
        return $this->imagePath($transaction) !== null;
    }

    public function amount(Transaction $transaction): int
    {
        $transaction->loadMissing('payment');

        return (int) ($transaction->payment?->qris_amount ?? $transaction->grand_total ?? 0);
    }

    public function reference(Transaction $transaction): ?string
    {
        $transaction->loadMissing('payment');

        return $transaction->payment?->qris_reference;
    }

    public function status(Transaction $transaction): string
    {
        $transaction->loadMissing('payment');

        return $transaction->payment?->qris_status ?? 'pending';
    }
}
