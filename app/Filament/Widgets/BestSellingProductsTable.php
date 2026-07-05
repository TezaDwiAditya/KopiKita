<?php

namespace App\Filament\Widgets;

use App\Models\MenuVariant;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class BestSellingProductsTable extends TableWidget
{
    protected static ?string $heading = 'Produk Terlaris Bulan Ini';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => MenuVariant::query()
                ->with('menu')
                ->withSum([
                    'transactionItems as total_qty' => fn (Builder $query) => $query
                        ->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery
                            ->where('status', 'paid')
                            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])),
                ], 'quantity')
                ->withSum([
                    'transactionItems as total_sales' => fn (Builder $query) => $query
                        ->whereHas('transaction', fn (Builder $transactionQuery) => $transactionQuery
                            ->where('status', 'paid')
                            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])),
                ], 'subtotal')
                ->whereHas('transactionItems.transaction', fn (Builder $query) => $query
                    ->where('status', 'paid')
                    ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()]))
                ->orderByDesc('total_qty')
                ->limit(10))
            ->columns([
                TextColumn::make('menu.name')
                    ->label('Menu')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Varian')
                    ->badge()
                    ->color('primary'),
                TextColumn::make('total_qty')
                    ->label('Qty Terjual')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('total_sales')
                    ->label('Total Penjualan')
                    ->formatStateUsing(fn ($state): string => 'Rp '.number_format((int) $state, 0, ',', '.'))
                    ->sortable(),
            ])
            ->defaultSort('total_qty', 'desc')
            ->paginated(false);
    }
}
