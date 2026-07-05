<?php

namespace App\Filament\Resources\Categories\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Kategori')->columns(2)->schema([
                TextEntry::make('name')->label('Nama Kategori'),
                TextEntry::make('slug')->label('Slug')->badge()->color('gray'),
                IconEntry::make('is_active')->label('Status Aktif')->boolean(),
                TextEntry::make('menus_count')->label('Jumlah Menu')->state(fn ($record): int => $record->menus()->count())->badge()->color('primary'),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}