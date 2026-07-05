<x-filament-panels::page>
    <style>
        .statement-grid { display: grid; gap: 16px; }
        .statement-card { background: white; border: 1px solid rgb(229 231 235); border-radius: 16px; padding: 16px; box-shadow: 0 8px 22px rgb(15 23 42 / 6%); }
        .dark .statement-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .statement-filters { display: grid; grid-template-columns: 1.2fr repeat(2, minmax(0, 1fr)) auto; gap: 12px; align-items: end; }
        .statement-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: rgb(55 65 81); }
        .statement-input { width: 100%; border-radius: 12px; border: 1px solid rgb(209 213 219); padding: 9px 11px; font-size: 14px; }
        .statement-actions { display: flex; gap: 8px; align-items: center; }
        .statement-btn { display: inline-flex; justify-content: center; border-radius: 10px; padding: 9px 12px; font-size: 13px; font-weight: 800; text-decoration: none; white-space: nowrap; }
        .statement-pdf { background: #dc2626; color: white; }
        .statement-excel { background: #16a34a; color: white; }
        .statement-header { display: flex; justify-content: space-between; gap: 16px; }
        .statement-title { font-size: 22px; font-weight: 900; margin: 0 0 4px; }
        .statement-muted { color: rgb(107 114 128); font-size: 13px; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .summary-item { border: 1px solid rgb(229 231 235); border-radius: 14px; padding: 12px; }
        .summary-label { color: rgb(107 114 128); font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .summary-value { margin-top: 4px; font-size: 20px; font-weight: 900; }
        .statement-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .statement-table th, .statement-table td { padding: 10px 12px; border-bottom: 1px solid rgb(229 231 235); text-align: left; vertical-align: top; }
        .statement-table th { font-size: 12px; text-transform: uppercase; color: rgb(107 114 128); }
        .item-detail { margin-top: 6px; color: rgb(107 114 128); font-size: 12px; line-height: 1.6; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: 800; }
        @media (max-width: 1000px) { .statement-filters, .summary-grid { grid-template-columns: 1fr; } .statement-header { flex-direction: column; } .statement-actions { flex-wrap: wrap; } }
    </style>

    <div class="statement-grid">
        <div class="statement-card statement-filters">
            <div>
                <label class="statement-label">Customer</label>
                <select wire:model.live="customerId" class="statement-input">
                    @forelse ($this->customers as $customerOption)
                        <option value="{{ $customerOption->id }}" @selected((string) $customerId === (string) $customerOption->id)>{{ $customerOption->name }}</option>
                    @empty
                        <option value="">Belum ada customer</option>
                    @endforelse
                </select>
            </div>
            <div>
                <label class="statement-label">Tanggal Mulai</label>
                <input type="date" wire:model.live="startDate" value="{{ $startDate }}" class="statement-input">
            </div>
            <div>
                <label class="statement-label">Tanggal Selesai</label>
                <input type="date" wire:model.live="endDate" value="{{ $endDate }}" class="statement-input">
            </div>
            <div class="statement-actions">
                <a class="statement-btn statement-pdf" href="{{ $this->exportUrl('pdf') }}">Export PDF</a>
                <a class="statement-btn statement-excel" href="{{ $this->exportUrl('excel') }}">Export Excel</a>
            </div>
        </div>

        <div class="statement-card statement-header">
            <div>
                <h2 class="statement-title">{{ $this->setting?->store_name ?? 'KopiKita' }}</h2>
                <div class="statement-muted">{{ $this->setting?->address }}</div>
                <div class="statement-muted">{{ $this->setting?->phone_number }}</div>
            </div>
            <div class="text-right">
                <div class="statement-title">Laporan Pihak dari {{ $this->customer?->name ?? '-' }}</div>
                <div class="statement-muted">Dari: {{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }} Kepada: {{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</div>
                <div class="statement-muted">Total Transaksi: {{ number_format($this->summary['transaction_count'], 0, ',', '.') }}</div>
            </div>
        </div>

        <div class="statement-card">
            <div class="summary-grid">
                <div class="summary-item"><div class="summary-label">Total Penjualan</div><div class="summary-value">{{ $this->rupiah($this->summary['total_sales']) }}</div></div>
                <div class="summary-item"><div class="summary-label">Total Pembelian</div><div class="summary-value">{{ $this->rupiah($this->summary['total_purchase']) }}</div></div>
                <div class="summary-item"><div class="summary-label">Total Piutang</div><div class="summary-value">{{ $this->rupiah($this->summary['receivable']) }}</div></div>
                <div class="summary-item"><div class="summary-label">Uang Masuk</div><div class="summary-value">{{ $this->rupiah($this->summary['cash_in']) }}</div></div>
                <div class="summary-item"><div class="summary-label">Uang Keluar</div><div class="summary-value">{{ $this->rupiah($this->summary['cash_out']) }}</div></div>
                <div class="summary-item"><div class="summary-label">Jumlah Transaksi</div><div class="summary-value">{{ number_format($this->summary['transaction_count'], 0, ',', '.') }}</div></div>
            </div>
        </div>

        <div class="statement-card">
            <h3 style="font-weight: 800; margin-bottom: 12px;">Detail Statement Customer</h3>
            <div style="overflow-x: auto;">
                <table class="statement-table">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Uraian</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Diterima / Dibayar</th>
                            <th>Jatuh Tempo</th>
                            <th class="text-right">Saldo Berjalan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->rows as $row)
                            <tr>
                                <td>{{ $row['date'] }}</td>
                                <td>
                                    <div class="font-bold">{{ $row['description'] }}</div>
                                    <div class="item-detail">
                                        @foreach ($row['items'] as $item)
                                            <div>{{ $item['name'] }} - Qty {{ number_format($item['qty'], 0, ',', '.') }} x {{ $this->rupiah($item['price']) }} = {{ $this->rupiah($item['subtotal']) }}</div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="text-right">{{ $this->rupiah($row['amount']) }}</td>
                                <td class="text-right">{{ $this->rupiah($row['paid']) }}</td>
                                <td>{{ $row['due'] }}</td>
                                <td class="text-right font-bold">{{ $this->rupiah($row['running_balance']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Tidak ada transaksi untuk customer dan periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
