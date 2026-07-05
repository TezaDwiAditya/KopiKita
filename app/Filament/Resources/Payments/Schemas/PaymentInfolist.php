<?php

namespace App\Filament\Resources\Payments\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Pembayaran')->columns(2)->schema([
                TextEntry::make('transaction.invoice_number')->label('No Invoice')->badge()->color('primary'),
                TextEntry::make('method')->label('Metode')->formatStateUsing(fn (string $state): string => strtoupper($state))->badge()->color('gray'),
                TextEntry::make('amount_paid')->label('Uang Bayar')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('change_amount')->label('Kembalian')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) { 'paid' => 'success', 'failed' => 'danger', default => 'warning' }),
                TextEntry::make('paid_at')->label('Waktu Bayar')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}