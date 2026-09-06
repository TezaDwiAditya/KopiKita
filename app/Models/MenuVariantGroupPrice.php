<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuVariantGroupPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'menu_variant_id',
        'customer_group_id',
        'selling_price',
    ];

    protected function casts(): array
    {
        return [
            'selling_price' => 'integer',
        ];
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(MenuVariant::class, 'menu_variant_id');
    }

    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }
}
