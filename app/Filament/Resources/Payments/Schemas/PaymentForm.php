<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Pembayaran')
                    ->columns(2)
                    ->schema([
                        Select::make('transaction_id')
                            ->label('No Invoice')
                            ->relationship('transaction', 'invoice_number')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('method')
                            ->label('Metode Pembayaran')
                            ->options([
                                'cash' => 'Cash',
                                'qris' => 'QRIS',
                                'transfer' => 'Transfer',
                                'debit' => 'Debit',
                            ])
                            ->required(),
                        MoneyInput::make('amount_paid')
                            ->label('Uang Bayar')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        MoneyInput::make('change_amount')
                            ->label('Kembalian')
                            ->required()
                            ->minValue(0)
                            ->default(0),
                        DateTimePicker::make('paid_at')
                            ->label('Waktu Bayar')
                            ->seconds(false),
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'failed' => 'Failed',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),
            ]);
    }
}
