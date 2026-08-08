<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_id',
        'name',
        'selling_price',
        'cost_price',
        'profit_amount',
        'recipe_multiplier',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'integer',
            'cost_price' => 'integer',
            'profit_amount' => 'integer',
            'recipe_multiplier' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (MenuVariant $variant): void {
            foreach (['selling_price', 'cost_price'] as $priceType) {
                if (! $variant->wasChanged($priceType)) {
                    continue;
                }

                $oldPrice = (int) $variant->getOriginal($priceType);
                $newPrice = (int) $variant->{$priceType};

                if ($oldPrice === $newPrice) {
                    continue;
                }

                PriceHistory::query()->create([
                    'priceable_type' => self::class,
                    'priceable_id' => $variant->id,
                    'item_type' => 'Produk',
                    'item_name' => trim(($variant->menu?->name ?? 'Menu').' - '.$variant->name),
                    'price_type' => $priceType,
                    'old_price' => $oldPrice,
                    'new_price' => $newPrice,
                    'difference' => $newPrice - $oldPrice,
                    'user_id' => auth()->id(),
                    'changed_by' => auth()->user()?->name,
                ]);
            }
        });
    }

    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    public function transactionItems(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }
}
