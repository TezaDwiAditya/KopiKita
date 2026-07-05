<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditMenu extends EditRecord
{
    protected static string $resource = MenuResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $firstVariant = collect($data['variants'] ?? [])->sortBy('sort_order')->first();

        if ($firstVariant) {
            $data['selling_price'] = (int) ($firstVariant['selling_price'] ?? 0);
            $data['cost_price'] = (int) ($firstVariant['cost_price'] ?? 0);
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
