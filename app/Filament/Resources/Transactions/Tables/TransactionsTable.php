<?php

namespace App\Filament\Resources\Transactions\Tables;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Transaction;
use App\Services\TransactionService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use RuntimeException;
use Throwable;

class TransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('items'))
            ->defaultSort('transaction_date', 'desc')
            ->columns([
                TextColumn::make('invoice_number')->label('No Invoice')->searchable()->sortable()->copyable(),
                TextColumn::make('transaction_date')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('cashier.name')->label('Kasir')->searchable()->sortable(),
                TextColumn::make('customer.name')->label('Customer')->searchable()->placeholder('Walk-in'),
                TextColumn::make('items_count')->label('Item')->numeric()->sortable(),
                TextColumn::make('subtotal')->label('Subtotal')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable()->toggleable(),
                TextColumn::make('discount')->label('Diskon')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable()->toggleable(),
                TextColumn::make('tax')->label('Pajak')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable()->toggleable(),
                TextColumn::make('grand_total')->label('Grand Total')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable()->weight('bold'),
                TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) { 'paid' => 'success', 'void' => 'danger', default => 'warning' }),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options(['draft' => 'Draft', 'paid' => 'Paid', 'void' => 'Void']),
                SelectFilter::make('cashier_id')->label('Kasir')->relationship('cashier', 'name')->searchable()->preload(),
                SelectFilter::make('customer_id')->label('Customer')->relationship('customer', 'name')->searchable()->preload(),
                Filter::make('today')->label('Hari Ini')->query(fn (Builder $query): Builder => $query->whereDate('transaction_date', today())),
            ])
            ->recordActions([
                Action::make('pay')
                    ->label('Bayar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Transaction $record): bool => $record->status === 'draft')
                    ->modalSubmitActionLabel('Bayar')
                    ->form([
                        Select::make('method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'qris' => 'QRIS',
                                'transfer' => 'Transfer',
                                'debit' => 'Debit',
                            ])
                            ->default('cash')
                            ->required(),
                        MoneyInput::make('amount_paid')
                            ->label('Uang Bayar')
                            ->required()
                            ->minValue(0),
                    ])
                    ->action(function (Transaction $record, array $data): void {
                        try {
                            app(TransactionService::class)->pay($record, $data['method'], (int) $data['amount_paid']);

                            Notification::make()
                                ->title('Transaksi berhasil dibayar')
                                ->success()
                                ->send();
                        } catch (RuntimeException $exception) {
                            Notification::make()
                                ->title('Pembayaran gagal')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                Action::make('print_receipt')
                    ->label('Print Struk')
                    ->icon('heroicon-o-printer')
                    ->color('gray')
                    ->url(fn (Transaction $record): string => route('admin.transactions.receipt', $record))
                    ->openUrlInNewTab(),
                Action::make('void')
                    ->label('Void')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (Transaction $record): bool => $record->status !== 'void')
                    ->requiresConfirmation()
                    ->modalHeading('Void transaksi')
                    ->modalDescription('Transaksi akan dibatalkan. Jika sudah dibayar, stock bahan akan dikembalikan.')
                    ->action(function (Transaction $record): void {
                        try {
                            app(TransactionService::class)->void($record);

                            Notification::make()
                                ->title('Transaksi berhasil di-void')
                                ->success()
                                ->send();
                        } catch (Throwable $exception) {
                            Notification::make()
                                ->title('Void gagal')
                                ->body($exception->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
