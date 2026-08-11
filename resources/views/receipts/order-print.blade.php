<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan {{ $transaction->invoice_number }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #eef0f3;
            color: #111827;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
        }

        .sheet {
            width: 100%;
            max-width: 210mm;
            min-height: 277mm;
            margin: 0 auto;
            background: #fff;
            padding: 10mm;
            border: 1px solid #e5e7eb;
        }

        .header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16mm;
            padding-bottom: 7mm;
            border-bottom: 2px solid #111827;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 5mm;
            min-width: 0;
        }

        .logo {
            width: 24mm;
            height: 24mm;
            border: 1px solid #d1d5db;
            border-radius: 4mm;
            object-fit: contain;
            padding: 2mm;
        }

        .logo-placeholder {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #111827;
            color: #fff;
            font-size: 18px;
            font-weight: 800;
        }

        .store-name {
            margin: 0;
            color: #111827;
            font-size: 22px;
            font-weight: 800;
            line-height: 1.05;
            text-transform: uppercase;
        }

        .store-detail {
            margin-top: 2mm;
            color: #4b5563;
            line-height: 1.4;
            max-width: 130mm;
        }

        .document-title {
            min-width: 46mm;
            text-align: right;
        }

        .document-title h1 {
            margin: 0;
            font-size: 18px;
            line-height: 1;
            letter-spacing: 0;
            text-transform: uppercase;
        }

        .document-title div {
            margin-top: 3mm;
            color: #6b7280;
            font-size: 11px;
            font-weight: 700;
        }

        .meta-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 6mm;
            margin: 7mm 0 8mm;
        }

        .meta-box {
            border: 1px solid #d1d5db;
            border-radius: 3mm;
            overflow: hidden;
        }

        .meta-title {
            background: #f3f4f6;
            border-bottom: 1px solid #d1d5db;
            padding: 2.5mm 4mm;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
        }

        .meta {
            width: 100%;
            border-collapse: collapse;
        }

        .meta td {
            padding: 2.2mm 4mm;
            border-bottom: 1px solid #eef0f3;
            vertical-align: top;
        }

        .meta tr:last-child td {
            border-bottom: 0;
        }

        .meta td:first-child {
            width: 34mm;
            color: #6b7280;
            font-weight: 700;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            border: 1px solid #111827;
        }

        .items th,
        .items td {
            border: 1px solid #d1d5db;
            padding: 3mm 3.5mm;
            min-height: 9mm;
            overflow-wrap: anywhere;
            vertical-align: top;
        }

        .items thead th {
            background: #111827;
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            text-align: center;
            text-transform: uppercase;
        }

        .col-no {
            width: 11mm;
            text-align: center;
        }

        .col-uraian {
            width: 38mm;
        }

        .col-category {
            width: 22mm;
        }

        .col-ukuran {
            width: 22mm;
        }

        .col-harga {
            width: 24mm;
            text-align: right;
        }

        .col-catatan {
            width: auto;
        }

        .col-qty {
            width: 14mm;
            text-align: center;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .total-label {
            font-weight: 700;
            text-align: right;
            text-transform: uppercase;
        }

        .item-name {
            font-weight: 800;
        }

        .item-note {
            color: #4b5563;
        }

        .total-row td {
            background: #f9fafb;
            font-weight: 800;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1fr 68mm;
            gap: 8mm;
            margin-top: 10mm;
            align-items: end;
        }

        .instruction-box {
            min-height: 28mm;
            border: 1px solid #d1d5db;
            border-radius: 3mm;
            padding: 4mm;
        }

        .instruction-title {
            margin-bottom: 2mm;
            font-weight: 800;
            text-transform: uppercase;
        }

        .signature {
            margin-top: 8mm;
            text-align: center;
        }

        .signature-line {
            height: 18mm;
            border-bottom: 1px solid #111827;
            margin-bottom: 2mm;
        }

        .signature-label {
            color: #6b7280;
            font-size: 11px;
        }

        .summary {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #d1d5db;
            border-radius: 3mm;
            overflow: hidden;
        }

        .summary td {
            padding: 3mm 4mm;
            border-bottom: 1px solid #e5e7eb;
            vertical-align: middle;
        }

        .summary tr:last-child td {
            border-bottom: 0;
        }

        .summary td:first-child {
            color: #6b7280;
            font-weight: 800;
        }

        .summary td:last-child {
            width: 36mm;
            text-align: right;
            white-space: nowrap;
            font-variant-numeric: tabular-nums;
        }

        .summary-total td {
            background: #f9fafb;
            color: #111827;
            font-weight: 900;
        }

        .print-button {
            position: fixed;
            right: 18px;
            bottom: 18px;
            border: 0;
            border-radius: 6px;
            background: #111827;
            color: #fff;
            padding: 10px 14px;
            cursor: pointer;
            font: 600 13px Arial, Helvetica, sans-serif;
            box-shadow: 0 10px 24px rgb(0 0 0 / 18%);
        }

        @media screen {
            .sheet {
                box-shadow: 0 12px 36px rgb(0 0 0 / 16%);
            }
        }

        @media print {
            body {
                background: #fff;
            }

            .sheet {
                max-width: none;
                min-height: 0;
                padding: 0;
                border: 0;
                box-shadow: none;
            }

            .print-button {
                display: none !important;
            }
        }
    </style>
</head>
<body>
    @php
        $totalQty = $transaction->items->sum('quantity');
        $storeName = $setting?->store_name ?? 'Coffee Kita';
        $logoUrl = $setting?->logo_path ? Storage::url($setting->logo_path) : null;
    @endphp

    <main class="sheet">
        <header class="header">
            <div class="brand">
                @if ($logoUrl)
                    <img class="logo" src="{{ $logoUrl }}" alt="{{ $storeName }}">
                @else
                    <div class="logo logo-placeholder">{{ Str::of($storeName)->substr(0, 1)->upper() }}</div>
                @endif

                <div>
                    <h2 class="store-name">{{ $storeName }}</h2>
                    <div class="store-detail">
                        @if ($setting?->address)
                            <div>{{ $setting->address }}</div>
                        @endif
                        @if ($setting?->phone_number)
                            <div>Telp. {{ $setting->phone_number }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="document-title">
                <h1>Order Sheet</h1>
                <div>{{ $transaction->invoice_number }}</div>
            </div>
        </header>

        <section class="meta-grid">
            <div class="meta-box">
                <div class="meta-title">Informasi Pesanan</div>
                <table class="meta">
                    <tr>
                        <td>No Pesanan</td>
                        <td>{{ $transaction->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>{{ $transaction->transaction_date?->locale('id')->translatedFormat('j F Y, H:i') }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>{{ strtoupper($transaction->status) }}</td>
                    </tr>
                </table>
            </div>

            <div class="meta-box">
                <div class="meta-title">Customer</div>
                <table class="meta">
                    <tr>
                        <td>Nama</td>
                        <td>{{ $transaction->customer?->name ?? 'Walk-in' }}</td>
                    </tr>
                    <tr>
                        <td>Kasir</td>
                        <td>{{ $transaction->cashier?->name ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td>Catatan</td>
                        <td>{{ $transaction->note ?: '-' }}</td>
                    </tr>
                </table>
            </div>
        </section>

        <table class="items">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-uraian">Uraian</th>
                    <th class="col-category">Category</th>
                    <th class="col-ukuran">Ukuran</th>
                    <th class="col-harga">Harga Satuan</th>
                    <th class="col-catatan">Catatan</th>
                    <th class="col-qty">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td class="item-name">{{ $item->menu_name }}</td>
                        <td>{{ $item->menu?->category?->name ?? '-' }}</td>
                        <td>{{ $item->variant_name ?: '-' }}</td>
                        <td class="right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="item-note">{{ $item->note ?: '-' }}</td>
                        <td class="center">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5" class="total-label">Total Item</td>
                    <td class="total-label">Total</td>
                    <td class="center">{{ $totalQty }}</td>
                </tr>
            </tbody>
        </table>

        <section class="footer-grid">
            <div class="instruction-box">
                <div class="instruction-title">Instruksi Produksi</div>
                <div>Periksa kembali ukuran, catatan, dan jumlah pesanan sebelum disiapkan.</div>
            </div>

            <div>
                <table class="summary">
                    <tr>
                        <td>Subtotal</td>
                        <td class="right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td>Diskon</td>
                        <td class="right">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="summary-total">
                        <td>Total</td>
                        <td class="right"><strong>Rp {{ number_format($transaction->grand_total, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>

                <div class="signature">
                    <div class="signature-line"></div>
                    <div>Petugas</div>
                    <div class="signature-label">Nama & tanda tangan</div>
                </div>
            </div>
        </section>
    </main>

    <button class="print-button" onclick="window.print()">Print Pesanan</button>
</body>
</html>
