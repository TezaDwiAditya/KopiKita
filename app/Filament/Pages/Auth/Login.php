<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function getTitle(): string|Htmlable
    {
        return 'Login - KopiKita';
    }

    public function getHeading(): string|Htmlable|null
    {
        return 'Welcome Back';
    }
}
