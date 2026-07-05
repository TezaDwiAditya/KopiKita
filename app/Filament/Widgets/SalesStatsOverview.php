<?php

namespace App\Filament\Widgets;

use App\Models\Ingredient;
use App\Models\Transaction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SalesStatsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $todaySales = Transaction::query()
            ->where('status', 'paid')
            ->whereDate('transaction_date', today())
            ->sum('grand_total');

        $monthSales = Transaction::query()
            ->where('status', 'paid')
            ->whereBetween('transaction_date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('grand_total');

        $todayTransactions = Transaction::query()
            ->where('status', 'paid')
            ->whereDate('transaction_date', today())
            ->count();

        $lowStockCount = Ingredient::query()
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->count();

        return [
            Stat::make('Penjualan Hari Ini', $this->rupiah($todaySales))
                ->description('Total transaksi paid hari ini')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),
            Stat::make('Pendapatan Bulan Ini', $this->rupiah($monthSales))
                ->description(now()->translatedFormat('F Y'))
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('primary'),
            Stat::make('Transaksi Hari Ini', number_format($todayTransactions, 0, ',', '.'))
                ->description('Jumlah transaksi paid')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),
            Stat::make('Stock Hampir Habis', number_format($lowStockCount, 0, ',', '.'))
                ->description('Bahan di bawah minimal stock')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockCount > 0 ? 'danger' : 'success'),
        ];
    }

    private function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
