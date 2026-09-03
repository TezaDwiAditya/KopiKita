<?php

namespace App\Filament\Resources\Transactions\Schemas;

use App\Filament\Forms\Components\MoneyInput;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Setting;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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
                            ->placeholder('Otomatis jika dikosongkan')
                            ->unique(ignoreRecord: true)
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
                            ->default(auth()->id())
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
                                    ->options(fn (): array => Menu::query()
                                        ->where('is_active', true)
                                        ->orderBy('name')
                                        ->pluck('name', 'id')
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->required()
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        self::syncMenuItem($state, $get, $set);
                                        self::recalculateFormTotals($get, $set);
                                    }),
                                Select::make('menu_variant_id')
                                    ->label('Varian')
                                    ->options(fn (callable $get): array => MenuVariant::query()
                                        ->where('menu_id', $get('menu_id'))
                                        ->where('is_active', true)
                                        ->orderBy('sort_order')
                                        ->orderBy('name')
                                        ->get()
                                        ->mapWithKeys(fn (MenuVariant $variant): array => [
                                            $variant->id => $variant->name.' - Rp '.number_format($variant->selling_price, 0, ',', '.'),
                                        ])
                                        ->all())
                                    ->searchable()
                                    ->preload()
                                    ->live()
                                    ->visible(fn (callable $get): bool => filled($get('menu_id')) && MenuVariant::query()
                                        ->where('menu_id', $get('menu_id'))
                                        ->where('is_active', true)
                                        ->exists())
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        self::syncVariantItem($state, $set);
                                        self::recalculateItemSubtotal($get, $set);
                                        self::recalculateFormTotals($get, $set);
                                    }),
                                Hidden::make('menu_name'),
                                Hidden::make('variant_name'),
                                TextInput::make('quantity')
                                    ->label('Qty')
                                    ->required()
                                    ->integer()
                                    ->minValue(1)
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                        $set('quantity', max(1, (int) $state));
                                        self::recalculateItemSubtotal($get, $set);
                                        self::recalculateFormTotals($get, $set);
                                    }),
                                MoneyInput::make('price')
                                    ->label('Harga')
                                    ->required()
                                    ->minValue(0)
                                    ->readOnly()
                                    ->dehydrated(),
                                MoneyInput::make('subtotal')
                                    ->label('Subtotal')
                                    ->required()
                                    ->minValue(0)
                                    ->readOnly()
                                    ->dehydrated(),
                                Textarea::make('note')
                                    ->label('Catatan')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Item')
                            ->live()
                            ->afterStateUpdated(function (callable $get, callable $set): void {
                                self::recalculateFormTotals($get, $set, fromRoot: true);
                            })
                            ->columnSpanFull(),
                    ]),
                Section::make('Total')
                    ->columns(2)
                    ->schema([
                        MoneyInput::make('subtotal')
                            ->label('Subtotal')
                            ->required()
                            ->minValue(0)
                            ->readOnly()
                            ->default(0),
                        MoneyInput::make('discount')
                            ->label('Diskon')
                            ->required()
                            ->minValue(0)
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                                $set('discount', max(0, self::moneyToInt($state)));
                                self::recalculateFormTotals($get, $set, fromRoot: true);
                            }),
                        MoneyInput::make('tax')
                            ->label('Pajak')
                            ->required()
                            ->minValue(0)
                            ->readOnly()
                            ->default(0),
                        MoneyInput::make('grand_total')
                            ->label('Grand Total')
                            ->required()
                            ->minValue(0)
                            ->readOnly()
                            ->default(0),
                    ]),
            ]);
    }

    private static function syncMenuItem(mixed $menuId, callable $get, callable $set): void
    {
        $menu = Menu::query()
            ->with(['activeVariants' => fn ($query) => $query->orderBy('sort_order')->orderBy('name')])
            ->find($menuId);

        if (! $menu) {
            $set('menu_variant_id', null);
            $set('menu_name', null);
            $set('variant_name', null);
            $set('price', 0);
            $set('subtotal', 0);

            return;
        }

        $variant = $menu->activeVariants->first();

        $set('menu_name', $menu->name);
        $set('menu_variant_id', $variant?->id);
        $set('variant_name', $variant?->name);
        $set('price', $variant?->selling_price ?? $menu->selling_price);

        if (! $get('quantity')) {
            $set('quantity', 1);
        }

        self::recalculateItemSubtotal($get, $set);
    }

    private static function syncVariantItem(mixed $variantId, callable $set): void
    {
        $variant = MenuVariant::query()
            ->with('menu')
            ->find($variantId);

        if (! $variant) {
            $set('variant_name', null);

            return;
        }

        $set('menu_id', $variant->menu_id);
        $set('menu_name', $variant->menu->name);
        $set('variant_name', $variant->name);
        $set('price', $variant->selling_price);
    }

    private static function recalculateItemSubtotal(callable $get, callable $set): void
    {
        $set('subtotal', max(1, (int) $get('quantity')) * self::moneyToInt($get('price')));
    }

    private static function recalculateFormTotals(callable $get, callable $set, bool $fromRoot = false): void
    {
        $items = $fromRoot ? ($get('items') ?? []) : ($get('../../items') ?? []);
        $subtotal = collect($items)->sum(fn (array $item): int => self::moneyToInt($item['subtotal'] ?? 0));
        $discount = $fromRoot ? self::moneyToInt($get('discount')) : self::moneyToInt($get('../../discount'));
        $taxPercentage = Setting::query()->value('tax_percentage') ?? 0;
        $tax = (int) round(max(0, $subtotal - $discount) * $taxPercentage / 100);
        $grandTotal = max(0, $subtotal - $discount + $tax);

        if ($fromRoot) {
            $set('subtotal', $subtotal);
            $set('tax', $tax);
            $set('grand_total', $grandTotal);

            return;
        }

        $set('../../subtotal', $subtotal);
        $set('../../tax', $tax);
        $set('../../grand_total', $grandTotal);
    }

    private static function moneyToInt(mixed $value): int
    {
        return (int) preg_replace('/\D/', '', (string) ($value ?? ''));
    }
}
