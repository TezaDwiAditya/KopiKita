<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class IngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Bahan Baku')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Bahan')
                            ->required()
                            ->maxLength(255),
                        Select::make('unit')
                            ->label('Satuan')
                            ->options([
                                'gr' => 'Gram (gr)',
                                'ml' => 'Mililiter (ml)',
                                'pcs' => 'Pieces (pcs)',
                            ])
                            ->searchable()
                            ->required(),
                        MoneyInput::make('price')
                            ->label('Harga per Satuan')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('minimum_stock')
                            ->label('Minimal Stock')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('current_stock')
                            ->label('Stock Saat Ini')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
