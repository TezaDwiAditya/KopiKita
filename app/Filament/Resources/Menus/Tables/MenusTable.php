<?php

namespace App\Filament\Resources\Menus\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
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
