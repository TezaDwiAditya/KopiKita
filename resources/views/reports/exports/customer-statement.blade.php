<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: DejaVu Sans, Arial, sans-serif; color: #111827; font-size: 11px; margin: 0; }
        .header { display: table; width: 100%; margin-bottom: 18px; }
        .header-left, .header-right { display: table-cell; vertical-align: top; width: 50%; }
        .header-right { text-align: right; }
        .store-name { font-size: 20px; font-weight: 800; margin-bottom: 4px; }
        .title { font-size: 17px; font-weight: 800; margin-bottom: 5px; }
        .muted { color: #4b5563; line-height: 1.45; }
        .summary { width: 100%; border-collapse: collapse; margin: 10px 0 16px; }
        .summary td { border: 1px solid #d1d5db; padding: 7px 8px; }
        .summary .label { background: #f3f4f6; font-weight: 700; width: 28%; }
        .summary .value { text-align: right; font-weight: 800; }
        table.statement { width: 100%; border-collapse: collapse; }
        .statement th, .statement td { border: 1px solid #d1d5db; padding: 7px 8px; vertical-align: top; }
        .statement th { background: #f3f4f6; text-transform: uppercase; font-size: 10px; letter-spacing: .02em; }
        .right { text-align: right; }
        .bold { font-weight: 800; }
        .status { font-weight: 800; color: #166534; }
        .status.unpaid { color: #991b1b; }
        .items { margin-top: 4px; color: #4b5563; font-size: 10px; line-height: 1.5; }
        .footer { margin-top: 18px; color: #4b5563; font-size: 10px; }
    </style>
</head>
<body>
@php
    $money = fn (int|float $amount): string => 'Rp '.number_format((int) $amount, 0, ',', '.');
@endphp

<div class="header">
    <div class="header-left">
        <div class="store-name">{{ $setting?->store_name ?? 'KopiKita' }}</div>
        <div class="muted">{{ $setting?->address }}</div>
        <div class="muted">{{ $setting?->phone_number }}</div>
    </div>
    <div class="header-right">
        <div class="title">Laporan Pihak dari {{ $customer?->name ?? '-' }}</div>
        <div class="muted">Dari: {{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }} Kepada: {{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</div>
        <div class="muted">Total Transaksi : {{ number_format($summary['transaction_count'], 0, ',', '.') }}</div>
    </div>
</div>

<table class="summary">
    <tr>
        <td class="label">Total Tagihan</td>
        <td class="value">{{ $money($summary['total_sales']) }}</td>
        <td class="label">Sudah Dibayar</td>
        <td class="value">{{ $money($summary['total_paid']) }}</td>
    </tr>
    <tr>
        <td class="label">Uang Masuk</td>
        <td class="value">{{ $money($summary['cash_in']) }}</td>
        <td class="label">Jumlah Transaksi</td>
        <td class="value">{{ number_format($summary['transaction_count'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td class="label" colspan="3">Belum Dibayar / Hutang</td>
        <td class="value">{{ $money($summary['total_unpaid']) }}</td>
    </tr>
</table>

<table class="statement">
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Uraian</th>
            <th>Status</th>
            <th class="right">Tagihan</th>
            <th class="right">Sudah Dibayar</th>
            <th class="right">Belum Dibayar</th>
            <th>Jatuh Tempo</th>
            <th class="right">Saldo Berjalan</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['date'] }}</td>
                <td>
                    <div class="bold">{{ $row['description'] }}</div>
                    <div class="items">
                        @foreach ($row['items'] as $item)
                            <div>{{ $item['name'] }} - Qty {{ number_format($item['qty'], 0, ',', '.') }} x {{ $money($item['price']) }} = {{ $money($item['subtotal']) }}</div>
                        @endforeach
                    </div>
                </td>
                <td><span class="status @if($row['unpaid'] > 0) unpaid @endif">{{ $row['status'] }}</span></td>
                <td class="right">{{ $money($row['amount']) }}</td>
                <td class="right">{{ $money($row['paid']) }}</td>
                <td class="right bold">{{ $money($row['unpaid']) }}</td>
                <td>{{ $row['due'] }}</td>
                <td class="right bold">{{ $money($row['running_balance']) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">Tidak ada transaksi untuk customer dan periode ini.</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="footer">
    Laporan Dibuat pada: {{ $generatedAt->format('d M Y H:i') }}
</div>
</body>
</html>
