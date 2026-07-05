<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_name',
        'address',
        'phone_number',
        'logo_path',
        'tax_percentage',
        'receipt_footer',
    ];

    protected function casts(): array
    {
        return [
            'tax_percentage' => 'integer',
        ];
    }
}
