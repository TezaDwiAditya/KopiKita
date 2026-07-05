<x-filament-panels::page>
    <style>
        .report-grid { display: grid; gap: 16px; }
        .report-filters { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; align-items: end; }
        .report-card { background: white; border: 1px solid rgb(229 231 235); border-radius: 16px; padding: 16px; box-shadow: 0 8px 22px rgb(15 23 42 / 6%); }
        .dark .report-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .report-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: rgb(55 65 81); }
        .report-input { width: 100%; border-radius: 12px; border: 1px solid rgb(209 213 219); padding: 9px 11px; font-size: 14px; }
        .summary-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .summary-title { color: rgb(107 114 128); font-size: 13px; font-weight: 700; }
        .summary-value { margin-top: 4px; font-size: 22px; font-weight: 900; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .report-table th, .report-table td { padding: 10px 12px; border-bottom: 1px solid rgb(229 231 235); text-align: left; }
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
            <div><label class="report-label">Tanggal Mulai</label><input type="date" wire:model.live="startDate" class="report-input"></div>
            <div><label class="report-label">Tanggal Selesai</label><input type="date" wire:model.live="endDate" class="report-input"></div>
            <div class="export-actions">
                <a class="export-btn export-pdf" href="{{ route('admin.report-exports.products', array_merge(['format' => 'pdf'], ['start_date' => $startDate, 'end_date' => $endDate])) }}">Export PDF</a>
                <a class="export-btn export-excel" href="{{ route('admin.report-exports.products', array_merge(['format' => 'excel'], ['start_date' => $startDate, 'end_date' => $endDate])) }}">Export Excel</a>
            </div>
        </div>

        <div class="summary-grid">
            <div class="report-card"><div class="summary-title">Qty Terjual</div><div class="summary-value">{{ number_format($this->summary['qty'], 0, ',', '.') }}</div></div>
            <div class="report-card"><div class="summary-title">Total Penjualan</div><div class="summary-value">{{ $this->rupiah($this->summary['sales']) }}</div></div>
            <div class="report-card"><div class="summary-title">Total Modal</div><div class="summary-value">{{ $this->rupiah($this->summary['cost']) }}</div></div>
            <div class="report-card"><div class="summary-title">Laba Kotor</div><div class="summary-value">{{ $this->rupiah($this->summary['gross_profit']) }}</div></div>
        </div>

        <div class="report-card">
            <h3 style="font-weight: 800; margin-bottom: 12px;">Produk Terjual</h3>
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead><tr><th>Menu</th><th>Varian</th><th class="text-right">Qty</th><th class="text-right">Penjualan</th><th class="text-right">Modal</th><th class="text-right">Laba Kotor</th></tr></thead>
                    <tbody>
                        @forelse ($this->rows as $row)
                            <tr>
                                <td>{{ $row['menu'] }}</td>
                                <td>{{ $row['variant'] }}</td>
                                <td class="text-right">{{ number_format($row['qty'], 0, ',', '.') }}</td>
                                <td class="text-right">{{ $this->rupiah($row['sales']) }}</td>
                                <td class="text-right">{{ $this->rupiah($row['cost']) }}</td>
                                <td class="text-right"><strong>{{ $this->rupiah($row['gross_profit']) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">Tidak ada data produk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
