<?php

namespace App\Filament\Resources\Ingredients\Tables;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class IngredientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama Bahan')->searchable()->sortable(),
                TextColumn::make('unit')->label('Satuan')->badge()->searchable(),
                TextColumn::make('price')->label('Harga')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable(),
                TextColumn::make('minimum_stock')->label('Minimal Stock')->numeric()->sortable(),
                TextColumn::make('current_stock')->label('Stock Saat Ini')->numeric()->sortable()->color(fn ($record): string => $record->current_stock <= $record->minimum_stock ? 'danger' : 'success'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('unit')->label('Satuan')->options(['gr' => 'Gram', 'ml' => 'Mililiter', 'pcs' => 'Pieces']),
                SelectFilter::make('stock_status')
                    ->label('Status Stock')
                    ->options(['low' => 'Hampir Habis', 'safe' => 'Aman'])
                    ->query(fn (Builder $query, array $data): Builder => match ($data['value'] ?? null) {
                        'low' => $query->whereColumn('current_stock', '<=', 'minimum_stock'),
                        'safe' => $query->whereColumn('current_stock', '>', 'minimum_stock'),
                        default => $query,
                    }),
            ])
            ->recordActions([
                Action::make('change_price')
                    ->label('Ubah Harga')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->modalHeading(fn ($record): string => 'Ubah Harga '.$record->name)
                    ->fillForm(fn ($record): array => [
                        'price' => $record->price,
                    ])
                    ->form([
                        MoneyInput::make('price')
                            ->label('Harga per Satuan Baru')
                            ->required()
                            ->minValue(0),
                    ])
                    ->action(function ($record, array $data): void {
                        $record->update([
                            'price' => (int) preg_replace('/\D/', '', (string) ($data['price'] ?? 0)),
                        ]);

                        Notification::make()
                            ->title('Harga bahan baku berhasil diperbarui')
                            ->success()
                            ->send();
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
