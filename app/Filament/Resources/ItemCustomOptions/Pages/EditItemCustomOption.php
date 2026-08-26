<?php

namespace App\Filament\Resources\ItemCustomOptions\Pages;

use App\Filament\Resources\ItemCustomOptions\ItemCustomOptionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditItemCustomOption extends EditRecord
{
    protected static string $resource = ItemCustomOptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
