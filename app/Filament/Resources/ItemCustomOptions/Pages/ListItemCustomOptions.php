<?php

namespace App\Filament\Resources\ItemCustomOptions\Pages;

use App\Filament\Resources\ItemCustomOptions\ItemCustomOptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListItemCustomOptions extends ListRecords
{
    protected static string $resource = ItemCustomOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
