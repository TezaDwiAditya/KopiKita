<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk {{ $transaction->invoice_number }}</title>
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        html,
        body {
            width: 58mm;
            margin: 0;
            padding: 0;
            background: #fff;
            color: #000;
            font-family: "Courier New", monospace;
            font-size: 10px;
            line-height: 1.2;
        }

        .receipt {
            width: 58mm;
            max-width: 58mm;
            padding: 2mm 3mm 4mm;
        }

        .center {
            text-align: center;
        }

        .store-name {
            font-size: 12px;
            font-weight: 700;
            line-height: 1.1;
        }

        .muted {
            font-size: 9px;
            line-height: 1.15;
        }

        .line {
            border-top: 1px dashed #000;
            margin: 4px 0;
        }

        .row {
            display: flex;
            justify-content: space-between;
            gap: 4px;
            width: 100%;
        }

        .row span:first-child,
        .row strong:first-child {
            min-width: 0;
            overflow-wrap: anywhere;
        }

        .row span:last-child,
        .row strong:last-child {
            flex-shrink: 0;
            text-align: right;
            white-space: nowrap;
        }

        .meta-label {
            display: inline-block;
            width: 46px;
        }

        .item {
            margin-bottom: 3px;
        }

        .item-name {
            font-weight: 700;
            overflow-wrap: anywhere;
        }

        .total-row {
            font-weight: 700;
        }

        .footer {
            margin-top: 5px;
            text-align: center;
        }

        .no-print {
            margin: 8px 0 0 3mm;
            border: 0;
            border-radius: 6px;
            background: #111827;
            color: #fff;
            padding: 8px 10px;
            cursor: pointer;
            font-family: Arial, sans-serif;
            font-size: 12px;
        }

        @media screen {
            body {
                background: #e5e7eb;
                padding: 12px;
            }

            .receipt {
                background: #fff;
                box-shadow: 0 8px 24px rgb(0 0 0 / 18%);
            }
        }

        @media print {
            html,
            body {
                width: 58mm;
                height: auto;
                overflow: visible;
                background: #fff;
            }

            body {
                padding: 0;
            }

            .receipt {
                padding: 1.5mm 2.5mm 3mm;
                box-shadow: none;
            }

            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="receipt">
        <div class="center">
            <div class="store-name">{{ $setting?->store_name ?? 'Coffee Kita' }}</div>
            @if ($setting?->address)
                <div class="muted">{{ $setting->address }}</div>
            @endif
            @if ($setting?->phone_number)
                <div class="muted">{{ $setting->phone_number }}</div>
            @endif
        </div>

        <div class="line"></div>

        <div><span class="meta-label">No</span>: {{ $transaction->invoice_number }}</div>
        <div><span class="meta-label">Tanggal</span>: {{ $transaction->transaction_date?->format('d/m/Y H:i') }}</div>
        <div><span class="meta-label">Kasir</span>: {{ $transaction->cashier?->name }}</div>
        <div><span class="meta-label">Customer</span>: {{ $transaction->customer?->name ?? 'Walk-in' }}</div>

        <div class="line"></div>

        @foreach ($transaction->items as $item)
            <div class="item">
                <div class="item-name">{{ $item->menu_name }}@if ($item->variant_name) - {{ $item->variant_name }}@endif</div>
                <div class="row">
                    <span>{{ $item->quantity }} x {{ number_format($item->price, 0, ',', '.') }}</span>
                    <span>{{ number_format($item->subtotal, 0, ',', '.') }}</span>
                </div>
                @if ($item->note)
                    <div class="muted">* {{ $item->note }}</div>
                @endif
            </div>
        @endforeach

        <div class="line"></div>

        <div class="row"><span>Subtotal</span><span>{{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
        <div class="row"><span>Diskon</span><span>{{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
        <div class="row"><span>Pajak</span><span>{{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
        <div class="row total-row"><strong>Total</strong><strong>{{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></div>
        <div class="row"><span>Bayar</span><span>{{ number_format($transaction->payment?->amount_paid ?? 0, 0, ',', '.') }}</span></div>
        <div class="row"><span>Kembali</span><span>{{ number_format($transaction->payment?->change_amount ?? 0, 0, ',', '.') }}</span></div>

        <div class="line"></div>

        <div class="footer">
            {{ $setting?->receipt_footer ?? 'Terima kasih' }}
        </div>
    </div>

    <button class="no-print" onclick="window.print()">Print Struk</button>
</body>
</html>
