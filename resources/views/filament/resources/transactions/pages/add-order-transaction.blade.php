<x-filament-panels::page>
    <style>
        .pos-shell { display: grid; grid-template-columns: minmax(0, 1fr) 420px; gap: 24px; }
        .pos-card { background: rgb(255 255 255); border: 1px solid rgb(229 231 235); border-radius: 8px; box-shadow: 0 8px 24px rgb(15 23 42 / 8%); }
        .dark .pos-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .pos-toolbar { padding: 18px; }
        .pos-toolbar-head { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 16px; }
        .pos-title { font-size: 18px; font-weight: 700; color: rgb(17 24 39); }
        .dark .pos-title { color: white; }
        .pos-subtitle { margin-top: 3px; color: rgb(107 114 128); font-size: 13px; }
        .pos-input { width: 100%; border-radius: 8px; border: 1px solid rgb(209 213 219); background: white; color: rgb(17 24 39); padding: 10px 12px; font-size: 14px; outline: none; }
        .pos-input:focus { border-color: rgb(245 158 11); box-shadow: 0 0 0 3px rgb(245 158 11 / 16%); }
        .dark .pos-input { background: rgb(31 41 55); border-color: rgb(75 85 99); color: white; }
        .pos-search { max-width: 320px; }
        .pos-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
        .pos-tab { border: 0; border-radius: 999px; padding: 9px 14px; cursor: pointer; font-size: 13px; font-weight: 700; background: rgb(243 244 246); color: rgb(55 65 81); }
        .pos-tab:hover { background: rgb(229 231 235); }
        .pos-tab.active { background: rgb(217 119 6); color: white; }
        .dark .pos-tab { background: rgb(31 41 55); color: rgb(229 231 235); }
        .dark .pos-tab:hover { background: rgb(55 65 81); }
        .pos-menu-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 18px; }
        .pos-menu-card { overflow: hidden; border: 1px solid rgb(229 231 235); border-radius: 8px; background: white; box-shadow: 0 8px 20px rgb(15 23 42 / 7%); }
        .dark .pos-menu-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .pos-menu-image { height: 128px; display: flex; align-items: center; justify-content: center; background: rgb(255 251 235); }
        .pos-menu-image img { width: 100%; height: 100%; object-fit: cover; }
        .pos-menu-body { padding: 14px; }
        .pos-menu-name { color: rgb(17 24 39); font-weight: 800; line-height: 1.25; }
        .dark .pos-menu-name { color: white; }
        .pos-menu-category { color: rgb(107 114 128); font-size: 12px; margin-top: 3px; }
        .pos-variants { display: grid; gap: 7px; margin-top: 12px; }
        .pos-variant-btn { width: 100%; border: 1px solid rgb(245 158 11 / 35%); border-radius: 8px; background: rgb(255 251 235); color: rgb(120 53 15); padding: 8px 9px; cursor: pointer; display: flex; justify-content: space-between; gap: 8px; font-size: 12px; font-weight: 800; }
        .pos-variant-btn:hover { background: rgb(254 243 199); }
        .dark .pos-variant-btn { background: rgb(245 158 11 / 12%); border-color: rgb(245 158 11 / 25%); color: rgb(252 211 77); }
        .pos-cart { position: sticky; top: 24px; padding: 18px; }
        .pos-cart-head { display: flex; align-items: start; justify-content: space-between; gap: 16px; }
        .pos-link-danger { color: rgb(220 38 38); font-size: 13px; font-weight: 800; background: transparent; border: 0; cursor: pointer; }
        .pos-cart-items { margin-top: 16px; display: grid; gap: 12px; }
        .pos-cart-item { border: 1px solid rgb(229 231 235); border-radius: 8px; padding: 12px; }
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
        .pos-customs { margin-top: 12px; display: flex; flex-wrap: wrap; gap: 7px; }
        .pos-custom-btn { border: 1px solid rgb(209 213 219); border-radius: 999px; background: rgb(249 250 251); color: rgb(55 65 81); padding: 7px 10px; font-size: 12px; font-weight: 800; cursor: pointer; }
        .pos-custom-btn.active { background: rgb(31 77 53); border-color: rgb(31 77 53); color: white; }
        .dark .pos-custom-btn { background: rgb(31 41 55); border-color: rgb(75 85 99); color: rgb(229 231 235); }
        .dark .pos-custom-btn.active { background: rgb(22 101 52); border-color: rgb(22 101 52); color: white; }
        .pos-section { margin-top: 16px; padding-top: 16px; border-top: 1px solid rgb(229 231 235); display: grid; gap: 10px; }
        .dark .pos-section { border-color: rgb(55 65 81); }
        .pos-total-row { display: flex; justify-content: space-between; gap: 12px; color: rgb(55 65 81); font-size: 14px; }
        .pos-total-row .pos-input { width: 150px; text-align: right; }
        .dark .pos-total-row { color: rgb(229 231 235); }
        .pos-grand { color: rgb(217 119 6); font-size: 18px; font-weight: 900; }
        .pos-actions { display: grid; gap: 10px; margin-top: 16px; }
        .pos-btn { border: 0; border-radius: 8px; padding: 12px 14px; font-weight: 900; cursor: pointer; }
        .pos-btn:disabled { cursor: not-allowed; opacity: .55; }
        .pos-btn-primary { background: rgb(217 119 6); color: white; }
        .pos-btn-primary:hover { background: rgb(180 83 9); }
        .pos-btn-secondary { background: rgb(243 244 246); color: rgb(55 65 81); }
        .dark .pos-btn-secondary { background: rgb(31 41 55); color: rgb(229 231 235); }
        .pos-empty { border: 1px dashed rgb(209 213 219); border-radius: 8px; padding: 24px; text-align: center; color: rgb(107 114 128); }
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
                        <div class="pos-subtitle">{{ $this->record->invoice_number }} - tambah item ke pesanan berjalan.</div>
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

            <div class="pos-menu-grid">
                @forelse ($this->menus as $menu)
                    <div class="pos-menu-card">
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
                    <div class="pos-title">Keranjang Tambahan</div>
                    <div class="pos-subtitle">{{ count($cart) }} item menu</div>
                </div>
                <button type="button" wire:click="clearCart" class="pos-link-danger">Kosongkan</button>
            </div>

            <div class="pos-cart-items">
                @forelse ($cart as $cartKey => $item)
                    <div class="pos-cart-item">
                        <div class="pos-row">
                            <div>
                                <div class="pos-item-name">{{ $item['name'] }} @if ($item['variant_name'] ?? null) - {{ $item['variant_name'] }} @endif</div>
                                <div class="pos-muted">Rp {{ number_format($item['price'], 0, ',', '.') }}</div>
                            </div>
                            <div class="pos-item-actions">
                                <button type="button" wire:click="removeItem('{{ $cartKey }}')" class="pos-link-danger">Hapus</button>
                            </div>
                        </div>
                        <div class="pos-row" style="margin-top: 12px;">
                            <div class="pos-qty">
                                <button type="button" wire:click="decrementQty('{{ $cartKey }}')">-</button>
                                <span>{{ $item['qty'] }}</span>
                                <button type="button" wire:click="incrementQty('{{ $cartKey }}')">+</button>
                            </div>
                            <strong>Rp {{ number_format($item['price'] * $item['qty'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="pos-customs">
                            @foreach ($this->itemCustomOptions as $custom)
                                <button
                                    type="button"
                                    wire:click="toggleItemCustom('{{ $cartKey }}', '{{ $custom }}')"
                                    class="pos-custom-btn {{ $this->itemHasCustom($item, $custom) ? 'active' : '' }}"
                                >
                                    {{ $custom }}
                                </button>
                            @endforeach
                        </div>
                        <input type="text" wire:model.blur="cart.{{ $cartKey }}.note" placeholder="Custom / catatan item" class="pos-input" style="margin-top: 12px;" />
                    </div>
                @empty
                    <div class="pos-empty">Klik menu untuk menambahkan ke keranjang.</div>
                @endforelse
            </div>

            <div class="pos-section">
                <div class="pos-total-row"><span>Total transaksi saat ini</span><strong>Rp {{ number_format($this->currentGrandTotal, 0, ',', '.') }}</strong></div>
                <div class="pos-total-row"><span>Tambahan pesanan</span><strong>Rp {{ number_format($this->subtotal, 0, ',', '.') }}</strong></div>
                <div class="pos-total-row">
                    <span>Diskon tambahan</span>
                    <input type="number" min="0" wire:model.live.debounce.300ms="discount" class="pos-input" />
                </div>
                <div class="pos-total-row"><span><strong>Estimasi total</strong></span><span class="pos-grand">Rp {{ number_format($this->estimatedGrandTotal, 0, ',', '.') }}</span></div>
            </div>

            <div class="pos-actions">
                <button type="button" wire:click="saveOrder" wire:loading.attr="disabled" wire:target="saveOrder" class="pos-btn pos-btn-primary">
                    <span wire:loading.remove wire:target="saveOrder">Simpan Pesanan</span>
                    <span wire:loading wire:target="saveOrder">Menyimpan...</span>
                </button>
                <a href="{{ \App\Filament\Resources\Transactions\TransactionResource::getUrl('view', ['record' => $this->record]) }}" class="pos-btn pos-btn-secondary" style="text-align: center;">Kembali</a>
            </div>
        </div>
    </div>
</x-filament-panels::page>
