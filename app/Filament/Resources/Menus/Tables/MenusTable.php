<?php

namespace App\Filament\Resources\Menus\Tables;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\MenuVariant;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class MenusTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('variants'))
            ->columns([
                ImageColumn::make('photo_path')->label('Foto')->square(),
                TextColumn::make('category.name')->label('Kategori')->searchable()->sortable(),
                TextColumn::make('name')->label('Nama Menu')->searchable()->sortable(),
                TextColumn::make('variants_summary')
                    ->label('Varian & Harga')
                    ->state(function ($record): HtmlString {
                        $variants = $record->variants;

                        if ($variants->isEmpty()) {
                            return new HtmlString('<span style="color:#6b7280">Belum ada varian</span>');
                        }

                        $html = $variants
                            ->sortBy('sort_order')
                            ->map(function ($variant): string {
                                $status = $variant->is_active ? '' : ' <span style="color:#dc2626">(nonaktif)</span>';

                                return sprintf(
                                    '<div><strong>%s</strong> — Jual Rp %s <span style="color:#16a34a">(+Rp %s)</span>%s</div>',
                                    e($variant->name),
                                    number_format((int) $variant->selling_price, 0, ',', '.'),
                                    number_format((int) $variant->profit_amount, 0, ',', '.'),
                                    $status,
                                );
                            })
                            ->implode('');

                        return new HtmlString('<div style="line-height:1.5">'.$html.'</div>');
                    })
                    ->html()
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas('variants', fn (Builder $variantQuery) => $variantQuery->where('name', 'like', "%{$search}%")))
                    ->wrap(),
                IconColumn::make('is_active')->label('Aktif')->boolean()->sortable(),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category_id')->label('Kategori')->relationship('category', 'name')->searchable()->preload(),
                TernaryFilter::make('is_active')->label('Status Aktif'),
            ])
            ->recordActions([
                Action::make('change_prices')
                    ->label('Ubah Harga')
                    ->icon('heroicon-o-currency-dollar')
                    ->color('warning')
                    ->modalHeading(fn ($record): string => 'Ubah Harga '.$record->name)
                    ->fillForm(fn ($record): array => [
                        'variants' => $record->variants
                            ->sortBy('sort_order')
                            ->map(fn ($variant): array => [
                                'id' => $variant->id,
                                'name' => $variant->name,
                                'selling_price' => $variant->selling_price,
                                'cost_price' => $variant->cost_price,
                            ])
                            ->values()
                            ->all(),
                    ])
                    ->form([
                        Repeater::make('variants')
                            ->label('Varian Produk')
                            ->schema([
                                Hidden::make('id'),
                                TextInput::make('name')
                                    ->label('Varian')
                                    ->disabled()
                                    ->dehydrated(),
                                MoneyInput::make('selling_price')
                                    ->label('Harga Jual Baru')
                                    ->required()
                                    ->minValue(0),
                                MoneyInput::make('cost_price')
                                    ->label('Harga Modal Baru')
                                    ->required()
                                    ->minValue(0),
                            ])
                            ->columns(3)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ])
                    ->action(function ($record, array $data): void {
                        foreach (($data['variants'] ?? []) as $variantData) {
                            $variant = MenuVariant::query()
                                ->where('menu_id', $record->id)
                                ->find($variantData['id'] ?? null);

                            if (! $variant) {
                                continue;
                            }

                            $sellingPrice = (int) preg_replace('/\D/', '', (string) ($variantData['selling_price'] ?? 0));
                            $costPrice = (int) preg_replace('/\D/', '', (string) ($variantData['cost_price'] ?? 0));

                            $variant->update([
                                'selling_price' => $sellingPrice,
                                'cost_price' => $costPrice,
                                'profit_amount' => max(0, $sellingPrice - $costPrice),
                            ]);
                        }

                        $firstVariant = $record->variants()->orderBy('sort_order')->first();

                        if ($firstVariant) {
                            $record->update([
                                'selling_price' => (int) $firstVariant->selling_price,
                                'cost_price' => (int) $firstVariant->cost_price,
                            ]);
                        }

                        Notification::make()
                            ->title('Harga produk berhasil diperbarui')
                            ->success()
                            ->send();
                    }),
                Action::make('toggle_active')
                    ->label(fn ($record): string => $record->is_active ? 'Nonaktifkan' : 'Aktifkan')
                    ->icon(fn ($record): string => $record->is_active ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                    ->color(fn ($record): string => $record->is_active ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['is_active' => ! $record->is_active])),
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
