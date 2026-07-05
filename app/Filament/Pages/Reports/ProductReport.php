<?php

namespace App\Filament\Pages\Reports;

use App\Models\TransactionItem;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class ProductReport extends Page
{
    protected string $view = 'filament.pages.reports.product-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Produk';

    protected static ?string $title = 'Laporan Produk';

    protected static ?int $navigationSort = 110;

    public string $startDate;

    public string $endDate;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getRowsProperty(): Collection
    {
        return TransactionItem::query()
            ->with(['menu', 'variant', 'transaction'])
            ->whereHas('transaction', fn ($query) => $query
                ->where('status', 'paid')
                ->whereDate('transaction_date', '>=', $this->startDate)
                ->whereDate('transaction_date', '<=', $this->endDate))
            ->get()
            ->groupBy(fn (TransactionItem $item): string => $item->menu_name.'|'.($item->variant_name ?: 'Regular'))
            ->map(function (Collection $items): array {
                $first = $items->first();
                $qty = $items->sum('quantity');
                $sales = $items->sum('subtotal');
                $cost = $items->sum(fn (TransactionItem $item): int => (int) ($item->variant?->cost_price ?? $item->menu?->cost_price ?? 0) * (int) $item->quantity);

                return [
                    'menu' => $first->menu_name,
                    'variant' => $first->variant_name ?: 'Regular',
                    'qty' => $qty,
                    'sales' => $sales,
                    'cost' => $cost,
                    'gross_profit' => $sales - $cost,
                ];
            })
            ->sortByDesc('qty')
            ->values();
    }

    public function getSummaryProperty(): array
    {
        return [
            'qty' => $this->rows->sum('qty'),
            'sales' => $this->rows->sum('sales'),
            'cost' => $this->rows->sum('cost'),
            'gross_profit' => $this->rows->sum('gross_profit'),
        ];
    }

    public function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
