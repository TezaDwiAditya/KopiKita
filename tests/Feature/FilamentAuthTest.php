<?php

namespace Tests\Feature;

use App\Filament\Pages\Auth\Login;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Livewire\Livewire;
use Tests\TestCase;

class FilamentAuthTest extends TestCase
{
    use DatabaseTransactions;

    public function test_login_page_can_be_rendered(): void
    {
        $this->get(route('filament.admin.auth.login'))
            ->assertOk()
            ->assertSee('KopiKita')
            ->assertSee('Welcome Back');
    }

    public function test_user_can_log_in_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'barista@kopikita.test',
            'password' => Hash::make('secret-password'),
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => $user->email,
                'password' => 'secret-password',
                'remember' => true,
            ])
            ->call('authenticate')
            ->assertHasNoFormErrors();

        $this->assertAuthenticatedAs($user);
    }

    public function test_invalid_login_shows_authentication_error(): void
    {
        User::factory()->create([
            'email' => 'barista@kopikita.test',
            'password' => Hash::make('secret-password'),
        ]);

        Livewire::test(Login::class)
            ->fillForm([
                'email' => 'barista@kopikita.test',
                'password' => 'wrong-password',
            ])
            ->call('authenticate')
            ->assertHasFormErrors(['email']);

        $this->assertGuest();
    }

    public function test_password_reset_pages_are_available(): void
    {
        $user = User::factory()->create([
            'email' => 'barista@kopikita.test',
        ]);
        $token = Password::broker(Filament::getAuthPasswordBroker())->createToken($user);

        $this->get(route('filament.admin.auth.password-reset.request'))
            ->assertOk()
            ->assertSee('Email');

        $this->get(Filament::getResetPasswordUrl($token, $user))
            ->assertOk()
            ->assertSee('Password');
    }
}
