@php
    $record = $getRecord();
    $transaction = $record instanceof \App\Models\Transaction ? $record : $record?->transaction;
    $qris = app(\App\Services\QrisService::class);
@endphp

@if ($transaction && $qris->exists($transaction))
    <img
        src="{{ route('admin.transactions.qris', $transaction) }}"
        alt="QRIS {{ $transaction->invoice_number }}"
        class="max-h-60 w-auto rounded-md border border-gray-200 bg-white p-2 dark:border-gray-700"
    >
@else
    <span class="text-sm text-gray-500 dark:text-gray-400">QRIS untuk transaksi ini belum tersedia.</span>
@endif
