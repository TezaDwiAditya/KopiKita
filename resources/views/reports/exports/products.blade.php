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
        .section-title { margin-top: 18px; font-size: 14px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="period">Periode: {{ $startDate }} s/d {{ $endDate }}</div>

    <table class="summary">
        <tr><td>Qty Terjual</td><td class="right">{{ $summary['qty'] }}</td><td>Total Penjualan</td><td class="right">Rp {{ number_format($summary['sales'], 0, ',', '.') }}</td></tr>
        <tr><td>Total Modal</td><td class="right">Rp {{ number_format($summary['cost'], 0, ',', '.') }}</td><td>Laba Kotor</td><td class="right">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</td></tr>
    </table>

    <div class="section-title">Produk Terjual</div>
    <table>
        <thead><tr><th>Menu</th><th>Varian</th><th class="right">Qty</th><th class="right">Penjualan</th><th class="right">Modal</th><th class="right">Laba Kotor</th></tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr><td>{{ $row['menu'] }}</td><td>{{ $row['variant'] }}</td><td class="right">{{ $row['qty'] }}</td><td class="right">Rp {{ number_format($row['sales'], 0, ',', '.') }}</td><td class="right">Rp {{ number_format($row['cost'], 0, ',', '.') }}</td><td class="right">Rp {{ number_format($row['gross_profit'], 0, ',', '.') }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="section-title">Evaluasi Harga Semua Produk</div>
    <table class="summary">
        <tr><td>Total Produk/Varian</td><td class="right">{{ $priceSummary['products'] }}</td><td>Produk Aktif</td><td class="right">{{ $priceSummary['active_products'] }}</td></tr>
        <tr><td>Rata-rata Margin</td><td class="right">{{ number_format($priceSummary['average_margin'], 1, ',', '.') }}%</td><td>Perlu Ditinjau</td><td class="right">{{ $priceSummary['low_margin'] + $priceSummary['no_profit'] }}</td></tr>
    </table>

    <table>
        <thead><tr><th>Kategori</th><th>Menu</th><th>Varian</th><th class="right">Harga Jual</th><th class="right">Harga Modal</th><th class="right">Keuntungan</th><th class="right">Margin</th><th class="right">Markup</th><th>Status</th></tr></thead>
        <tbody>
            @foreach ($priceRows as $row)
                <tr>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['menu'] }}</td>
                    <td>{{ $row['variant'] }}</td>
                    <td class="right">Rp {{ number_format($row['selling_price'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                    <td class="right">{{ number_format($row['margin_percent'], 1, ',', '.') }}%</td>
                    <td class="right">{{ number_format($row['markup_percent'], 1, ',', '.') }}%</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
