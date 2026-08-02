<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerStatementReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_statement_shows_paid_and_unpaid_amounts_per_transaction(): void
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'name' => 'Eman Test',
            'phone_number' => '0800000000',
        ]);
        $menu = $this->createMenu();

        $paidTransaction = $this->createTransaction($user, $customer, $menu, 'INV-PAID-TEST', 30000, 'paid');
        Payment::query()->create([
            'transaction_id' => $paidTransaction->id,
            'method' => 'cash',
            'amount_paid' => 50000,
            'change_amount' => 20000,
            'paid_at' => now(),
            'status' => 'paid',
        ]);

        $draftTransaction = $this->createTransaction($user, $customer, $menu, 'INV-DRAFT-TEST', 45000, 'draft');

        $this->actingAs($user)
            ->get(route('admin.report-exports.customer-statement', [
                'format' => 'excel',
                'customer_id' => $customer->id,
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Eman Test')
            ->assertSee($paidTransaction->invoice_number)
            ->assertSee($draftTransaction->invoice_number)
            ->assertSee('Lunas')
            ->assertSee('Belum Lunas')
            ->assertSee('Rp 30.000')
            ->assertSee('Rp 45.000');
    }

    public function test_customer_product_sales_report_includes_unpaid_customer_transactions(): void
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'name' => 'Eman',
            'phone_number' => '0800000000',
        ]);
        $menu = $this->createMenu();

        $this->createTransaction($user, $customer, $menu, 'INV-EMAN-HUTANG', 45000, 'draft');

        $this->actingAs($user)
            ->get(route('admin.report-exports.customer-product-sales', [
                'format' => 'excel',
                'customer_id' => $customer->id,
                'start_date' => now()->startOfMonth()->toDateString(),
                'end_date' => now()->toDateString(),
            ]))
            ->assertOk()
            ->assertSee('Eman')
            ->assertSee('Belum Dibayar')
            ->assertSee('Rp 45.000');
    }

    private function createMenu(): Menu
    {
        $category = Category::query()->create([
            'name' => 'Test Category',
            'slug' => 'test-category-'.uniqid(),
            'is_active' => true,
        ]);

        return Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'Test Kopi',
            'slug' => 'test-kopi-'.uniqid(),
            'selling_price' => 15000,
            'cost_price' => 5000,
            'is_active' => true,
        ]);
    }

    private function createTransaction(
        User $user,
        Customer $customer,
        Menu $menu,
        string $invoiceNumber,
        int $grandTotal,
        string $status,
    ): Transaction {
        $transaction = Transaction::query()->create([
            'invoice_number' => $invoiceNumber.'-'.uniqid(),
            'transaction_date' => now(),
            'cashier_id' => $user->id,
            'customer_id' => $customer->id,
            'subtotal' => $grandTotal,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => $grandTotal,
            'status' => $status,
        ]);

        $transaction->items()->create([
            'menu_id' => $menu->id,
            'menu_name' => $menu->name,
            'quantity' => 1,
            'price' => $grandTotal,
            'subtotal' => $grandTotal,
        ]);

        return $transaction;
    }
}
