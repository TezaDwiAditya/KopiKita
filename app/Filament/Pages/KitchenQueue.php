<?php

namespace App\Filament\Pages;

use App\Models\TransactionItem;
use App\Services\KitchenOrderService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Throwable;
use UnitEnum;

class KitchenQueue extends Page
{
    protected string $view = 'filament.pages.kitchen-queue';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'POS';

    protected static ?string $navigationLabel = 'Antrian Dapur';

    protected static ?string $title = 'Antrian Dapur';

    protected static ?int $navigationSort = 2;

    public string $statusFilter = 'active';

    public function getKitchenItemsProperty(): Collection
    {
        return TransactionItem::query()
            ->with(['transaction.customer'])
            ->whereHas('transaction', fn ($query) => $query->whereIn('status', ['draft', 'paid']))
            ->when($this->statusFilter === 'active', fn ($query) => $query->whereIn('kitchen_status', ['pending', 'preparing', 'ready']))
            ->when($this->statusFilter !== 'active', fn ($query) => $query->where('kitchen_status', $this->statusFilter))
            ->orderByRaw("case kitchen_status when 'pending' then 1 when 'preparing' then 2 when 'ready' then 3 else 4 end")
            ->orderBy('created_at')
            ->get();
    }

    public function getPendingCountProperty(): int
    {
        return $this->countByStatus('pending');
    }

    public function getPreparingCountProperty(): int
    {
        return $this->countByStatus('preparing');
    }

    public function getReadyCountProperty(): int
    {
        return $this->countByStatus('ready');
    }

    public function getActiveCountProperty(): int
    {
        return $this->pendingCount + $this->preparingCount + $this->readyCount;
    }

    public function setStatusFilter(string $status): void
    {
        $this->statusFilter = $status;
    }

    public function markPreparing(int $itemId): void
    {
        $this->updateKitchenStatus($itemId, 'preparing');
    }

    public function markReady(int $itemId): void
    {
        $this->updateKitchenStatus($itemId, 'ready');
    }

    public function markServed(int $itemId): void
    {
        $this->updateKitchenStatus($itemId, 'served');
    }

    public function statusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Baru',
            'preparing' => 'Diproses',
            'ready' => 'Siap',
            'served' => 'Disajikan',
            default => ucfirst($status),
        };
    }

    public function statusClass(string $status): string
    {
        return match ($status) {
            'pending' => 'status-pending',
            'preparing' => 'status-preparing',
            'ready' => 'status-ready',
            'served' => 'status-served',
            default => 'status-pending',
        };
    }

    private function updateKitchenStatus(int $itemId, string $status): void
    {
        try {
            $item = TransactionItem::query()->findOrFail($itemId);
            $service = app(KitchenOrderService::class);

            match ($status) {
                'preparing' => $service->markPreparing($item),
                'ready' => $service->markReady($item),
                'served' => $service->markServed($item),
                default => null,
            };

            Notification::make()
                ->title('Status dapur diperbarui')
                ->success()
                ->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Gagal memperbarui status')
                ->body('Terjadi kesalahan saat memproses antrian dapur.')
                ->danger()
                ->send();
        }
    }

    private function countByStatus(string $status): int
    {
        return TransactionItem::query()
            ->where('kitchen_status', $status)
            ->whereHas('transaction', fn ($query) => $query->whereIn('status', ['draft', 'paid']))
            ->count();
    }
}
