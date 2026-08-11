<x-filament-panels::page>
    <style>
        .pos-shell { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 24px; }
        .pos-card { background: rgb(255 255 255); border: 1px solid rgb(229 231 235); border-radius: 18px; box-shadow: 0 8px 24px rgb(15 23 42 / 8%); }
        .dark .pos-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .pos-toolbar { padding: 18px; }
        .pos-toolbar-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
        .pos-title { font-size: 18px; font-weight: 700; color: rgb(17 24 39); }
        .dark .pos-title { color: white; }
        .pos-subtitle { margin-top: 3px; color: rgb(107 114 128); font-size: 13px; }
        .pos-input, .pos-select, .pos-textarea { width: 100%; border-radius: 12px; border: 1px solid rgb(209 213 219); background: white; color: rgb(17 24 39); padding: 10px 12px; font-size: 14px; outline: none; }
        .customer-search { position: relative; }
        .customer-dropdown { position: absolute; z-index: 30; top: calc(100% + 6px); left: 0; right: 0; max-height: 240px; overflow-y: auto; border: 1px solid rgb(229 231 235); border-radius: 12px; background: white; box-shadow: 0 16px 35px rgb(15 23 42 / 16%); }
        .customer-option { width: 100%; display: block; border: 0; background: transparent; padding: 10px 12px; text-align: left; cursor: pointer; color: rgb(17 24 39); }
        .customer-option:hover { background: rgb(254 243 199); }
        .customer-option-title { font-size: 14px; font-weight: 800; }
        .customer-option-subtitle { margin-top: 2px; color: rgb(107 114 128); font-size: 12px; }
        .customer-selected { display: flex; justify-content: space-between; gap: 8px; align-items: center; margin-top: 8px; border-radius: 10px; background: rgb(255 251 235); color: rgb(146 64 14); padding: 8px 10px; font-size: 12px; font-weight: 800; }
        .customer-clear { border: 0; background: transparent; color: rgb(185 28 28); cursor: pointer; font-size: 12px; font-weight: 900; }
        .pos-input:focus, .pos-select:focus, .pos-textarea:focus { border-color: rgb(245 158 11); box-shadow: 0 0 0 3px rgb(245 158 11 / 16%); }
        .dark .pos-input, .dark .pos-select, .dark .pos-textarea { background: rgb(31 41 55); border-color: rgb(75 85 99); color: white; }
        .dark .customer-dropdown { background: rgb(31 41 55); border-color: rgb(75 85 99); }
        .dark .customer-option { color: white; }
        .dark .customer-option:hover { background: rgb(55 65 81); }
        .dark .customer-selected { background: rgb(69 26 3); color: rgb(253 186 116); }
        .pos-search { max-width: 320px; }
        .pos-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
        .pos-tab { border: 0; border-radius: 999px; padding: 9px 14px; cursor: pointer; font-size: 13px; font-weight: 700; background: rgb(243 244 246); color: rgb(55 65 81); }
        .pos-tab:hover { background: rgb(229 231 235); }
        .pos-tab.active { background: rgb(217 119 6); color: white; }
        .dark .pos-tab { background: rgb(31 41 55); color: rgb(229 231 235); }
        .dark .pos-tab:hover { background: rgb(55 65 81); }
        .pos-menu-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .pos-menu-button { overflow: hidden; text-align: left; cursor: pointer; border: 1px solid rgb(229 231 235); border-radius: 18px; background: white; box-shadow: 0 8px 20px rgb(15 23 42 / 7%); transition: transform .15s ease, box-shadow .15s ease; }
        .pos-menu-button:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgb(15 23 42 / 12%); }
        .dark .pos-menu-button { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .pos-menu-image { height: 128px; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, rgb(254 243 199), rgb(255 237 213)); font-size: 40px; }
        .dark .pos-menu-image { background: linear-gradient(135deg, rgb(120 53 15 / 45%), rgb(124 45 18 / 45%)); }
        .pos-menu-image img { width: 100%; height: 100%; object-fit: cover; }
        .pos-menu-body { padding: 14px; }
        .pos-menu-name { color: rgb(17 24 39); font-weight: 800; line-height: 1.25; }
        .dark .pos-menu-name { color: white; }
        .pos-menu-category { color: rgb(107 114 128); font-size: 12px; margin-top: 3px; }
        .pos-menu-price { display: inline-flex; margin-top: 12px; border-radius: 999px; background: rgb(255 251 235); color: rgb(180 83 9); padding: 5px 10px; font-size: 12px; font-weight: 800; } .pos-variants { display: grid; gap: 7px; margin-top: 12px; } .pos-variant-btn { width: 100%; border: 1px solid rgb(245 158 11 / 35%); border-radius: 10px; background: rgb(255 251 235); color: rgb(120 53 15); padding: 8px 9px; cursor: pointer; display: flex; justify-content: space-between; gap: 8px; font-size: 12px; font-weight: 800; } .pos-variant-btn:hover { background: rgb(254 243 199); }
        .dark .pos-menu-price { background: rgb(245 158 11 / 12%); color: rgb(252 211 77); } .dark .pos-variant-btn { background: rgb(245 158 11 / 12%); border-color: rgb(245 158 11 / 25%); color: rgb(252 211 77); }
        .pos-cart { position: sticky; top: 24px; padding: 18px; }
        .pos-cart-head { display: flex; align-items: start; justify-content: space-between; gap: 16px; }
        .pos-link-danger { color: rgb(220 38 38); font-size: 13px; font-weight: 800; background: transparent; border: 0; cursor: pointer; }
        .pos-cart-items { margin-top: 16px; display: grid; gap: 12px; }
        .pos-cart-item { border: 1px solid rgb(229 231 235); border-radius: 14px; padding: 12px; }
        .dark .pos-cart-item { border-color: rgb(55 65 81); }
        .pos-row { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .pos-item-actions { display: inline-flex; align-items: center; gap: 10px; }
        .pos-item-name { font-weight: 800; color: rgb(17 24 39); }
        .dark .pos-item-name { color: white; }
        .pos-muted { color: rgb(107 114 128); font-size: 13px; }
        .pos-qty { display: inline-flex; align-items: center; border-radius: 999px; background: rgb(243 244 246); padding: 4px; }
        .dark .pos-qty { background: rgb(31 41 55); }
        .pos-qty button { width: 30px; height: 30px; border: 0; border-radius: 999px; background: white; color: rgb(17 24 39); cursor: pointer; font-weight: 900; }
        .dark .pos-qty button { background: rgb(55 65 81); color: white; }
        .pos-qty span { min-width: 36px; text-align: center; font-weight: 800; color: rgb(17 24 39); }
        .dark .pos-qty span { color: white; }
        .pos-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgb(229 231 235); display: grid; gap: 10px; }
        .dark .pos-section { border-color: rgb(55 65 81); }
        .pos-total-row { display: flex; justify-content: space-between; gap: 12px; color: rgb(55 65 81); font-size: 14px; }
        .dark .pos-total-row { color: rgb(229 231 235); }
        .pos-grand { color: rgb(217 119 6); font-size: 18px; font-weight: 900; }
        .pos-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-top: 16px; }
        .pos-btn { border: 0; border-radius: 13px; padding: 12px 14px; font-weight: 900; cursor: pointer; } .pos-btn:disabled { cursor: not-allowed; opacity: .55; }
        .pos-btn-secondary { background: rgb(243 244 246); color: rgb(55 65 81); }
        .pos-btn-secondary:hover { background: rgb(229 231 235); }
        .pos-btn-primary { background: rgb(217 119 6); color: white; }
        .pos-btn-primary:hover { background: rgb(180 83 9); }
        .pos-btn-outline { grid-column: span 2 / span 2; background: transparent; border: 1px solid rgb(209 213 219); color: rgb(55 65 81); }
        .dark .pos-btn-secondary { background: rgb(31 41 55); color: rgb(229 231 235); }
        .dark .pos-btn-outline { border-color: rgb(75 85 99); color: rgb(229 231 235); }
        .pos-empty { border: 1px dashed rgb(209 213 219); border-radius: 14px; padding: 24px; text-align: center; color: rgb(107 114 128); }
        .dark .pos-empty { border-color: rgb(75 85 99); color: rgb(156 163 175); }
        @media (max-width: 1100px) { .pos-shell { grid-template-columns: 1fr; } .pos-cart { position: static; } }
        @media (max-width: 760px) { .pos-toolbar-head { display: grid; } .pos-search { max-width: none; } .pos-menu-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 520px) { .pos-menu-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="pos-shell">
        <div>
            <div class="pos-card pos-toolbar">
                <div class="pos-toolbar-head">
                    <div>
                        <div class="pos-title">Pilih Menu</div>
                        <div class="pos-subtitle">Cari menu berdasarkan kategori atau nama.</div>
                    </div>
                    <input type="search" wire:model.live.debounce.300ms="search" placeholder="Search menu..." class="pos-input pos-search" />
                </div>

                <div class="pos-tabs">
                    <button type="button" wire:click="selectCategory(null)" class="pos-tab {{ blank($selectedCategoryId) ? 'active' : '' }}">Semua</button>
                    @foreach ($this->categories as $category)
                        <button type="button" wire:click="selectCategory({{ $category->id }})" class="pos-tab {{ $selectedCategoryId === $category->id ? 'active' : '' }}">
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>
            </div>

            <div class="pos-menu-grid" style="margin-top: 18px;">
                @forelse ($this->menus as $menu)
                    <div class="pos-menu-button">
                        <div class="pos-menu-image">
                            @if ($menu->photo_path)
                                <img src="{{ Storage::url($menu->photo_path) }}" alt="{{ $menu->name }}">
                            @endif
                        </div>
                        <div class="pos-menu-body">
                            <div class="pos-menu-name">{{ $menu->name }}</div>
                            <div class="pos-menu-category">{{ $menu->category?->name }}</div>
                            <div class="pos-variants">
                                @forelse ($menu->activeVariants as $variant)
                                    <button type="button" wire:click="addToCartVariant({{ $variant->id }})" class="pos-variant-btn">
                                        <span>{{ $variant->name }}</span>
                                        <span>Rp {{ number_format($variant->selling_price, 0, ',', '.') }}</span>
                                    </button>
                                @empty
                                    <button type="button" wire:click="addToCart({{ $menu->id }})" class="pos-variant-btn">
                                        <span>Regular</span>
                                        <span>Rp {{ number_format($menu->selling_price, 0, ',', '.') }}</span>
                                    </button>
                                @endforelse
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="pos-card pos-empty" style="grid-column: 1 / -1;">Menu tidak ditemukan.</div>
                @endforelse
            </div>
        </div>

        <div class="pos-card pos-cart">
            <div class="pos-cart-head">
                <div>
                    <div class="pos-title">Keranjang</div>
                    <div class="pos-subtitle">{{ count($cart) }} item menu</div>
                </div>
                <button type="button" wire:click="voidCart" class="pos-link-danger">Void</button>
            </div>

            <div class="pos-cart-items">
                @forelse ($cart as $menuId => $item)
                    <div class="pos-cart-item">
                        <div class="pos-row">
                            <div>
                                <div class="pos-item-name">{{ $item['name'] }} @if ($item['variant_name'] ?? null) - {{ $item['variant_name'] }} @endif</div>
                                <div class="pos-muted">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                            </div>
                            <div class="pos-item-actions">
                                <button type="button" wire:click="duplicateItem('{{ $menuId }}')" class="pos-link-danger" style="color: rgb(217 119 6);">Pisah</button>
                                <button type="button" wire:click="removeItem('{{ $menuId }}')" class="pos-link-danger">Hapus</button>
                            </div>
                        </div>
                        <div class="pos-row" style="margin-top: 12px;">
                            <div class="pos-qty">
                                <button type="button" wire:click="decrementQty('{{ $menuId }}')">-</button>
                                <span>{{ $item['qty'] }}</span>
                                <button type="button" wire:click="incrementQty('{{ $menuId }}')">+</button>
                            </div>
                            <strong>Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</strong>
                        </div>
                        <input type="text" wire:model.blur="cart.{{ $menuId }}.note" placeholder="Catatan item" class="pos-input" style="margin-top: 12px;" />
                    </div>
                @empty
                    <div class="pos-empty">Klik menu untuk menambahkan ke keranjang.</div>
                @endforelse
            </div>

            <div class="pos-section">
                <div class="customer-search" x-data="{ open: false }" @click.outside="open = false">
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="customerSearch"
                        @focus="open = true"
                        @input="open = true"
                        placeholder="Cari customer / no HP (kosongkan untuk Walk-in)"
                        class="pos-input"
                    />

                    @if ($this->selectedCustomer)
                        <div class="customer-selected">
                            <span>Customer: {{ $this->selectedCustomer->name }}</span>
                            <button type="button" wire:click="clearCustomer" class="customer-clear">Hapus</button>
                        </div>
                    @endif

                    <div x-show="open" x-cloak class="customer-dropdown">
                        <button type="button" wire:click="clearCustomer" @click="open = false" class="customer-option">
                            <div class="customer-option-title">Walk-in</div>
                            <div class="customer-option-subtitle">Transaksi tanpa customer</div>
                        </button>

                        @forelse ($this->filteredCustomers as $customer)
                            <button type="button" wire:click="selectCustomer({{ $customer->id }})" @click="open = false" class="customer-option">
                                <div class="customer-option-title">{{ $customer->name }}</div>
                                <div class="customer-option-subtitle">{{ $customer->phone_number ?: 'No HP belum diisi' }}</div>
                            </button>
                        @empty
                            <div class="customer-option">
                                <div class="customer-option-title">Customer tidak ditemukan</div>
                                <div class="customer-option-subtitle">Coba kata kunci lain atau gunakan Walk-in.</div>
                            </div>
                        @endforelse
                    </div>
                </div>

                <input
                    type="date"
                    wire:model.live="transactionDate"
                    class="pos-input"
                    onclick="this.showPicker?.()"
                    onfocus="this.showPicker?.()"
                />
                <textarea wire:model.blur="note" rows="2" placeholder="Catatan transaksi" class="pos-textarea"></textarea>
            </div>

            <div class="pos-section">
                <div class="pos-total-row"><span>Subtotal</span><strong>Rp {{ number_format($this->subtotal, 0, ',', '.') }}</strong></div>
                <div class="pos-total-row"><span>Diskon</span><input type="number" min="0" wire:model.live.debounce.300ms="discount" class="pos-input" style="width: 150px; text-align: right;" /></div>
                <div class="pos-total-row"><span>Pajak</span><strong>Rp {{ number_format($tax, 0, ',', '.') }}</strong></div>
                <div class="pos-total-row"><span><strong>Grand Total</strong></span><span class="pos-grand">Rp {{ number_format($this->grandTotal, 0, ',', '.') }}</span></div>
            </div>

            <div class="pos-section">
                <select wire:model.live="paymentMethod" class="pos-select">
                    <option value="cash">Cash</option>
                    <option value="qris">QRIS</option>
                    <option value="transfer">Transfer</option>
                    <option value="debit">Debit</option>
                </select>
                <input type="number" min="0" wire:model.blur="amountPaid" placeholder="Uang bayar" class="pos-input" @disabled($paymentMethod !== 'cash') />
                <div class="pos-total-row"><span>Kembalian</span><strong style="color: rgb(22 163 74);">Rp {{ number_format($this->changeAmount, 0, ',', '.') }}</strong></div>
            </div>

            <div class="pos-actions">
                <button type="button" wire:click="saveDraft" wire:loading.attr="disabled" wire:target="saveDraft" class="pos-btn pos-btn-secondary"><span wire:loading.remove wire:target="saveDraft">Simpan</span><span wire:loading wire:target="saveDraft">Menyimpan...</span></button>
                <button type="button" wire:click="pay" wire:loading.attr="disabled" wire:target="pay" class="pos-btn pos-btn-primary"><span wire:loading.remove wire:target="pay">Bayar</span><span wire:loading wire:target="pay">Memproses...</span></button>
                <button type="button" wire:click="printLastReceipt" class="pos-btn pos-btn-outline">Print Struk Terakhir</button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
