<?php

namespace App\Filament\Resources\Recipes\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Resep')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_id')
                            ->label('Menu')
                            ->relationship('menu', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Resep')
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ]),
                Section::make('Bahan Resep')
                    ->schema([
                        Repeater::make('items')
                            ->label('Bahan')
                            ->relationship('items')
                            ->schema([
                                Select::make('ingredient_id')
                                    ->label('Bahan Baku')
                                    ->relationship('ingredient', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('quantity')
                                    ->label('Jumlah Pakai')
                                    ->required()
                                    ->integer()
                                    ->minValue(1),
                            ])
                            ->columns(2)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Bahan')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
