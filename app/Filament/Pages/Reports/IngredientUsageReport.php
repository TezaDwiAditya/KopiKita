<?php

namespace App\Filament\Pages\Reports;

use App\Models\Ingredient;
use App\Models\IngredientStock;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class IngredientUsageReport extends Page
{
    protected string $view = 'filament.pages.reports.ingredient-usage-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Bahan Baku';

    protected static ?string $title = 'Laporan Bahan Baku';

    protected static ?int $navigationSort = 120;

    public string $startDate;

    public string $endDate;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getRowsProperty(): Collection
    {
        return IngredientStock::query()
            ->with('ingredient')
            ->whereIn('type', ['sale', 'void'])
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->get()
            ->groupBy('ingredient_id')
            ->map(function (Collection $movements): array {
                $ingredient = $movements->first()->ingredient;
                $used = abs($movements->where('type', 'sale')->sum('quantity'));
                $restored = $movements->where('type', 'void')->sum('quantity');
                $netUsed = $used - $restored;

                return [
                    'ingredient' => $ingredient?->name,
                    'unit' => $ingredient?->unit,
                    'used' => $used,
                    'restored' => $restored,
                    'net_used' => $netUsed,
                    'value' => $netUsed * (int) ($ingredient?->price ?? 0),
                ];
            })
            ->sortByDesc('net_used')
            ->values();
    }

    public function getSummaryProperty(): array
    {
        return [
            'ingredients' => $this->rows->count(),
            'value' => $this->rows->sum('value'),
        ];
    }

    public function getPriceRowsProperty(): Collection
    {
        return Ingredient::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Ingredient $ingredient): array => $this->priceRow($ingredient))
            ->values();
    }

    public function getPriceSummaryProperty(): array
    {
        $rows = $this->priceRows;

        return [
            'ingredients' => $rows->count(),
            'stock_value' => $rows->sum('stock_value'),
            'average_price' => round((float) $rows->avg('price'), 0),
            'low_stock' => $rows->where('is_low_stock', true)->count(),
            'missing_price' => $rows->where('price', '<=', 0)->count(),
        ];
    }

    private function priceRow(Ingredient $ingredient): array
    {
        $price = (int) $ingredient->price;
        $currentStock = (int) $ingredient->current_stock;
        $minimumStock = (int) $ingredient->minimum_stock;
        $stockValue = $price * $currentStock;
        $isLowStock = $minimumStock > 0 && $currentStock <= $minimumStock;

        return [
            'ingredient' => $ingredient->name,
            'unit' => $ingredient->unit,
            'price' => $price,
            'current_stock' => $currentStock,
            'minimum_stock' => $minimumStock,
            'stock_value' => $stockValue,
            'is_low_stock' => $isLowStock,
            'status' => $this->priceStatus($price, $isLowStock),
        ];
    }

    private function priceStatus(int $price, bool $isLowStock): string
    {
        if ($price <= 0) {
            return 'Harga Belum Diisi';
        }

        if ($isLowStock) {
            return 'Stok Rendah';
        }

        return 'Aman';
    }

    public function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
