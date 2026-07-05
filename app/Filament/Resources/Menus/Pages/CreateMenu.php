<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMenu extends CreateRecord
{
    protected static string $resource = MenuResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $firstVariant = collect($data['variants'] ?? [])->sortBy('sort_order')->first();

        $data['selling_price'] = (int) ($firstVariant['selling_price'] ?? 0);
        $data['cost_price'] = (int) ($firstVariant['cost_price'] ?? 0);

        return $data;
    }
}
