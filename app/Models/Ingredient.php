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
