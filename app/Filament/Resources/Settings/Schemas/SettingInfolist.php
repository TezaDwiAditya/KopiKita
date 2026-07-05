<?php

namespace App\Filament\Resources\Settings\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SettingInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Toko')->columns(3)->schema([
                ImageEntry::make('logo_path')->label('Logo')->square()->placeholder('-'),
                TextEntry::make('store_name')->label('Nama Toko'),
                TextEntry::make('phone_number')->label('No HP')->placeholder('-'),
                TextEntry::make('address')->label('Alamat')->placeholder('-')->columnSpanFull(),
            ]),
            Section::make('Struk & Pajak')->columns(2)->schema([
                TextEntry::make('tax_percentage')->label('Pajak')->suffix('%')->badge()->color('warning'),
                TextEntry::make('receipt_footer')->label('Footer Struk')->placeholder('-')->columnSpanFull(),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}