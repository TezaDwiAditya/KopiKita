<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class LowStockIngredientsTable extends TableWidget
{
    protected static ?string $heading = 'Stock Hampir Habis';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => Ingredient::query()
                ->whereColumn('current_stock', '<=', 'minimum_stock')
                ->orderBy('current_stock'))
            ->columns([
                TextColumn::make('name')
                    ->label('Bahan')
                    ->searchable(),
                TextColumn::make('current_stock')
                    ->label('Stock Saat Ini')
                    ->formatStateUsing(fn ($state, $record): string => number_format((int) $state, 0, ',', '.').' '.$record->unit)
                    ->badge()
                    ->color('danger')
                    ->sortable(),
                TextColumn::make('minimum_stock')
                    ->label('Minimal Stock')
                    ->formatStateUsing(fn ($state, $record): string => number_format((int) $state, 0, ',', '.').' '.$record->unit)
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Harga')
                    ->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))
                    ->toggleable(),
            ])
            ->emptyStateHeading('Semua stock aman')
            ->emptyStateDescription('Tidak ada bahan yang berada di bawah minimal stock.')
            ->paginated(false);
    }
}
