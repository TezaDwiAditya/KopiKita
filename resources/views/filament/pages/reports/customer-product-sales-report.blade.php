<x-filament-panels::page>
    <style>
        .customer-sales-grid { display: grid; gap: 16px; }
        .customer-sales-card { background: white; border: 1px solid rgb(229 231 235); border-radius: 16px; padding: 16px; box-shadow: 0 8px 22px rgb(15 23 42 / 6%); }
        .dark .customer-sales-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .customer-sales-filters { display: grid; grid-template-columns: 1.2fr repeat(2, minmax(0, 1fr)) auto; gap: 12px; align-items: end; }
        .customer-sales-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 700; color: rgb(55 65 81); }
        .customer-sales-input { width: 100%; border-radius: 12px; border: 1px solid rgb(209 213 219); padding: 9px 11px; font-size: 14px; }
        .customer-sales-actions { display: flex; gap: 8px; align-items: center; }
        .customer-sales-btn { display: inline-flex; justify-content: center; border-radius: 10px; padding: 9px 12px; font-size: 13px; font-weight: 800; text-decoration: none; white-space: nowrap; }
        .customer-sales-pdf { background: #dc2626; color: white; }
        .customer-sales-excel { background: #16a34a; color: white; }
        .report-paper { background: white; border: 1px solid rgb(229 231 235); border-radius: 8px; padding: 22px; box-shadow: 0 8px 22px rgb(15 23 42 / 6%); color: #111; }
        .report-brand { display: flex; align-items: center; padding-bottom: 34px; border-bottom: 2px solid #999; }
        .brand-left { font-size: 42px; line-height: 1; font-weight: 900; letter-spacing: 0; }
        .report-head { display: grid; grid-template-columns: 1fr auto; gap: 24px; padding: 16px 16px 26px; border-bottom: 2px solid #999; }
        .report-title { font-size: 28px; font-weight: 900; margin-bottom: 8px; }
        .period-row, .summary-row { display: grid; grid-template-columns: auto auto; gap: 18px; font-size: 22px; line-height: 1.35; }
        .summary-row { grid-template-columns: auto 1fr; gap: 28px; font-weight: 800; }
        .summary-row .amount { text-align: right; white-space: nowrap; }
        .report-meta { display: flex; justify-content: space-between; align-items: center; gap: 18px; margin: 48px 0 12px; font-size: 20px; }
        .report-table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .report-table th, .report-table td { padding: 12px 14px; border: 1px solid rgb(209 213 219); text-align: left; vertical-align: top; }
        .report-table th { font-size: 18px; color: #111; font-weight: 900; }
        .report-table td { font-size: 18px; }
        .item-detail { color: rgb(75 85 99); font-size: 14px; line-height: 1.45; margin-top: 4px; }
        .text-right { text-align: right !important; }
        .font-bold { font-weight: 800; }
        @media (max-width: 1000px) { .customer-sales-filters { grid-template-columns: 1fr; } .customer-sales-actions { flex-wrap: wrap; } .report-brand, .report-head, .report-meta { display: block; } .brand-left { font-size: 32px; } .report-meta { font-size: 16px; } .report-table th, .report-table td { font-size: 14px; } }
    </style>

    <div class="customer-sales-grid">
        <div class="customer-sales-card customer-sales-filters">
            <div>
                <label class="customer-sales-label">Customer</label>
                <select wire:model.live="customerId" class="customer-sales-input">
                    <option value="">Semua Customer</option>
                    @foreach ($this->customers as $customerOption)
                        <option value="{{ $customerOption->id }}" @selected((string) $customerId === (string) $customerOption->id)>{{ $customerOption->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="customer-sales-label">Tanggal Mulai</label>
                <input type="date" wire:model.live="startDate" class="customer-sales-input">
            </div>
            <div>
                <label class="customer-sales-label">Tanggal Selesai</label>
                <input type="date" wire:model.live="endDate" class="customer-sales-input">
            </div>
            <div class="customer-sales-actions">
                <a class="customer-sales-btn customer-sales-pdf" href="{{ $this->exportUrl('pdf') }}">Export PDF</a>
                <a class="customer-sales-btn customer-sales-excel" href="{{ $this->exportUrl('excel') }}">Export Excel</a>
            </div>
        </div>

        <div class="report-paper">
            <div class="report-brand">
                <div class="brand-left">{{ $this->setting?->store_name ?? 'KopiKita' }}</div>
            </div>

            <div class="report-head">
                <div>
                    <div class="report-title">Laporan Penjualan</div>
                    <div class="period-row"><span>Dari:</span><strong>{{ \Illuminate\Support\Carbon::parse($startDate)->format('d M Y') }}</strong></div>
                    <div class="period-row"><span>Kepada:</span><strong>{{ \Illuminate\Support\Carbon::parse($endDate)->format('d M Y') }}</strong></div>
                </div>
                <div>
                    <div class="summary-row"><span>Diterima:</span><span class="amount">{{ $this->rupiah($this->summary['paid']) }}</span></div>
                    <div class="summary-row"><span>Belum Dibayar:</span><span class="amount">{{ $this->rupiah($this->summary['unpaid']) }}</span></div>
                    <div class="summary-row"><span>Total Penjualan:</span><span class="amount">{{ $this->rupiah($this->summary['sales']) }}</span></div>
                </div>
            </div>

            <div class="report-meta">
                <div>Total Transaksi : {{ number_format($this->summary['transaction_count'], 0, ',', '.') }}</div>
                <div>Laporan Dibuat pada: {{ $this->generatedAt()->format('d M Y') }} &bull; {{ $this->generatedAt()->format('H:i') }}</div>
            </div>

            <div style="overflow-x: auto;">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>No. Faktur</th>
                            <th>Nama Pihak</th>
                            <th>Tanggal Faktur</th>
                            <th>Produk</th>
                            <th class="text-right">Jumlah</th>
                            <th class="text-right">Harga Jual</th>
                            <th class="text-right">Jumlah Total</th>
                            <th class="text-right">Diterima</th>
                            <th class="text-right">Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->rows as $row)
                            <tr>
                                <td>{{ $row['invoice'] }}</td>
                                <td>{{ $row['customer_name'] }}</td>
                                <td>{{ $row['date'] }}</td>
                                <td>
                                    @foreach ($row['items'] as $item)
                                        <div>{{ $item['name'] }}</div>
                                    @endforeach
                                </td>
                                <td class="text-right">
                                    @foreach ($row['items'] as $item)
                                        <div>{{ number_format($item['qty'], 0, ',', '.') }}</div>
                                    @endforeach
                                </td>
                                <td class="text-right">
                                    @foreach ($row['items'] as $item)
                                        <div>{{ $this->rupiah($item['price']) }}</div>
                                    @endforeach
                                </td>
                                <td class="text-right">{{ $this->rupiah($row['amount']) }}</td>
                                <td class="text-right">{{ $this->rupiah($row['paid']) }}</td>
                                <td class="text-right">{{ $this->rupiah($row['balance']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="9">Tidak ada data penjualan customer untuk periode ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-filament-panels::page>
