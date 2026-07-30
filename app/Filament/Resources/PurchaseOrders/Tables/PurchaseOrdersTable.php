<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Throwable;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withCount('items'))
            ->defaultSort('order_date', 'desc')
            ->columns([
                TextColumn::make('po_number')->label('No PO')->searchable()->sortable()->copyable(),
                TextColumn::make('order_date')->label('Tanggal Order')->date('d M Y')->sortable(),
                TextColumn::make('expected_date')->label('Estimasi')->date('d M Y')->sortable()->placeholder('-'),
                TextColumn::make('supplier_name')->label('Supplier')->searchable()->placeholder('-'),
                TextColumn::make('items_count')->label('Item')->numeric()->sortable(),
                TextColumn::make('grand_total')->label('Grand Total')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable()->weight('bold'),
                TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'received' => 'success',
                    'cancelled' => 'danger',
                    'ordered' => 'info',
                    default => 'warning',
                }),
                TextColumn::make('received_at')->label('Diterima')->dateTime('d M Y H:i')->sortable()->placeholder('-'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->label('Status')->options([
                    'draft' => 'Draft',
                    'ordered' => 'Ordered',
                    'received' => 'Received',
                    'cancelled' => 'Cancelled',
                ]),
                Filter::make('order_date')
                    ->label('Tanggal Order')
                    ->form([
                        DatePicker::make('date_from')
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        DatePicker::make('date_until')
                            ->label('Tanggal Selesai')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['date_from'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('order_date', '>=', $date))
                        ->when($data['date_until'] ?? null, fn (Builder $query, string $date): Builder => $query->whereDate('order_date', '<=', $date))),
            ])
            ->recordActions([
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
