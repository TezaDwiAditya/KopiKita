<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Purchase Order')
                    ->columns(3)
                    ->schema([
                        TextInput::make('po_number')
                            ->label('No PO')
                            ->placeholder('Otomatis jika dikosongkan')
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        DatePicker::make('order_date')
                            ->label('Tanggal Order')
                            ->default(today())
                            ->native(false)
                            ->displayFormat('d/m/Y')
                            ->required(),
                        DatePicker::make('expected_date')
                            ->label('Estimasi Datang')
                            ->native(false)
                            ->displayFormat('d/m/Y'),
                        TextInput::make('supplier_name')
                            ->label('Supplier')
                            ->maxLength(255),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'ordered' => 'Ordered',
                                'received' => 'Received',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('draft')
                            ->required(),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Item Bahan Baku')
                    ->schema([
                        Repeater::make('items')
                            ->label('Item')
                            ->relationship('items')
                            ->schema([
                                Select::make('ingredient_id')
                                    ->label('Bahan Baku')
                                    ->relationship('ingredient', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required(),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->default(1),
                                MoneyInput::make('unit_price')
                                    ->label('Harga Satuan')
                                    ->required()
                                    ->minValue(0)
                                    ->default(0),
                                MoneyInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->default(0),
                                Textarea::make('note')
                                    ->label('Catatan')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->columnSpanFull(),
                    ]),
                Section::make('Total')
                    ->columns(4)
                    ->schema([
                        MoneyInput::make('subtotal')
                            ->label('Subtotal')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0),
                        MoneyInput::make('discount')
                            ->label('Diskon')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        MoneyInput::make('shipping_cost')
                            ->label('Ongkir')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        MoneyInput::make('grand_total')
                            ->label('Grand Total')
                            ->disabled()
                            ->dehydrated(false)
                            ->default(0),
                    ]),
            ]);
    }
}
