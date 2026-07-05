<x-filament-panels::page>
    <style>
        .report-grid { display: grid; gap: 16px; }
        .report-filters { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; align-items: end; }
        .report-card { background: white; border: 1px solid rgb(229 231 235); border-radius: 16px; padding: 16px; box-shadow: 0 8px 22px rgb(15 23 42 / 6%); }
        .dark .report-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .report-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: rgb(55 65 81); }
        .dark .report-label { color: rgb(229 231 235); }
        .report-input { width: 100%; border-radius: 12px; border: 1px solid rgb(209 213 219); padding: 9px 11px; font-size: 14px; }
        .dark .report-input { background: rgb(31 41 55); border-color: rgb(75 85 99); color: white; }
        .summary-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .summary-title { color: rgb(107 114 128); font-size: 13px; font-weight: 700; }
        .summary-value { margin-top: 4px; font-size: 22px; font-weight: 900; color: rgb(17 24 39); }
        .dark .summary-value { color: white; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .report-table th, .report-table td { padding: 10px 12px; border-bottom: 1px solid rgb(229 231 235); text-align: left; }
        .dark .report-table th, .dark .report-table td { border-color: rgb(55 65 81); }
        .report-table th { font-size: 12px; text-transform: uppercase; color: rgb(107 114 128); }
        .text-right { text-align: right !important; }

        .export-actions { display: flex; gap: 8px; align-items: center; }
        .export-btn { display: inline-flex; justify-content: center; border-radius: 10px; padding: 9px 12px; font-size: 13px; font-weight: 800; text-decoration: none; }
        .export-pdf { background: #dc2626; color: white; }
        .export-excel { background: #16a34a; color: white; }
        @media (max-width: 900px) { .report-filters, .summary-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="report-grid">
        <div class="report-card report-filters">
            <div>
                <label class="report-label">Tanggal Mulai</label>
                <input type="date" wire:model.live="startDate" class="report-input">
            </div>
            <div>
                <label class="report-label">Tanggal Selesai</label>
                <input type="date" wire:model.live="endDate" class="report-input">
            </div>
            <div>
                <label class="report-label">Kasir</label>
                <select wire:model.live="cashierId" class="report-input">
                    <option value="">Semua Kasir</option>
                    @foreach ($this->cashiers as $cashier)
                        <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="export-actions">
                <a class="export-btn export-pdf" href="{{ route('admin.report-exports.sales', array_merge(['format' => 'pdf'], ['start_date' => $startDate, 'end_date' => $endDate, 'cashier_id' => $cashierId])) }}">Export PDF</a>
                <a class="export-btn export-excel" href="{{ route('admin.report-exports.sales', array_merge(['format' => 'excel'], ['start_date' => $startDate, 'end_date' => $endDate, 'cashier_id' => $cashierId])) }}">Export Excel</a>
            </div>
        </div>

        <div class="summary-grid">
            <div class="report-card"><div class="summary-title">Total Penjualan</div><div class="summary-value">{{ $this->rupiah($this->summary['total_sales']) }}</div></div>
            <div class="report-card"><div class="summary-title">Jumlah Transaksi</div><div class="summary-value">{{ number_format($this->summary['transaction_count'], 0, ',', '.') }}</div></div>
            <div class="report-card"><div class="summary-title">Laba Kotor</div><div class="summary-value">{{ $this->rupiah($this->summary['gross_profit']) }}</div></div>
            <div class="report-card"><div class="summary-title">Subtotal</div><div class="summary-value">{{ $this->rupiah($this->summary['total_subtotal']) }}</div></div>
            <div class="report-card"><div class="summary-title">Diskon</div><div class="summary-value">{{ $this->rupiah($this->summary['total_discount']) }}</div></div>
            <div class="report-card"><div class="summary-title">Pajak</div><div class="summary-value">{{ $this->rupiah($this->summary['total_tax']) }}</div></div>
        </div>

        <div class="report-card">
            <h3 style="font-weight: 800; margin-bottom: 12px;">Ringkasan Harian</h3>
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead><tr><th>Tanggal</th><th class="text-right">Transaksi</th><th class="text-right">Subtotal</th><th class="text-right">Diskon</th><th class="text-right">Pajak</th><th class="text-right">Grand Total</th></tr></thead>
                    <tbody>
                        @forelse ($this->dailyRows as $row)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($row['date'])->format('d M Y') }}</td>
                                <td class="text-right">{{ number_format($row['count'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ $this->rupiah($row['subtotal']) }}</td>
                                <td class="text-right">{{ $this->rupiah($row['discount']) }}</td>
                                <td class="text-right">{{ $this->rupiah($row['tax']) }}</td>
                                <td class="text-right"><strong>{{ $this->rupiah($row['grand_total']) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Tidak ada data.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="report-card">
            <h3 style="font-weight: 800; margin-bottom: 12px;">Detail Transaksi</h3>
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead><tr><th>Invoice</th><th>Tanggal</th><th>Kasir</th><th>Customer</th><th class="text-right">Total</th></tr></thead>
                    <tbody>
                        @forelse ($this->transactions as $transaction)
                            <tr>
                                <td>{{ $transaction->invoice_number }}</td>
                                <td>{{ $transaction->transaction_date->format('d M Y H:i') }}</td>
                                <td>{{ $transaction->cashier?->name }}</td>
                                <td>{{ $transaction->customer?->name ?? 'Walk-in' }}</td>
                                <td class="text-right"><strong>{{ $this->rupiah($transaction->grand_total) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">Tidak ada transaksi.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
