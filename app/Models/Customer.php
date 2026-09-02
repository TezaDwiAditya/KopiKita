<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'phone_number',
        'note',
    ];

    public function setPhoneNumberAttribute(?string $value): void
    {
        $phone = trim((string) $value);

        if ($phone === '') {
            $this->attributes['phone_number'] = null;

            return;
        }

        $phone = preg_replace('/[^\d+]/', '', $phone) ?: '';

        if (str_starts_with($phone, '+62')) {
            $phone = '62'.substr($phone, 3);
        } elseif (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        } elseif (str_starts_with($phone, '8')) {
            $phone = '62'.$phone;
        }

        $this->attributes['phone_number'] = $phone;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
