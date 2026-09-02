<?php

namespace App\Services;

class RupiahFormatter
{
    public function format(int|float|null $amount): string
    {
        return 'Rp'.number_format((int) ($amount ?? 0), 0, ',', '.');
    }
}
