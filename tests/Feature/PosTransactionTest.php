<?php

namespace Tests\Feature;

use App\Filament\Pages\Pos;
use App\Filament\Resources\Transactions\Pages\AddOrderTransaction;
use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Recipe;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class PosTransactionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_pay_transaction_creates_payment_and_reduces_ingredient_stock(): void
    {
        $user = User::factory()->create();
        $transaction = $this->createDraftTransaction($user);

        $this->actingAs($user);

        app(TransactionService::class)->pay($transaction, 'cash', 50000);

        $transaction->refresh()->load('payment');

        $this->assertSame('paid', $transaction->status);
        $this->assertNotNull($transaction->payment);
        $this->assertSame('cash', $transaction->payment->method);
        $this->assertSame(50000, $transaction->payment->amount_paid);
        $this->assertSame(20000, $transaction->payment->change_amount);
        $this->assertSame(64, Ingredient::query()->where('name', 'Test Espresso')->value('current_stock'));
        $this->assertSame(2, $transaction->stockMovements()->where('type', 'sale')->count());
    }

    public function test_void_paid_transaction_restores_ingredient_stock(): void
    {
        $user = User::factory()->create();
        $transaction = $this->createDraftTransaction($user);

        $this->actingAs($user);

        app(TransactionService::class)->pay($transaction, 'cash', 50000);
        app(TransactionService::class)->void($transaction);

        $transaction->refresh()->load('payment');

        $this->assertSame('void', $transaction->status);
        $this->assertSame('failed', $transaction->payment->status);
        $this->assertSame(100, Ingredient::query()->where('name', 'Test Espresso')->value('current_stock'));
        $this->assertSame(2, $transaction->stockMovements()->where('type', 'void')->count());
    }

    public function test_transaction_policy_only_allows_delete_for_draft_transaction(): void
    {
        $user = User::factory()->create();
        $draft = $this->createDraftTransaction($user, 'INV-TEST-DRAFT');
        $paid = $this->createDraftTransaction($user, 'INV-TEST-PAID');
        $paid->update(['status' => 'paid']);

        $this->assertTrue(Gate::forUser($user)->allows('delete', $draft));
        $this->assertFalse(Gate::forUser($user)->allows('delete', $paid));
    }

    public function test_receipt_route_can_render_thermal_receipt(): void
    {
        $user = User::factory()->create();
        $transaction = $this->createDraftTransaction($user);

        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Coffee Kita Test',
                'address' => 'Jl. Test',
                'phone_number' => '0800000000',
                'logo_path' => null,
                'tax_percentage' => 10,
                'receipt_footer' => 'Terima kasih test.',
            ],
        );

        $this->actingAs($user)
            ->get(route('admin.transactions.receipt', $transaction))
            ->assertOk()
            ->assertSee('Coffee Kita Test')
            ->assertSee($transaction->invoice_number)
            ->assertSee('Test Americano');
    }

    public function test_pos_save_draft_resets_selected_customer(): void
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'phone_number' => '0800000001',
        ]);
        $menu = $this->createMenu();

        $this->actingAs($user);

        Livewire::test(Pos::class)
            ->set('customerId', $customer->id)
            ->set('customerSearch', $customer->name)
            ->set('cart', [
                (string) $menu->id => [
                    'menu_id' => $menu->id,
                    'name' => $menu->name,
                    'price' => $menu->selling_price,
                    'qty' => 1,
                    'note' => '',
                ],
            ])
            ->call('saveDraft')
            ->assertSet('customerId', null)
            ->assertSet('customerSearch', '')
            ->assertSet('cart', []);
    }

    public function test_transaction_item_uses_selected_variant_and_recalculates_totals(): void
    {
        $user = User::factory()->create();
        $menu = $this->createMenu();
        $variant = MenuVariant::query()->create([
            'menu_id' => $menu->id,
            'name' => 'Large',
            'selling_price' => 22000,
            'cost_price' => 8000,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Coffee Kita Test',
                'tax_percentage' => 10,
            ],
        );

        $this->actingAs($user);

        $transaction = Transaction::query()->create([
            'transaction_date' => now(),
            'cashier_id' => $user->id,
            'discount' => 4000,
            'status' => 'draft',
        ]);

        $transaction->items()->create([
            'menu_id' => $menu->id,
            'menu_variant_id' => $variant->id,
            'quantity' => 2,
            'price' => 0,
            'subtotal' => 0,
        ]);

        $transaction->recalculateTotals();
        $transaction->refresh();

        $item = $transaction->items()->first();

        $this->assertNotNull($transaction->invoice_number);
        $this->assertSame($menu->name, $item->menu_name);
        $this->assertSame('Large', $item->variant_name);
        $this->assertSame(22000, $item->price);
        $this->assertSame(44000, $item->subtotal);
        $this->assertSame(44000, $transaction->subtotal);
        $this->assertSame(4000, $transaction->tax);
        $this->assertSame(44000, $transaction->grand_total);
    }

    public function test_add_order_page_adds_pos_cart_items_to_draft_transaction(): void
    {
        $user = User::factory()->create();
        $transaction = $this->createDraftTransaction($user);
        $menu = $this->createMenu();

        Setting::query()->updateOrCreate(
            ['id' => 1],
            [
                'store_name' => 'Coffee Kita Test',
                'tax_percentage' => 10,
            ],
        );

        $this->actingAs($user);

        Livewire::test(AddOrderTransaction::class, ['record' => $transaction->getRouteKey()])
            ->call('addToCart', $menu->id)
            ->call('incrementQty', (string) $menu->id)
            ->set('discount', 5000)
            ->call('saveOrder')
            ->assertRedirect(TransactionResource::getUrl('view', ['record' => $transaction]));

        $transaction->refresh();

        $this->assertSame(2, $transaction->items()->count());
        $this->assertSame(60000, $transaction->subtotal);
        $this->assertSame(5000, $transaction->discount);
        $this->assertSame(5500, $transaction->tax);
        $this->assertSame(60500, $transaction->grand_total);
    }

    public function test_order_print_route_can_render_printable_order_sheet(): void
    {
        $user = User::factory()->create();
        $transaction = $this->createDraftTransaction($user);

        $this->actingAs($user)
            ->get(route('admin.transactions.order-print', $transaction))
            ->assertOk()
            ->assertSee('No Pesanan')
            ->assertSee($transaction->invoice_number)
            ->assertSee('Test Americano')
            ->assertSee('Test Coffee')
            ->assertSee('Total');
    }

    private function createDraftTransaction(User $user, string $invoiceNumber = 'INV-TEST-001'): Transaction
    {
        $menu = $this->createMenu();

        $espresso = Ingredient::query()->create([
            'name' => 'Test Espresso',
            'unit' => 'gr',
            'price' => 100,
            'minimum_stock' => 10,
            'current_stock' => 100,
        ]);

        $water = Ingredient::query()->create([
            'name' => 'Test Water',
            'unit' => 'ml',
            'price' => 1,
            'minimum_stock' => 100,
            'current_stock' => 1000,
        ]);

        $recipe = Recipe::query()->create([
            'menu_id' => $menu->id,
            'name' => 'Test Recipe',
            'is_active' => true,
        ]);

        $recipe->items()->createMany([
            ['ingredient_id' => $espresso->id, 'quantity' => 18],
            ['ingredient_id' => $water->id, 'quantity' => 150],
        ]);

        $transaction = Transaction::query()->create([
            'invoice_number' => $invoiceNumber.'-'.uniqid(),
            'transaction_date' => now(),
            'cashier_id' => $user->id,
            'customer_id' => null,
            'subtotal' => 30000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 30000,
            'status' => 'draft',
            'note' => null,
        ]);

        $transaction->items()->create([
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => 2,
            'price' => 15000,
            'subtotal' => 30000,
            'note' => null,
        ]);

        return $transaction;
    }

    private function createMenu(): Menu
    {
        $category = Category::query()->create([
            'name' => 'Test Coffee',
            'slug' => 'test-coffee-'.uniqid(),
            'is_active' => true,
        ]);

        return Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Americano',
            'slug' => 'test-americano-'.uniqid(),
            'selling_price' => 15000,
            'cost_price' => 5000,
            'is_active' => true,
            'photo_path' => null,
        ]);
    }
}
