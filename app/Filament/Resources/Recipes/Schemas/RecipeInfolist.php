<?php

namespace App\Filament\Resources\Recipes\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecipeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Resep')->columns(2)->schema([
                TextEntry::make('menu.name')->label('Menu')->badge()->color('primary'),
                TextEntry::make('name')->label('Nama Resep')->placeholder('-'),
                IconEntry::make('is_active')->label('Status Aktif')->boolean(),
                TextEntry::make('items_count')->label('Jumlah Bahan')->state(fn ($record): int => $record->items()->count())->badge()->color('primary'),
            ]),
            Section::make('Bahan Resep')->schema([
                RepeatableEntry::make('items')->label('Bahan')->schema([
                    TextEntry::make('ingredient.name')->label('Bahan Baku'),
                    TextEntry::make('quantity')->label('Jumlah Pakai')->formatStateUsing(fn ($state, $record): string => number_format((int) $state, 0, ',', '.').' '.$record->ingredient?->unit),
                ])->columns(2),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}