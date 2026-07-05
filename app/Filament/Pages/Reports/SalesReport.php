<?php

namespace App\Filament\Pages\Reports;

use App\Models\Transaction;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class SalesReport extends Page
{
    protected string $view = 'filament.pages.reports.sales-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Penjualan';

    protected static ?string $title = 'Laporan Penjualan';

    protected static ?int $navigationSort = 100;

    public string $startDate;

    public string $endDate;

    public ?int $cashierId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getCashiersProperty(): Collection
    {
        return User::query()->orderBy('name')->get();
    }

    public function getTransactionsProperty(): Collection
    {
        return $this->baseQuery()
            ->with(['cashier', 'customer', 'items'])
            ->latest('transaction_date')
            ->get();
    }

    public function getSummaryProperty(): array
    {
        $transactions = $this->transactions;

        $grossProfit = $transactions->sum(function (Transaction $transaction): int {
            return $transaction->items->sum(fn ($item): int => ((int) $item->price - (int) ($item->menu?->cost_price ?? 0)) * (int) $item->quantity);
        });

        return [
            'total_sales' => $transactions->sum('grand_total'),
            'total_subtotal' => $transactions->sum('subtotal'),
            'total_discount' => $transactions->sum('discount'),
            'total_tax' => $transactions->sum('tax'),
            'transaction_count' => $transactions->count(),
            'gross_profit' => $grossProfit,
        ];
    }

    public function getDailyRowsProperty(): Collection
    {
        return $this->transactions
            ->groupBy(fn (Transaction $transaction): string => $transaction->transaction_date->format('Y-m-d'))
            ->map(fn (Collection $transactions, string $date): array => [
                'date' => $date,
                'count' => $transactions->count(),
                'subtotal' => $transactions->sum('subtotal'),
                'discount' => $transactions->sum('discount'),
                'tax' => $transactions->sum('tax'),
                'grand_total' => $transactions->sum('grand_total'),
            ])
            ->values();
    }

    private function baseQuery()
    {
        return Transaction::query()
            ->where('status', 'paid')
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate)
            ->when($this->cashierId, fn ($query) => $query->where('cashier_id', $this->cashierId));
    }

    public function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
