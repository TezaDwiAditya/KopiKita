<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Menu;
use App\Models\Recipe;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use App\Services\TransactionService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Gate;
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
        $category = Category::query()->create([
            'name' => 'Test Coffee',
            'slug' => 'test-coffee-'.uniqid(),
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Americano',
            'slug' => 'test-americano-'.uniqid(),
            'selling_price' => 15000,
            'cost_price' => 5000,
            'is_active' => true,
            'photo_path' => null,
        ]);

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
}
