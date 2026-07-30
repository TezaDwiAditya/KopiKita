<x-filament-panels::page>
    <style>
        .kitchen-shell { display: grid; gap: 16px; }
        .kitchen-toolbar { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
        .kitchen-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .kitchen-stat, .kitchen-card { background: white; border: 1px solid rgb(229 231 235); border-radius: 14px; box-shadow: 0 8px 22px rgb(15 23 42 / 6%); }
        .dark .kitchen-stat, .dark .kitchen-card { background: rgb(17 24 39); border-color: rgb(55 65 81); }
        .kitchen-stat { padding: 14px; }
        .stat-label { color: rgb(107 114 128); font-size: 12px; font-weight: 800; text-transform: uppercase; }
        .stat-value { margin-top: 4px; color: rgb(17 24 39); font-size: 26px; font-weight: 900; }
        .dark .stat-value { color: white; }
        .kitchen-tabs { display: flex; flex-wrap: wrap; gap: 8px; }
        .kitchen-tab { border: 0; border-radius: 999px; padding: 9px 14px; cursor: pointer; font-size: 13px; font-weight: 800; background: rgb(243 244 246); color: rgb(55 65 81); }
        .kitchen-tab.active { background: rgb(217 119 6); color: white; }
        .dark .kitchen-tab { background: rgb(31 41 55); color: rgb(229 231 235); }
        .kitchen-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .kitchen-card { padding: 14px; display: grid; gap: 12px; }
        .kitchen-card-head { display: flex; justify-content: space-between; gap: 12px; align-items: start; }
        .invoice { font-size: 13px; color: rgb(107 114 128); font-weight: 800; }
        .menu-name { margin-top: 4px; color: rgb(17 24 39); font-size: 18px; font-weight: 900; line-height: 1.25; }
        .dark .menu-name { color: white; }
        .item-meta { color: rgb(107 114 128); font-size: 13px; line-height: 1.5; }
        .qty-pill { min-width: 54px; text-align: center; border-radius: 12px; background: rgb(255 251 235); color: rgb(180 83 9); padding: 8px 10px; font-size: 18px; font-weight: 900; }
        .status-badge { width: fit-content; border-radius: 999px; padding: 6px 10px; font-size: 12px; font-weight: 900; }
        .status-pending { background: rgb(254 243 199); color: rgb(146 64 14); }
        .status-preparing { background: rgb(219 234 254); color: rgb(30 64 175); }
        .status-ready { background: rgb(220 252 231); color: rgb(22 101 52); }
        .status-served { background: rgb(243 244 246); color: rgb(75 85 99); }
        .note-box { border-left: 3px solid rgb(217 119 6); padding-left: 10px; color: rgb(55 65 81); font-size: 13px; }
        .dark .note-box { color: rgb(229 231 235); }
        .kitchen-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .kitchen-btn { border: 0; border-radius: 11px; padding: 10px 12px; cursor: pointer; font-size: 13px; font-weight: 900; }
        .btn-primary { background: rgb(217 119 6); color: white; }
        .btn-success { background: rgb(22 163 74); color: white; }
        .btn-secondary { background: rgb(243 244 246); color: rgb(55 65 81); }
        .dark .btn-secondary { background: rgb(31 41 55); color: rgb(229 231 235); }
        .empty-state { background: white; border: 1px dashed rgb(209 213 219); border-radius: 14px; padding: 32px; text-align: center; color: rgb(107 114 128); }
        .dark .empty-state { background: rgb(17 24 39); border-color: rgb(75 85 99); }
        @media (max-width: 1100px) { .kitchen-grid, .kitchen-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        @media (max-width: 680px) { .kitchen-grid, .kitchen-stats { grid-template-columns: 1fr; } }
    </style>

    <div class="kitchen-shell" wire:poll.10s>
        <div class="kitchen-toolbar">
            <div class="kitchen-tabs">
                <button type="button" wire:click="setStatusFilter('active')" class="kitchen-tab {{ $statusFilter === 'active' ? 'active' : '' }}">Aktif</button>
                <button type="button" wire:click="setStatusFilter('pending')" class="kitchen-tab {{ $statusFilter === 'pending' ? 'active' : '' }}">Baru</button>
                <button type="button" wire:click="setStatusFilter('preparing')" class="kitchen-tab {{ $statusFilter === 'preparing' ? 'active' : '' }}">Diproses</button>
                <button type="button" wire:click="setStatusFilter('ready')" class="kitchen-tab {{ $statusFilter === 'ready' ? 'active' : '' }}">Siap</button>
                <button type="button" wire:click="setStatusFilter('served')" class="kitchen-tab {{ $statusFilter === 'served' ? 'active' : '' }}">Disajikan</button>
            </div>
        </div>

        <div class="kitchen-stats">
            <div class="kitchen-stat"><div class="stat-label">Aktif</div><div class="stat-value">{{ $this->activeCount }}</div></div>
            <div class="kitchen-stat"><div class="stat-label">Baru</div><div class="stat-value">{{ $this->pendingCount }}</div></div>
            <div class="kitchen-stat"><div class="stat-label">Diproses</div><div class="stat-value">{{ $this->preparingCount }}</div></div>
            <div class="kitchen-stat"><div class="stat-label">Siap</div><div class="stat-value">{{ $this->readyCount }}</div></div>
        </div>

        <div class="kitchen-grid">
            @forelse ($this->kitchenItems as $item)
                <div class="kitchen-card" wire:key="kitchen-item-{{ $item->id }}">
                    <div class="kitchen-card-head">
                        <div>
                            <div class="invoice">{{ $item->transaction->invoice_number }}</div>
                            <div class="menu-name">{{ $item->menu_name }} @if ($item->variant_name) - {{ $item->variant_name }} @endif</div>
                        </div>
                        <div class="qty-pill">x{{ $item->quantity }}</div>
                    </div>

                    <div class="item-meta">
                        <div>{{ $item->transaction->customer?->name ?: 'Walk-in' }}</div>
                        <div>{{ $item->transaction->transaction_date->format('d M Y H:i') }}</div>
                    </div>

                    <div class="status-badge {{ $this->statusClass($item->kitchen_status) }}">{{ $this->statusLabel($item->kitchen_status) }}</div>

                    @if ($item->note)
                        <div class="note-box">{{ $item->note }}</div>
                    @endif

                    <div class="kitchen-actions">
                        @if ($item->kitchen_status === 'pending')
                            <button type="button" wire:click="markPreparing({{ $item->id }})" class="kitchen-btn btn-primary">Mulai Buat</button>
                            <button type="button" wire:click="markReady({{ $item->id }})" class="kitchen-btn btn-success">Langsung Siap</button>
                        @elseif ($item->kitchen_status === 'preparing')
                            <button type="button" wire:click="markReady({{ $item->id }})" class="kitchen-btn btn-success">Siap</button>
                            <button type="button" wire:click="markServed({{ $item->id }})" class="kitchen-btn btn-secondary">Disajikan</button>
                        @elseif ($item->kitchen_status === 'ready')
                            <button type="button" wire:click="markServed({{ $item->id }})" class="kitchen-btn btn-success" style="grid-column: span 2 / span 2;">Disajikan</button>
                        @else
                            <button type="button" disabled class="kitchen-btn btn-secondary" style="grid-column: span 2 / span 2;">Selesai</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="empty-state" style="grid-column: 1 / -1;">Belum ada pesanan pada filter ini.</div>
            @endforelse
        </div>
    </div>
</x-filament-panels::page>
