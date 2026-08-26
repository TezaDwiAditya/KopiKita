<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Reports\CustomerProductSalesReport;
use App\Filament\Pages\Reports\CustomerStatementReport;
use App\Filament\Pages\Reports\IngredientUsageReport;
use App\Filament\Pages\Reports\ProductReport;
use App\Filament\Pages\Reports\SalesReport;
use App\Filament\Widgets\BestSellingProductsTable;
use App\Filament\Widgets\LowStockIngredientsTable;
use App\Filament\Widgets\SalesChart;
use App\Filament\Widgets\SalesStatsOverview;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login(Login::class)
            ->brandName('KopiKita')
            ->brandLogo(new HtmlString('
                <span class="kopikita-brand-mark" aria-hidden="true">
                    <svg viewBox="0 0 32 32" role="img">
                        <path d="M8 11h15v6.5a7.5 7.5 0 0 1-15 0V11Z" />
                        <path d="M23 13h2.5a3 3 0 0 1 0 6H23" />
                        <path d="M11 7c0-1.5 1.4-1.8 1.4-3.2M16 7c0-1.5 1.4-1.8 1.4-3.2M21 7c0-1.5 1.4-1.8 1.4-3.2" />
                        <path d="M7 25h18" />
                    </svg>
                    <span>KopiKita</span>
                </span>
            '))
            ->brandLogoHeight('1.75rem')
            ->simplePageMaxContentWidth('64rem')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->passwordReset()
            ->colors([
                'primary' => Color::Green,
                'warning' => Color::Amber,
                'gray' => Color::Zinc,
            ])
            ->sidebarFullyCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
                SalesReport::class,
                ProductReport::class,
                IngredientUsageReport::class,
                CustomerStatementReport::class,
                CustomerProductSalesReport::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                SalesStatsOverview::class,
                SalesChart::class,
                BestSellingProductsTable::class,
                LowStockIngredientsTable::class,
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->maxcontentwidth('full');
    }
}
