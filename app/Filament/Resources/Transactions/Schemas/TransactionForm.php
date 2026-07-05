<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Transaksi')
                    ->columns(2)
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('No Invoice')
                            ->required()
                            ->maxLength(255),
                        DateTimePicker::make('transaction_date')
                            ->label('Tanggal')
                            ->seconds(false)
                            ->default(now())
                            ->required(),
                        Select::make('cashier_id')
                            ->label('Kasir')
                            ->relationship('cashier', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('customer_id')
                            ->label('Customer')
                            ->relationship('customer', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'paid' => 'Paid',
                                'void' => 'Void',
                            ])
                            ->default('draft')
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Item Transaksi')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item')
                            ->relationship('items')
                            ->schema([
                                Select::make('menu_id')
                                    ->label('Menu')
                                    ->relationship('menu', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('menu_name')
                                    ->label('Nama Menu')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->default(1),
                                MoneyInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->minValue(0),
                                MoneyInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->required()
                                    ->minValue(0),
                                Textarea::make('note')
                                    ->label('Catatan')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->columnSpanFull(),
                    ]),
                Section::make('Total')
                    ->columns(2)
                    ->schema([
                        MoneyInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        MoneyInput::make('discount')
                            ->label('Diskon')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        MoneyInput::make('tax')
                            ->label('Pajak')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        MoneyInput::make('grand_total')
                            ->label('Grand Total')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                    ]),
            ]);
    }
}
