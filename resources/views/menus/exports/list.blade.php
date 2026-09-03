<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; }
        h1 { font-size: 20px; margin: 0 0 4px; }
        .meta { margin-bottom: 14px; color: #555; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 7px; }
        th { background: #f3f4f6; text-align: left; font-weight: bold; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h1>List Menu dan Harga Varian</h1>
    <div class="meta">Diexport: {{ $generatedAt->format('d/m/Y H:i') }}</div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Kategori</th>
                <th>Menu</th>
                <th>Varian</th>
                <th class="right">Harga Jual</th>
                <th class="right">Harga Modal</th>
                <th class="right">Keuntungan</th>
                <th>Status Menu</th>
                <th>Status Varian</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['category'] }}</td>
                    <td>{{ $row['menu'] }}</td>
                    <td>{{ $row['variant'] }}</td>
                    <td class="right">Rp {{ number_format($row['selling_price'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['cost_price'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                    <td>{{ $row['menu_status'] }}</td>
                    <td>{{ $row['variant_status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Belum ada data menu.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
