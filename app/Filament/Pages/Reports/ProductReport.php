<?php

namespace App\Filament\Pages\Reports;

use App\Models\Menu;
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

    public function getPriceRowsProperty(): Collection
    {
        return Menu::query()
            ->with(['category', 'variants'])
            ->orderBy('name')
            ->get()
            ->flatMap(function (Menu $menu): Collection {
                if ($menu->variants->isEmpty()) {
                    return collect([$this->priceRow(
                        $menu->category?->name ?? '-',
                        $menu->name,
                        'Regular',
                        (int) $menu->selling_price,
                        (int) $menu->cost_price,
                        (bool) $menu->is_active,
                    )]);
                }

                return $menu->variants->map(fn ($variant): array => $this->priceRow(
                    $menu->category?->name ?? '-',
                    $menu->name,
                    $variant->name,
                    (int) $variant->selling_price,
                    (int) $variant->cost_price,
                    (bool) $variant->is_active && (bool) $menu->is_active,
                ));
            })
            ->sortBy([
                ['menu', 'asc'],
                ['variant', 'asc'],
            ])
            ->values();
    }

    public function getPriceSummaryProperty(): array
    {
        $rows = $this->priceRows;
        $activeRows = $rows->where('is_active', true);

        return [
            'products' => $rows->count(),
            'active_products' => $activeRows->count(),
            'average_margin' => round((float) $activeRows->avg('margin_percent'), 1),
            'low_margin' => $activeRows->where('margin_percent', '<', 30)->count(),
            'no_profit' => $activeRows->where('profit', '<=', 0)->count(),
        ];
    }

    private function priceRow(string $category, string $menu, string $variant, int $sellingPrice, int $costPrice, bool $isActive): array
    {
        $profit = $sellingPrice - $costPrice;
        $marginPercent = $sellingPrice > 0 ? round(($profit / $sellingPrice) * 100, 1) : 0.0;
        $markupPercent = $costPrice > 0 ? round(($profit / $costPrice) * 100, 1) : 0.0;

        return [
            'category' => $category,
            'menu' => $menu,
            'variant' => $variant,
            'selling_price' => $sellingPrice,
            'cost_price' => $costPrice,
            'profit' => $profit,
            'margin_percent' => $marginPercent,
            'markup_percent' => $markupPercent,
            'status' => $this->priceStatus($profit, $marginPercent, $isActive),
            'is_active' => $isActive,
        ];
    }

    private function priceStatus(int $profit, float $marginPercent, bool $isActive): string
    {
        if (! $isActive) {
            return 'Nonaktif';
        }

        if ($profit <= 0) {
            return 'Rugi / Impas';
        }

        if ($marginPercent < 30) {
            return 'Margin Rendah';
        }

        return 'Sehat';
    }

    public function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
