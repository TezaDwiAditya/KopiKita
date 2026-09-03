<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditTransaction extends EditRecord
{
    protected static string $resource = TransactionResource::class;

    protected function afterSave(): void
    {
        if ($this->record->status === 'draft') {
            $this->record->recalculateTotals();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('add_order')
                ->label('Tambah Pesanan')
                ->icon('heroicon-o-plus-circle')
                ->color('primary')
                ->visible(fn (Transaction $record): bool => $record->status === 'draft')
                ->url(fn (Transaction $record): string => TransactionResource::getUrl('add-order', ['record' => $record])),
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
