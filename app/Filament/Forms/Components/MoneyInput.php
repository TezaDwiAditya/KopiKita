<?php

namespace App\Filament\Forms\Components;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

class MoneyInput
{
    public static function make(string $name): TextInput
    {
        return TextInput::make($name)
            ->prefix('Rp')
            ->inputMode('numeric')
            ->mask(RawJs::make(<<<'JS'
                $input => {
                    const value = String($input ?? '').replace(/\D/g, '')

                    if (! value) {
                        return ''
                    }

                    return value.replace(/\B(?=(\d{3})+(?!\d))/g, '.')
                }
            JS))
            ->stripCharacters('.')
            ->dehydrateStateUsing(fn ($state): int => (int) str_replace('.', '', (string) $state))
            ->formatStateUsing(fn ($state): string => filled($state) ? number_format((int) str_replace('.', '', (string) $state), 0, ',', '.') : '')
            ->rule('integer');
    }
}
