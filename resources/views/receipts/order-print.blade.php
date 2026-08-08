<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesanan {{ $transaction->invoice_number }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 8mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            background: #f3f4f6;
            color: #000;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 13px;
        }

        .sheet {
            width: 100%;
            max-width: 297mm;
            min-height: 190mm;
            margin: 0 auto;
            background: #fff;
            padding: 4mm;
        }

        .meta {
            width: 150mm;
            margin-bottom: 12mm;
            border-collapse: collapse;
        }

        .meta td {
            height: 20px;
            padding: 1px 3px;
            vertical-align: top;
        }

        .meta td:first-child {
            width: 34mm;
        }

        .items {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .items th,
        .items td {
            border: 1px solid #000;
            padding: 2px 4px;
            height: 20px;
            overflow-wrap: anywhere;
        }

        .items thead th {
            background: #d9d9d9;
            font-size: 16px;
            font-weight: 700;
            text-align: center;
            vertical-align: bottom;
            height: 50px;
        }

        .col-no {
            width: 25mm;
            text-align: center;
        }

        .col-uraian {
            width: 76mm;
        }

        .col-category {
            width: 21mm;
        }

        .col-ukuran {
            width: 31mm;
        }

        .col-catatan {
            width: 23mm;
        }

        .col-qty {
            width: 24mm;
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .right {
            text-align: right;
        }

        .total-label {
            font-weight: 700;
            text-align: center;
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
    @endphp

    <main class="sheet">
        <table class="meta">
            <tr>
                <td>No Pesanan</td>
                <td>{{ $transaction->invoice_number }}</td>
            </tr>
            <tr>
                <td>Customer</td>
                <td>{{ $transaction->customer?->name ?? 'Walk-in' }}</td>
            </tr>
            <tr>
                <td>Tanggal Pesan</td>
                <td>{{ $transaction->transaction_date?->locale('id')->translatedFormat('j F Y') }}</td>
            </tr>
        </table>

        <table class="items">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-uraian">Uraian</th>
                    <th class="col-category">Category</th>
                    <th class="col-ukuran">Ukuran</th>
                    <th class="col-catatan">Catatan</th>
                    <th class="col-qty">Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($transaction->items as $item)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $item->menu_name }}</td>
                        <td>{{ $item->menu?->category?->name ?? '-' }}</td>
                        <td>{{ $item->variant_name ?: '-' }}</td>
                        <td>{{ $item->note }}</td>
                        <td class="right">{{ $item->quantity }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4"></td>
                    <td class="total-label">Total</td>
                    <td class="right">{{ $totalQty }}</td>
                </tr>
            </tbody>
        </table>
    </main>

    <button class="print-button" onclick="window.print()">Print Pesanan</button>
</body>
</html>
