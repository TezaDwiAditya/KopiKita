<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TransactionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'menu_id',
        'menu_variant_id',
        'menu_name',
        'variant_name',
        'quantity',
        'price',
        'subtotal',
        'note',
        'kitchen_status',
        'preparing_at',
        'ready_at',
        'served_at',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'price' => 'integer',
            'subtotal' => 'integer',
            'preparing_at' => 'datetime',
            'ready_at' => 'datetime',
            'served_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (TransactionItem $item): void {
            $shouldSyncMenu = ! $item->exists || $item->isDirty('menu_id') || $item->isDirty('menu_variant_id');
            $shouldSyncPrice = $shouldSyncMenu || (int) $item->price <= 0;

            if ($item->menu_variant_id && ($shouldSyncMenu || $shouldSyncPrice)) {
                $variant = MenuVariant::query()
                    ->with('menu')
                    ->find($item->menu_variant_id);

                if ($variant) {
                    $item->menu_id = $variant->menu_id;
                    $item->menu_name = $variant->menu->name;
                    $item->variant_name = $variant->name;

                    if ($shouldSyncPrice) {
                        $item->price = $variant->selling_price;
                    }
                }
            } elseif ($item->menu_id && ($shouldSyncMenu || $shouldSyncPrice)) {
                $menu = Menu::query()->find($item->menu_id);

                if ($menu) {
                    $item->menu_name = $menu->name;
                    $item->variant_name = null;

                    if ($shouldSyncPrice) {
                        $item->price = $menu->selling_price;
                    }
                }
            }

            $item->quantity = max(1, (int) $item->quantity);
            $item->subtotal = $item->quantity * (int) $item->price;
        });
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(MenuVariant::class, 'menu_variant_id');
    }
}
