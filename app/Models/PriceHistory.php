<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PriceHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'priceable_type',
        'priceable_id',
        'item_type',
        'item_name',
        'price_type',
        'old_price',
        'new_price',
        'difference',
        'user_id',
        'changed_by',
    ];

    protected function casts(): array
    {
        return [
            'old_price' => 'integer',
            'new_price' => 'integer',
            'difference' => 'integer',
        ];
    }

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
