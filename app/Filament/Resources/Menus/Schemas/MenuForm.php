<?php

namespace App\Filament\Resources\Menus\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class MenuForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Menu')
                    ->columns(2)
                    ->schema([
                        Select::make('category_id')
                            ->label('Kategori')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label('Nama Menu')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create' ? $set('slug', Str::slug($state)) : null),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->required(),
                    ]),
                Section::make('Varian Ukuran')
                    ->schema([
                        Repeater::make('variants')
                            ->label('Varian')
                            ->relationship('variants')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Ukuran / Varian')
                                    ->placeholder('250 ml')
                                    ->required()
                                    ->maxLength(255),
                                MoneyInput::make('selling_price')
                                    ->label('Harga Jual')
                                    ->required()
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        $sellingPrice = self::moneyToInt($state);
                                        $costPrice = self::moneyToInt($get('cost_price'));

                                        $set('profit_amount', max(0, $sellingPrice - $costPrice));
                                    }),
                                MoneyInput::make('cost_price')
                                    ->label('Harga Modal')
                                    ->required()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        $sellingPrice = self::moneyToInt($get('selling_price'));
                                        $costPrice = self::moneyToInt($state);

                                        $set('profit_amount', max(0, $sellingPrice - $costPrice));
                                    }),
                                MoneyInput::make('profit_amount')
                                    ->label('Keuntungan / Item')
                                    ->required()
                                    ->minValue(0)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        $sellingPrice = self::moneyToInt($get('selling_price'));
                                        $profitAmount = self::moneyToInt($state);

                                        $set('cost_price', max(0, $sellingPrice - $profitAmount));
                                    }),
                                TextInput::make('sort_order')
                                    ->label('Urutan')
                                    ->integer()
                                    ->minValue(0)
                                    ->default(0),
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Varian')
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
                Section::make('Foto Menu')
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Foto')
                            ->image()
                            ->directory('menus')
                            ->imageEditor()
                            ->columnSpanFull(),
                    ]),
            ])->columns(1);
    }

    private static function moneyToInt(mixed $value): int
    {
        return (int) preg_replace('/\D/', '', (string) ($value ?? ''));
    }
}
