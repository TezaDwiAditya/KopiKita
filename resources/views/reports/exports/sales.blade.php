<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .period { margin-bottom: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ccc; padding: 6px; }
        th { background: #f3f4f6; text-align: left; }
        .right { text-align: right; }
        .summary td { font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="period">Periode: {{ $startDate }} s/d {{ $endDate }}</div>

    <table class="summary">
        <tr><td>Total Penjualan</td><td class="right">Rp {{ number_format($summary['total_sales'], 0, ',', '.') }}</td><td>Jumlah Transaksi</td><td class="right">{{ number_format($summary['transaction_count'], 0, ',', '.') }}</td></tr>
        <tr><td>Subtotal</td><td class="right">Rp {{ number_format($summary['total_subtotal'], 0, ',', '.') }}</td><td>Diskon</td><td class="right">Rp {{ number_format($summary['total_discount'], 0, ',', '.') }}</td></tr>
        <tr><td>Pajak</td><td class="right">Rp {{ number_format($summary['total_tax'], 0, ',', '.') }}</td><td>Laba Kotor</td><td class="right">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</td></tr>
    </table>

    <h3>Ringkasan Harian</h3>
    <table>
        <thead><tr><th>Tanggal</th><th class="right">Transaksi</th><th class="right">Subtotal</th><th class="right">Diskon</th><th class="right">Pajak</th><th class="right">Grand Total</th></tr></thead>
        <tbody>
            @foreach ($dailyRows as $row)
                <tr><td>{{ $row['date'] }}</td><td class="right">{{ $row['count'] }}</td><td class="right">{{ $row['subtotal'] }}</td><td class="right">{{ $row['discount'] }}</td><td class="right">{{ $row['tax'] }}</td><td class="right">{{ $row['grand_total'] }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <h3>Detail Transaksi</h3>
    <table>
        <thead><tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Customer</th><th class="right">Total</th></tr></thead>
        <tbody>
            @foreach ($transactions as $transaction)
                <tr><td>{{ $transaction->invoice_number }}</td><td>{{ $transaction->transaction_date->format('d M Y H:i') }}</td><td>{{ $transaction->cashier?->name }}</td><td>{{ $transaction->customer?->name ?? 'Walk-in' }}</td><td class="right">{{ $transaction->grand_total }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
