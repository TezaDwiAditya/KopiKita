<?php

namespace App\Filament\Widgets;

use App\Models\TransactionItem;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class BestSellingProductsTable extends TableWidget
{
    protected static ?string $heading = 'Produk Terlaris Bulan Ini';

    protected int|string|array $columnSpan = [
        'default' => 'full',
        'md' => 1,
    ];

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => $this->bestSellingProductsQuery())
            ->columns([
                TextColumn::make('menu_name')
                    ->label('Menu')
                    ->searchable(),
                TextColumn::make('variant_display')
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
            ->emptyStateHeading('Belum ada produk terjual bulan ini')
            ->emptyStateDescription('Data muncul setelah transaksi berstatus paid memiliki item penjualan.')
            ->defaultSort('total_qty', 'desc')
            ->paginated(false);
    }

    private function bestSellingProductsQuery(): Builder
    {
        $variantExpression = "COALESCE(NULLIF(transaction_items.variant_name, ''), 'Regular')";

        $subQuery = DB::table('transaction_items')
            ->join('transactions', 'transactions.id', '=', 'transaction_items.transaction_id')
            ->selectRaw('MIN(transaction_items.id) as id')
            ->selectRaw('transaction_items.menu_name')
            ->selectRaw("{$variantExpression} as variant_display")
            ->selectRaw('SUM(transaction_items.quantity) as total_qty')
            ->selectRaw('SUM(transaction_items.subtotal) as total_sales')
            ->where('transactions.status', 'paid')
            ->whereBetween('transactions.transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->groupBy('transaction_items.menu_name')
            ->groupByRaw($variantExpression);

        return TransactionItem::query()
            ->fromSub($subQuery, 'transaction_items')
            ->select([
                'transaction_items.id',
                'transaction_items.menu_name',
                'transaction_items.variant_display',
                'transaction_items.total_qty',
                'transaction_items.total_sales',
            ])
            ->limit(10);
    }
}
