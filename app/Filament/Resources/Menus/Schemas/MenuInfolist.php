<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Menu')->columns(3)->schema([
                ImageEntry::make('photo_path')->label('Foto')->square()->placeholder('-'),
                TextEntry::make('name')->label('Nama Menu'),
                TextEntry::make('category.name')->label('Kategori')->badge()->color('primary'),
                TextEntry::make('slug')->label('Slug')->badge()->color('gray'),
                IconEntry::make('is_active')->label('Status Aktif')->boolean(),
            ]),
            Section::make('Varian Ukuran & Harga')->schema([
                RepeatableEntry::make('variants')
                    ->label('Varian')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Ukuran / Varian')
                            ->badge()
                            ->color('primary'),
                        TextEntry::make('selling_price')
                            ->label('Harga Jual')
                            ->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                        TextEntry::make('cost_price')
                            ->label('Harga Modal')
                            ->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                        TextEntry::make('recipe_multiplier')
                            ->label('Kali Resep')
                            ->formatStateUsing(fn (int $state): string => 'x'.$state),
                        IconEntry::make('is_active')
                            ->label('Aktif')
                            ->boolean(),
                    ])
                    ->columns(5)
                    ->columnSpanFull(),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}
