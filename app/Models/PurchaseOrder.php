<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'po_number',
        'order_date',
        'expected_date',
        'received_at',
        'created_by',
        'supplier_name',
        'subtotal',
        'discount',
        'shipping_cost',
        'grand_total',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'order_date' => 'date',
            'expected_date' => 'date',
            'received_at' => 'datetime',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'shipping_cost' => 'integer',
            'grand_total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (PurchaseOrder $purchaseOrder): void {
            $purchaseOrder->po_number ??= static::generatePoNumber();
            $purchaseOrder->created_by ??= auth()->id();
        });
    }

    public static function generatePoNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';
        $lastNumber = static::query()
            ->where('po_number', 'like', $prefix.'%')
            ->orderByDesc('po_number')
            ->value('po_number');

        $sequence = $lastNumber ? ((int) str($lastNumber)->afterLast('-')->toString()) + 1 : 1;

        return $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(IngredientStock::class, 'reference');
    }

    public function recalculateTotals(): void
    {
        $subtotal = (int) $this->items()->sum('subtotal');

        $this->forceFill([
            'subtotal' => $subtotal,
            'grand_total' => max(0, $subtotal - $this->discount + $this->shipping_cost),
        ])->save();
    }
}
