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
        .status-pill { display: inline-flex; border-radius: 999px; padding: 4px 9px; font-size: 12px; font-weight: 800; white-space: nowrap; }
        .status-healthy { background: rgb(220 252 231); color: rgb(22 101 52); }
        .status-low { background: rgb(254 249 195); color: rgb(133 77 14); }
        .status-danger { background: rgb(254 226 226); color: rgb(153 27 27); }
        .status-muted { background: rgb(229 231 235); color: rgb(75 85 99); }

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

        <div class="summary-grid">
            <div class="report-card"><div class="summary-title">Total Produk/Varian</div><div class="summary-value">{{ number_format($this->priceSummary['products'], 0, ',', '.') }}</div></div>
            <div class="report-card"><div class="summary-title">Produk Aktif</div><div class="summary-value">{{ number_format($this->priceSummary['active_products'], 0, ',', '.') }}</div></div>
            <div class="report-card"><div class="summary-title">Rata-rata Margin</div><div class="summary-value">{{ number_format($this->priceSummary['average_margin'], 1, ',', '.') }}%</div></div>
            <div class="report-card"><div class="summary-title">Perlu Ditinjau</div><div class="summary-value">{{ number_format($this->priceSummary['low_margin'] + $this->priceSummary['no_profit'], 0, ',', '.') }}</div></div>
        </div>

        <div class="report-card">
            <h3 style="font-weight: 800; margin-bottom: 12px;">Evaluasi Harga Semua Produk</h3>
            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Menu</th>
                            <th>Varian</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-right">Harga Modal</th>
                            <th class="text-right">Keuntungan</th>
                            <th class="text-right">Margin</th>
                            <th class="text-right">Markup</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->priceRows as $row)
                            @php
                                $statusClass = match ($row['status']) {
                                    'Sehat' => 'status-healthy',
                                    'Margin Rendah' => 'status-low',
                                    'Rugi / Impas' => 'status-danger',
                                    default => 'status-muted',
                                };
                            @endphp
                            <tr>
                                <td>{{ $row['category'] }}</td>
                                <td>{{ $row['menu'] }}</td>
                                <td>{{ $row['variant'] }}</td>
                                <td class="text-right">{{ $this->rupiah($row['selling_price']) }}</td>
                                <td class="text-right">{{ $this->rupiah($row['cost_price']) }}</td>
                                <td class="text-right"><strong>{{ $this->rupiah($row['profit']) }}</strong></td>
                                <td class="text-right">{{ number_format($row['margin_percent'], 1, ',', '.') }}%</td>
                                <td class="text-right">{{ number_format($row['markup_percent'], 1, ',', '.') }}%</td>
                                <td><span class="status-pill {{ $statusClass }}">{{ $row['status'] }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="9">Tidak ada data produk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
