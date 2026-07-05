<x-filament-panels::page>
    <style>
        .cash-grid { display: grid; grid-template-columns: minmax(0, 1fr) 380px; gap: 16px; align-items: start; }
        .cash-card { background: white; border: 1px solid rgb(229 231 235); border-radius: 16px; padding: 16px; box-shadow: 0 8px 22px rgb(15 23 42 / 6%); }
        .dark .cash-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .cash-title { font-size: 18px; font-weight: 900; margin-bottom: 4px; }
        .cash-muted { color: rgb(107 114 128); font-size: 13px; }
        .cash-input, .cash-select { width: 100%; border-radius: 12px; border: 1px solid rgb(209 213 219); background: white; color: rgb(17 24 39); padding: 10px 12px; font-size: 14px; outline: none; }
        .cash-input:focus, .cash-select:focus { border-color: rgb(245 158 11); box-shadow: 0 0 0 3px rgb(245 158 11 / 16%); }
        .dark .cash-input, .dark .cash-select { background: rgb(31 41 55); border-color: rgb(75 85 99); color: white; }
        .customer-search { position: relative; margin-top: 12px; }
        .customer-dropdown { position: absolute; z-index: 30; top: calc(100% + 6px); left: 0; right: 0; max-height: 260px; overflow-y: auto; border: 1px solid rgb(229 231 235); border-radius: 12px; background: white; box-shadow: 0 16px 35px rgb(15 23 42 / 16%); }
        .dark .customer-dropdown { background: rgb(31 41 55); border-color: rgb(75 85 99); }
        .customer-option { width: 100%; display: block; border: 0; background: transparent; padding: 10px 12px; text-align: left; cursor: pointer; color: rgb(17 24 39); }
        .dark .customer-option { color: white; }
        .customer-option:hover { background: rgb(254 243 199); }
        .dark .customer-option:hover { background: rgb(55 65 81); }
        .customer-option-title { font-size: 14px; font-weight: 800; }
        .customer-option-subtitle { margin-top: 2px; color: rgb(107 114 128); font-size: 12px; }
        .customer-selected { display: flex; justify-content: space-between; gap: 8px; align-items: center; margin-top: 8px; border-radius: 10px; background: rgb(255 251 235); color: rgb(146 64 14); padding: 8px 10px; font-size: 12px; font-weight: 800; }
        .customer-clear { border: 0; background: transparent; color: rgb(185 28 28); cursor: pointer; font-size: 12px; font-weight: 900; }
        .cash-summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
        .summary-box { border: 1px solid rgb(229 231 235); border-radius: 14px; padding: 12px; }
        .summary-label { font-size: 12px; color: rgb(107 114 128); font-weight: 800; text-transform: uppercase; }
        .summary-value { margin-top: 4px; font-size: 22px; font-weight: 900; }
        .cash-table { width: 100%; border-collapse: collapse; margin-top: 14px; font-size: 14px; }
        .cash-table th, .cash-table td { border-bottom: 1px solid rgb(229 231 235); padding: 10px 8px; text-align: left; }
        .cash-table th { color: rgb(107 114 128); font-size: 12px; text-transform: uppercase; }
        .text-right { text-align: right !important; }
        .cash-form { display: grid; gap: 12px; }
        .cash-label { display: block; margin-bottom: 6px; font-size: 13px; font-weight: 800; color: rgb(55 65 81); }
        .cash-row { display: flex; justify-content: space-between; gap: 12px; align-items: center; padding: 8px 0; border-bottom: 1px dashed rgb(229 231 235); }
        .cash-grand { font-size: 24px; font-weight: 900; color: rgb(217 119 6); }
        .cash-btn { width: 100%; border: 0; border-radius: 12px; padding: 11px 14px; cursor: pointer; font-size: 14px; font-weight: 900; }
        .cash-btn-primary { background: rgb(217 119 6); color: white; }
        .cash-btn-secondary { background: rgb(243 244 246); color: rgb(55 65 81); }
        .cash-btn:disabled { opacity: .55; cursor: not-allowed; }
        .empty-state { text-align: center; color: rgb(107 114 128); padding: 32px 12px; }
        @media (max-width: 1100px) { .cash-grid, .cash-summary { grid-template-columns: 1fr; } }
    </style>

    <div class="cash-grid">
        <div class="cash-card">
            <div class="cash-title">Tagihan Customer</div>
            <div class="cash-muted">Pilih customer untuk melihat seluruh transaksi draft yang belum dibayar.</div>

            <div class="customer-search" x-data="{ open: false }" @click.outside="open = false">
                <input
                    type="text"
                    wire:model.live.debounce.300ms="customerSearch"
                    @focus="open = true"
                    @input="open = true"
                    placeholder="Cari customer / no HP"
                    class="cash-input"
                />

                @if ($this->selectedCustomer)
                    <div class="customer-selected">
                        <span>Customer: {{ $this->selectedCustomer->name }}</span>
                        <button type="button" wire:click="clearCustomer" class="customer-clear">Hapus</button>
                    </div>
                @endif

                <div x-show="open" x-cloak class="customer-dropdown">
                    @forelse ($this->filteredCustomers as $customer)
                        <button type="button" wire:click="selectCustomer({{ $customer->id }})" @click="open = false" class="customer-option">
                            <div class="customer-option-title">{{ $customer->name }}</div>
                            <div class="customer-option-subtitle">{{ $customer->phone_number ?: 'No HP belum diisi' }}</div>
                        </button>
                    @empty
                        <div class="customer-option">
                            <div class="customer-option-title">Customer tidak ditemukan</div>
                            <div class="customer-option-subtitle">Hanya customer dengan transaksi draft yang muncul.</div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="cash-summary">
                <div class="summary-box"><div class="summary-label">Jumlah Tagihan</div><div class="summary-value">{{ number_format($this->totalTransactions, 0, ',', '.') }}</div></div>
                <div class="summary-box"><div class="summary-label">Total Tagihan</div><div class="summary-value">{{ $this->rupiah($this->totalBill) }}</div></div>
                <div class="summary-box"><div class="summary-label">Sisa Kurang</div><div class="summary-value">{{ $this->rupiah($this->remainingAmount) }}</div></div>
            </div>

            <div style="overflow-x: auto;">
                <table class="cash-table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Tanggal</th>
                            <th class="text-right">Item</th>
                            <th class="text-right">Grand Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->unpaidTransactions as $transaction)
                            <tr>
                                <td><strong>{{ $transaction->invoice_number }}</strong></td>
                                <td>{{ $transaction->transaction_date->format('d M Y H:i') }}</td>
                                <td class="text-right">{{ number_format($transaction->items_count, 0, ',', '.') }}</td>
                                <td class="text-right"><strong>{{ $this->rupiah($transaction->grand_total) }}</strong></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="empty-state">Belum ada tagihan customer yang dipilih.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="cash-card">
            <div class="cash-title">Pembayaran</div>
            <div class="cash-muted">Masukkan uang masuk untuk melunasi semua tagihan customer.</div>

            <div class="cash-form" style="margin-top: 14px;">
                <div>
                    <label class="cash-label">Metode Pembayaran</label>
                    <select wire:model="paymentMethod" class="cash-select">
                        <option value="cash">Cash</option>
                        <option value="qris">QRIS</option>
                        <option value="transfer">Transfer</option>
                        <option value="debit">Debit</option>
                    </select>
                </div>

                <div>
                    <label class="cash-label">Uang Bayar</label>
                    <input type="number" min="0" wire:model.blur="amountPaid" class="cash-input" placeholder="0">
                </div>

                <button type="button" wire:click="fillExactAmount" class="cash-btn cash-btn-secondary">Isi Sesuai Total Tagihan</button>

                <div>
                    <div class="cash-row"><span>Total Tagihan</span><strong>{{ $this->rupiah($this->totalBill) }}</strong></div>
                    <div class="cash-row"><span>Uang Bayar</span><strong>{{ $this->rupiah((int) ($amountPaid ?: 0)) }}</strong></div>
                    <div class="cash-row"><span>Kembalian</span><strong style="color: rgb(22 163 74);">{{ $this->rupiah($this->changeAmount) }}</strong></div>
                    <div class="cash-row"><span><strong>Grand Total</strong></span><span class="cash-grand">{{ $this->rupiah($this->totalBill) }}</span></div>
                </div>

                <button
                    type="button"
                    wire:click="payAll"
                    wire:loading.attr="disabled"
                    wire:target="payAll"
                    @disabled(! $this->customerId || $this->totalBill <= 0)
                    class="cash-btn cash-btn-primary"
                >
                    <span wire:loading.remove wire:target="payAll">Bayar Semua Tagihan</span>
                    <span wire:loading wire:target="payAll">Memproses...</span>
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
