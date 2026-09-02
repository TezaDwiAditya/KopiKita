<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\IngredientStock;
use App\Models\Menu;
use App\Models\Payment;
use App\Models\PurchaseOrder;
use App\Models\Recipe;
use App\Models\RecipeItem;
use App\Models\Setting;
use App\Models\Transaction;
use App\Policies\CategoryPolicy;
use App\Policies\CustomerPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\IngredientStockPolicy;
use App\Policies\MenuPolicy;
use App\Policies\PaymentPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\RecipeItemPolicy;
use App\Policies\RecipePolicy;
use App\Policies\SettingPolicy;
use App\Policies\TransactionPolicy;
use App\Services\WhatsAppService;
use App\Services\WhatsAppWebService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(WhatsAppService::class, WhatsAppWebService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Menu::class, MenuPolicy::class);
        Gate::policy(Ingredient::class, IngredientPolicy::class);
        Gate::policy(IngredientStock::class, IngredientStockPolicy::class);
        Gate::policy(Recipe::class, RecipePolicy::class);
        Gate::policy(RecipeItem::class, RecipeItemPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Transaction::class, TransactionPolicy::class);
        Gate::policy(Payment::class, PaymentPolicy::class);
        Gate::policy(PurchaseOrder::class, PurchaseOrderPolicy::class);
        Gate::policy(Setting::class, SettingPolicy::class);
    }
}
