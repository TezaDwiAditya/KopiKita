<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Services\QrisService;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class QrisController extends Controller
{
    public function show(Transaction $transaction, QrisService $qris): Response|BinaryFileResponse
    {
        $path = $qris->imagePath($transaction);

        abort_if($path === null, 404);

        return response()->file(Storage::disk('local')->path($path));
    }

    public function download(Transaction $transaction, QrisService $qris): BinaryFileResponse
    {
        $path = $qris->imagePath($transaction);

        abort_if($path === null, 404);

        return response()->download(
            Storage::disk('local')->path($path),
            'qris-'.$transaction->invoice_number.'.'.pathinfo($path, PATHINFO_EXTENSION),
        );
    }
}
