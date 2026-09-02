<?php

namespace App\Filament\Resources\Transactions\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TransactionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Transaksi')->columns(3)->schema([
                TextEntry::make('invoice_number')->label('No Invoice')->badge()->color('primary')->copyable(),
                TextEntry::make('transaction_date')->label('Tanggal')->dateTime('d M Y H:i'),
                TextEntry::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'paid' => 'success', 'void' => 'danger', default => 'warning'
                }),
                TextEntry::make('cashier.name')->label('Kasir'),
                TextEntry::make('customer.name')->label('Customer')->placeholder('Walk-in'),
                TextEntry::make('note')->label('Catatan')->placeholder('-')->columnSpanFull(),
            ]),
            Section::make('Item Transaksi')->schema([
                RepeatableEntry::make('items')->label('Item')->schema([
                    TextEntry::make('menu_name')->label('Menu'),
                    TextEntry::make('variant_name')->label('Varian')->placeholder('-'),
                    TextEntry::make('quantity')->label('Qty')->numeric(),
                    TextEntry::make('price')->label('Harga')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                    TextEntry::make('subtotal')->label('Subtotal')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                    TextEntry::make('note')->label('Catatan')->placeholder('-')->columnSpanFull(),
                ])->columns(5),
            ]),
            Section::make('Total')->columns(4)->schema([
                TextEntry::make('subtotal')->label('Subtotal')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('discount')->label('Diskon')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('tax')->label('Pajak')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('grand_total')->label('Grand Total')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->weight('bold')->color('primary'),
            ]),
            Section::make('QRIS')->columns(2)->schema([
                ViewEntry::make('qris_preview')
                    ->label('Gambar QRIS')
                    ->view('filament.transactions.qris-preview'),
                TextEntry::make('payment.qris_reference')->label('Referensi QRIS')->placeholder('-'),
                TextEntry::make('payment.qris_amount')->label('Nominal QRIS')->formatStateUsing(fn (?int $state): string => 'Rp'.number_format((int) $state, 0, ',', '.'))->placeholder('-'),
                TextEntry::make('payment.qris_status')->label('Status QRIS')->badge()->color(fn (?string $state): string => match ($state) {
                    'paid' => 'success', 'expired', 'cancelled' => 'danger', default => 'warning'
                })->placeholder('-'),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}
