<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('print_order')
                ->label('Print Pesanan')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn (Transaction $record): string => route('admin.transactions.order-print', $record))
                ->openUrlInNewTab(),
            Action::make('print_receipt')
                ->label('Print Struk')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Transaction $record): string => route('admin.transactions.receipt', $record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }
}
