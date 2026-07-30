<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('receive')
                ->label('Terima')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (PurchaseOrder $record): bool => ! in_array($record->status, ['received', 'cancelled'], true))
                ->requiresConfirmation()
                ->modalHeading('Terima purchase order')
                ->modalDescription('Stok bahan baku akan bertambah sesuai qty item PO.')
                ->action(function (PurchaseOrder $record): void {
                    try {
                        app(PurchaseOrderService::class)->receive($record);

                        Notification::make()
                            ->title('Purchase order berhasil diterima')
                            ->success()
                            ->send();
                    } catch (Throwable $exception) {
                        Notification::make()
                            ->title('Penerimaan gagal')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            EditAction::make(),
        ];
    }
}
