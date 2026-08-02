<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 26px 24px; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 9px; color: #111; margin: 0; }
        .brand { width: 100%; padding-bottom: 18px; border-bottom: 1px solid #999; }
        .brand-left { font-size: 22px; line-height: 1; font-weight: 900; }
        .report-head { display: table; width: 100%; padding: 10px 0 18px; border-bottom: 1px solid #999; }
        .head-left, .head-right { display: table-cell; width: 50%; vertical-align: top; }
        .head-right { text-align: right; }
        .title { font-size: 14px; font-weight: 900; margin-bottom: 6px; }
        .period-row { font-size: 10px; line-height: 1.35; }
        .period-label { display: inline-block; width: 46px; }
        .summary-row { font-size: 10px; line-height: 1.35; font-weight: 800; }
        .summary-label { display: inline-block; min-width: 92px; text-align: left; }
        .summary-amount { display: inline-block; min-width: 74px; text-align: right; }
        .meta { display: table; width: 100%; margin: 24px 0 8px; font-size: 9px; }
        .meta-left, .meta-right { display: table-cell; width: 50%; }
        .meta-right { text-align: right; }
        table.report { width: 100%; border-collapse: collapse; }
        .report th, .report td { border: 1px solid #c9c5d1; padding: 5px 4px; vertical-align: top; }
        .report th { font-size: 8px; font-weight: 900; text-align: left; }
        .report td { font-size: 8px; }
        .right { text-align: right; }
        .item-line { margin-bottom: 3px; }
    </style>
</head>
<body>
@php
    $money = fn (int|float $amount): string => 'Rp '.number_format((int) $amount, 0, ',', '.');
@endphp

<div class="brand">
    <div class="brand-left">{{ $setting?->store_name ?? 'KopiKita' }}</div>
</div>

<div class="report-head">
    <div class="head-left">
        <div class="title">Laporan Penjualan</div>
        <div class="period-row"><span class="period-label">Dari:</span><strong>{{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }}</strong></div>
        <div class="period-row"><span class="period-label">Kepada:</span><strong>{{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</strong></div>
    </div>
    <div class="head-right">
        <div class="summary-row"><span class="summary-label">Diterima:</span><span class="summary-amount">{{ $money($summary['paid']) }}</span></div>
        <div class="summary-row"><span class="summary-label">Belum Dibayar:</span><span class="summary-amount">{{ $money($summary['unpaid']) }}</span></div>
        <div class="summary-row"><span class="summary-label">Total Penjualan:</span><span class="summary-amount">{{ $money($summary['sales']) }}</span></div>
    </div>
</div>

<div class="meta">
    <div class="meta-left">Total Transaksi : {{ number_format($summary['transaction_count'], 0, ',', '.') }}</div>
    <div class="meta-right">Laporan Dibuat pada: {{ $generatedAt->format('d M Y') }} &bull; {{ $generatedAt->format('H:i') }}</div>
</div>

<table class="report">
    <thead>
        <tr>
            <th>No. Faktur</th>
            <th>Nama Pihak</th>
            <th>Tanggal Faktur</th>
            <th>Produk</th>
            <th class="right">Jumlah</th>
            <th class="right">Harga Jual</th>
            <th class="right">Jumlah Total</th>
            <th class="right">Diterima</th>
            <th class="right">Saldo</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($rows as $row)
            <tr>
                <td>{{ $row['invoice'] }}</td>
                <td>{{ $row['customer_name'] }}</td>
                <td>{{ $row['date'] }}</td>
                <td>
                    @foreach ($row['items'] as $item)
                        <div class="item-line">{{ $item['name'] }}</div>
                    @endforeach
                </td>
                <td class="right">
                    @foreach ($row['items'] as $item)
                        <div class="item-line">{{ number_format($item['qty'], 0, ',', '.') }}</div>
                    @endforeach
                </td>
                <td class="right">
                    @foreach ($row['items'] as $item)
                        <div class="item-line">{{ $money($item['price']) }}</div>
                    @endforeach
                </td>
                <td class="right">{{ $money($row['amount']) }}</td>
                <td class="right">{{ $money($row['paid']) }}</td>
                <td class="right">{{ $money($row['balance']) }}</td>
            </tr>
        @empty
            <tr><td colspan="9">Tidak ada data penjualan customer untuk periode ini.</td></tr>
        @endforelse
    </tbody>
</table>
</body>
</html>
