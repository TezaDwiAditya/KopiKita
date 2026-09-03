<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number',
        'transaction_date',
        'cashier_id',
        'customer_id',
        'subtotal',
        'discount',
        'tax',
        'grand_total',
        'status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'transaction_date' => 'datetime',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'tax' => 'integer',
            'grand_total' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Transaction $transaction): void {
            $transaction->transaction_date ??= now();
            $transaction->cashier_id ??= auth()->id();
            $transaction->invoice_number ??= static::generateInvoiceNumber($transaction->transaction_date);
        });
    }

    public static function generateInvoiceNumber(mixed $transactionDate = null): string
    {
        $invoiceDate = $transactionDate ? Carbon::parse($transactionDate) : now();
        $prefix = 'INV-'.$invoiceDate->format('Ymd').'-';
        $lastInvoice = static::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice, -5)) + 1 : 1;

        return $prefix.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function stockMovements(): MorphMany
    {
        return $this->morphMany(IngredientStock::class, 'reference');
    }

    public function recalculateTotals(): void
    {
        $subtotal = (int) $this->items()->sum('subtotal');
        $discount = max(0, (int) $this->discount);
        $taxPercentage = Setting::query()->value('tax_percentage') ?? 0;
        $tax = (int) round(max(0, $subtotal - $discount) * $taxPercentage / 100);

        $this->forceFill([
            'subtotal' => $subtotal,
            'discount' => $discount,
            'tax' => $tax,
            'grand_total' => max(0, $subtotal - $discount + $tax),
        ])->save();
    }
}
