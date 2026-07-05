<?php

namespace App\Filament\Pages\Reports;

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

    protected static ?string $navigationLabel = 'Penggunaan Bahan';

    protected static ?string $title = 'Laporan Penggunaan Bahan';

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

    public function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
