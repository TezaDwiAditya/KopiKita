<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\Transaction;
use App\Models\User;
use App\Services\QrisService;
use App\Services\WhatsAppService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WhatsAppInvoiceTest extends TestCase
{
    use DatabaseTransactions;

    public function test_normalizes_indonesian_whatsapp_number(): void
    {
        $service = app(WhatsAppService::class);

        $this->assertSame('6281234567890', $service->normalizeIndonesianPhone('081234567890'));
        $this->assertSame('6281234567890', $service->normalizeIndonesianPhone('6281234567890'));
        $this->assertSame('6281234567890', $service->normalizeIndonesianPhone('+62 812-3456-7890'));
    }

    public function test_generates_whatsapp_url_for_transaction_invoice(): void
    {
        $transaction = $this->createTransactionWithCustomer();

        $url = app(WhatsAppService::class)->generateWhatsAppUrl($transaction);

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString(rawurlencode($transaction->invoice_number), $url);
    }

    public function test_generates_order_confirmation_url_for_saved_order(): void
    {
        $transaction = $this->createTransactionWithCustomer();

        $url = app(WhatsAppService::class)->generateOrderConfirmationUrl($transaction);
        $message = urldecode((string) parse_url($url, PHP_URL_QUERY));

        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $url);
        $this->assertStringContainsString('Konfirmasi Pesanan', $message);
        $this->assertStringContainsString('Halo Test Customer, pesanan Anda sudah kami terima.', $message);
        $this->assertStringContainsString('Mohon konfirmasi apakah pesanan sudah sesuai.', $message);
    }

    public function test_generates_invoice_message_from_database_values(): void
    {
        $transaction = $this->createTransactionWithCustomer();

        $message = app(WhatsAppService::class)->generateInvoiceMessage($transaction);

        $this->assertStringContainsString('Invoice: '.$transaction->invoice_number, $message);
        $this->assertStringContainsString('Tanggal: 02 September 2026', $message);
        $this->assertStringContainsString('Customer: Test Customer', $message);
        $this->assertStringContainsString('KSK x 2 Rp30.000', $message);
        $this->assertStringContainsString('Americano x 1 Rp15.000', $message);
        $this->assertStringContainsString('Subtotal Rp45.000', $message);
        $this->assertStringContainsString('Diskon Rp0', $message);
        $this->assertStringContainsString('TOTAL Rp45.000', $message);
    }

    public function test_generates_invoice_message_with_qris_instruction(): void
    {
        $transaction = $this->createTransactionWithCustomer();

        $message = app(WhatsAppService::class)->generateInvoiceMessage($transaction, includeQrisInstruction: true);

        $this->assertStringContainsString('Silakan scan QRIS yang kami lampirkan/kirim terpisah', $message);
    }

    public function test_invoice_message_handles_missing_optional_data(): void
    {
        $user = User::factory()->create();

        $transaction = Transaction::query()->create([
            'invoice_number' => 'INV-EMPTY-'.uniqid(),
            'transaction_date' => '2026-09-02 10:00:00',
            'cashier_id' => $user->id,
            'customer_id' => null,
            'subtotal' => 0,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 0,
            'status' => 'draft',
            'note' => null,
        ]);

        $message = app(WhatsAppService::class)->generateInvoiceMessage($transaction);

        $this->assertStringContainsString('Invoice: '.$transaction->invoice_number, $message);
        $this->assertStringContainsString('TOTAL Rp0', $message);
    }

    public function test_authenticated_admin_can_view_private_default_qris(): void
    {
        $transaction = $this->createTransactionWithCustomer();

        $this->actingAs(User::factory()->create())
            ->get(route('admin.transactions.qris', $transaction))
            ->assertOk();
    }

    public function test_qris_service_only_uses_private_default_qris_image(): void
    {
        $transaction = $this->createTransactionWithCustomer();
        $transaction->payment()->create([
            'method' => 'qris',
            'amount_paid' => 0,
            'change_amount' => 0,
            'paid_at' => null,
            'status' => 'pending',
            'qris_image' => 'qris/transaction-specific.jpeg',
            'qris_reference' => null,
            'qris_amount' => $transaction->grand_total,
            'qris_status' => 'pending',
        ]);

        $this->assertSame('images/QRIS_KopitKita.jpeg', app(QrisService::class)->imagePath($transaction));
    }

    private function createTransactionWithCustomer(): Transaction
    {
        $user = User::factory()->create();
        $customer = Customer::query()->create([
            'name' => 'Test Customer',
            'phone_number' => '081234567890',
        ]);

        $category = Category::query()->create([
            'name' => 'Test Coffee',
            'slug' => 'test-coffee-'.uniqid(),
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'category_id' => $category->id,
            'name' => 'KSK',
            'slug' => 'ksk-'.uniqid(),
            'selling_price' => 15000,
            'cost_price' => 5000,
            'is_active' => true,
            'photo_path' => null,
        ]);

        $transaction = Transaction::query()->create([
            'invoice_number' => 'INV-20260902-0001-'.uniqid(),
            'transaction_date' => '2026-09-02 10:00:00',
            'cashier_id' => $user->id,
            'customer_id' => $customer->id,
            'subtotal' => 45000,
            'discount' => 0,
            'tax' => 0,
            'grand_total' => 45000,
            'status' => 'draft',
            'note' => null,
        ]);

        $transaction->items()->createMany([
            [
                'menu_id' => $menu->id,
                'menu_name' => 'KSK',
                'quantity' => 2,
                'price' => 15000,
                'subtotal' => 30000,
                'note' => null,
            ],
            [
                'menu_id' => $menu->id,
                'menu_name' => 'Americano',
                'quantity' => 1,
                'price' => 15000,
                'subtotal' => 15000,
                'note' => null,
            ],
        ]);

        return $transaction->refresh();
    }
}
