<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Customer')->columns(2)->schema([
                TextEntry::make('name')->label('Nama'),
                TextEntry::make('phone_number')->label('No HP')->placeholder('-'),
                TextEntry::make('transactions_count')->label('Jumlah Transaksi')->state(fn ($record): int => $record->transactions()->count())->badge()->color('primary'),
                TextEntry::make('note')->label('Catatan')->placeholder('-')->columnSpanFull(),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}