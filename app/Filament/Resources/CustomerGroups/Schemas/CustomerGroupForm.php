<?php

namespace App\Filament\Resources\CustomerGroups\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerGroupForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Group')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Group')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->required(),
                        Textarea::make('description')
                            ->label('Keterangan')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
