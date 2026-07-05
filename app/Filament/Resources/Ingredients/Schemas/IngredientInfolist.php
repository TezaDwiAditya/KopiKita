<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IngredientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Bahan Baku')->columns(2)->schema([
                TextEntry::make('name')->label('Nama Bahan'),
                TextEntry::make('unit')->label('Satuan')->badge()->color('gray'),
                TextEntry::make('price')->label('Harga per Satuan')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('minimum_stock')->label('Minimal Stock')->numeric(),
                TextEntry::make('current_stock')->label('Stock Saat Ini')->numeric()->badge()->color(fn ($record): string => $record->current_stock <= $record->minimum_stock ? 'danger' : 'success'),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}