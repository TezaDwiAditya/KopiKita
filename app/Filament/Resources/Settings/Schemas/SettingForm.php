<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Toko')
                    ->columns(2)
                    ->schema([
                        TextInput::make('store_name')
                            ->label('Nama Toko')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone_number')
                            ->label('No HP')
                            ->tel()
                            ->maxLength(30),
                        Textarea::make('address')
                            ->label('Alamat')
                            ->rows(3)
                            ->columnSpanFull(),
                        FileUpload::make('logo_path')
                            ->label('Logo')
                            ->image()
                            ->directory('settings')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
                Section::make('Struk & Pajak')
                    ->columns(2)
                    ->schema([
                        TextInput::make('tax_percentage')
                            ->label('Pajak (%)')
                            ->required()
                            ->integer()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default(0)
                            ->suffix('%'),
                        Textarea::make('receipt_footer')
                            ->label('Footer Struk')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
