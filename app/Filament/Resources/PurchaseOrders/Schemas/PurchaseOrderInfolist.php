<?php

namespace App\Filament\Resources\PurchaseOrders\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseOrderInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Purchase Order')->columns(3)->schema([
                TextEntry::make('po_number')->label('No PO')->badge()->color('primary')->copyable(),
                TextEntry::make('order_date')->label('Tanggal Order')->date('d M Y'),
                TextEntry::make('expected_date')->label('Estimasi Datang')->date('d M Y')->placeholder('-'),
                TextEntry::make('supplier_name')->label('Supplier')->placeholder('-'),
                TextEntry::make('creator.name')->label('Dibuat Oleh')->placeholder('-'),
                TextEntry::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) {
                    'received' => 'success',
                    'cancelled' => 'danger',
                    'ordered' => 'info',
                    default => 'warning',
                }),
                TextEntry::make('received_at')->label('Diterima')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('note')->label('Catatan')->placeholder('-')->columnSpanFull(),
            ]),
            Section::make('Item Bahan Baku')->schema([
                RepeatableEntry::make('items')->label('Item')->schema([
                    TextEntry::make('ingredient.name')->label('Bahan'),
                    TextEntry::make('ingredient.unit')->label('Satuan')->badge()->color('gray'),
                    TextEntry::make('quantity')->label('Qty')->numeric(),
                    TextEntry::make('unit_price')->label('Harga Satuan')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                    TextEntry::make('subtotal')->label('Subtotal')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                    TextEntry::make('note')->label('Catatan')->placeholder('-')->columnSpanFull(),
                ])->columns(5),
            ]),
            Section::make('Total')->columns(4)->schema([
                TextEntry::make('subtotal')->label('Subtotal')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('discount')->label('Diskon')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('shipping_cost')->label('Ongkir')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.')),
                TextEntry::make('grand_total')->label('Grand Total')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->weight('bold')->color('primary'),
            ]),
            Section::make('Audit')->columns(2)->collapsed()->schema([
                TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->placeholder('-'),
                TextEntry::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->placeholder('-'),
            ]),
        ]);
    }
}
