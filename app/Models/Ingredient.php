<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'unit',
        'price',
        'minimum_stock',
        'current_stock',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'minimum_stock' => 'integer',
            'current_stock' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (Ingredient $ingredient): void {
            if (! $ingredient->wasChanged('price')) {
                return;
            }

            $oldPrice = (int) $ingredient->getOriginal('price');
            $newPrice = (int) $ingredient->price;

            if ($oldPrice === $newPrice) {
                return;
            }

            PriceHistory::query()->create([
                'priceable_type' => self::class,
                'priceable_id' => $ingredient->id,
                'item_type' => 'Bahan Baku',
                'item_name' => $ingredient->name,
                'price_type' => 'price',
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'difference' => $newPrice - $oldPrice,
                'user_id' => auth()->id(),
                'changed_by' => auth()->user()?->name,
            ]);
        });
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(IngredientStock::class);
    }

    public function recipeItems(): HasMany
    {
        return $this->hasMany(RecipeItem::class);
    }

    public function purchaseOrderItems(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
