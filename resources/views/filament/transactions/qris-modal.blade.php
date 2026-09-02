@php
    use Illuminate\Support\Js;

    $qris = app(\App\Services\QrisService::class);
    $qrisUrl = $qris->exists($transaction) ? route('admin.transactions.qris', $transaction) : null;
@endphp

<div class="space-y-4">
    @if ($qrisUrl)
        <div class="flex justify-center rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
            <img src="{{ $qrisUrl }}" alt="QRIS {{ $transaction->invoice_number }}" class="max-h-96 w-auto rounded-md">
        </div>

        <div class="space-y-1 text-sm text-gray-600 dark:text-gray-300">
            <div>Invoice: {{ $transaction->invoice_number }}</div>
            @if ($qris->reference($transaction))
                <div>Referensi QRIS: {{ $qris->reference($transaction) }}</div>
            @endif
            <div>Nominal QRIS: Rp{{ number_format($qris->amount($transaction), 0, ',', '.') }}</div>
            <div>Status QRIS: {{ strtoupper($qris->status($transaction)) }}</div>
        </div>

        <div class="flex flex-wrap gap-2">
            <button
                type="button"
                class="fi-btn fi-color-primary fi-size-md"
                x-data
                x-on:click="
                    if (navigator.share) {
                        navigator.share({
                            title: 'QRIS {{ $transaction->invoice_number }}',
                            text: 'QRIS pembayaran {{ $transaction->invoice_number }}',
                            url: {{ Js::from($qrisUrl) }},
                        })
                    } else {
                        window.open({{ Js::from($qrisUrl) }}, '_blank', 'noopener,noreferrer')
                    }
                "
            >
                Share QRIS
            </button>

            <a class="fi-btn fi-color-gray fi-size-md" href="{{ route('admin.transactions.qris.download', $transaction) }}" target="_blank" rel="noopener noreferrer">
                Download QRIS
            </a>
        </div>
    @else
        <div class="rounded-lg border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300">
            QRIS untuk transaksi ini belum tersedia.
        </div>
    @endif
</div>
