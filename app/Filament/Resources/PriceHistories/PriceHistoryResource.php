<?php

namespace App\Filament\Resources\PriceHistories;

use App\Filament\Resources\PriceHistories\Pages\ListPriceHistories;
use App\Models\PriceHistory;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class PriceHistoryResource extends Resource
{
    protected static ?string $model = PriceHistory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static ?string $navigationLabel = 'Riwayat Harga';

    protected static ?string $modelLabel = 'Riwayat Harga';

    protected static ?string $pluralModelLabel = 'Riwayat Harga';

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?int $navigationSort = 130;

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('Tanggal')->dateTime('d M Y H:i')->sortable(),
                TextColumn::make('item_type')->label('Tipe')->badge()->sortable(),
                TextColumn::make('item_name')->label('Nama Item')->searchable()->sortable(),
                TextColumn::make('price_type')
                    ->label('Jenis Harga')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'selling_price' => 'Harga Jual',
                        'cost_price' => 'Harga Modal',
                        'price' => 'Harga Bahan',
                        default => $state,
                    })
                    ->sortable(),
                TextColumn::make('old_price')->label('Harga Lama')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable(),
                TextColumn::make('new_price')->label('Harga Baru')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable(),
                TextColumn::make('difference')
                    ->label('Selisih')
                    ->formatStateUsing(fn (int $state): string => ($state >= 0 ? '+' : '-').'Rp '.number_format(abs($state), 0, ',', '.'))
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('changed_by')->label('Diubah Oleh')->placeholder('-')->searchable(),
            ])
            ->filters([
                SelectFilter::make('item_type')->label('Tipe')->options([
                    'Produk' => 'Produk',
                    'Bahan Baku' => 'Bahan Baku',
                ]),
                SelectFilter::make('price_type')->label('Jenis Harga')->options([
                    'selling_price' => 'Harga Jual',
                    'cost_price' => 'Harga Modal',
                    'price' => 'Harga Bahan',
                ]),
            ])
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('user'));
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceHistories::route('/'),
        ];
    }
}
