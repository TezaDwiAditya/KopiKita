<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;

class ReceiptController extends Controller
{
    public function __invoke(Transaction $transaction): View
    {
        $transaction->load(['cashier', 'customer', 'items', 'payment']);

        return view('receipts.thermal-58', [
            'setting' => Setting::query()->first(),
            'transaction' => $transaction,
        ]);
    }
}
