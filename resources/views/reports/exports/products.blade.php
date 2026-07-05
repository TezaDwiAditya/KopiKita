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
        <tr><td>Qty Terjual</td><td class="right">{{ $summary['qty'] }}</td><td>Total Penjualan</td><td class="right">Rp {{ number_format($summary['sales'], 0, ',', '.') }}</td></tr>
        <tr><td>Total Modal</td><td class="right">Rp {{ number_format($summary['cost'], 0, ',', '.') }}</td><td>Laba Kotor</td><td class="right">Rp {{ number_format($summary['gross_profit'], 0, ',', '.') }}</td></tr>
    </table>

    <table>
        <thead><tr><th>Menu</th><th>Varian</th><th class="right">Qty</th><th class="right">Penjualan</th><th class="right">Modal</th><th class="right">Laba Kotor</th></tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr><td>{{ $row['menu'] }}</td><td>{{ $row['variant'] }}</td><td class="right">{{ $row['qty'] }}</td><td class="right">{{ $row['sales'] }}</td><td class="right">{{ $row['cost'] }}</td><td class="right">{{ $row['gross_profit'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
