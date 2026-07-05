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
        <tr><td>Jumlah Bahan Terpakai</td><td class="right">{{ $summary['ingredients'] }}</td><td>Nilai Penggunaan</td><td class="right">Rp {{ number_format($summary['value'], 0, ',', '.') }}</td></tr>
    </table>

    <table>
        <thead><tr><th>Bahan</th><th class="right">Terpakai</th><th class="right">Void/Return</th><th class="right">Netto</th><th class="right">Nilai</th></tr></thead>
        <tbody>
            @foreach ($rows as $row)
                <tr><td>{{ $row['ingredient'] }}</td><td class="right">{{ $row['used'] }} {{ $row['unit'] }}</td><td class="right">{{ $row['restored'] }} {{ $row['unit'] }}</td><td class="right">{{ $row['net_used'] }} {{ $row['unit'] }}</td><td class="right">{{ $row['value'] }}</td></tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
