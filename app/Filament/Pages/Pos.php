<?php

namespace App\Filament\Pages;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Setting;
use App\Models\Transaction;
use App\Services\TransactionService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use UnitEnum;
use Throwable;

class Pos extends Page
{
    protected string $view = 'filament.pages.pos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'POS';

    protected static ?string $navigationLabel = 'Kasir POS';

    protected static ?string $title = 'Kasir POS';

    protected static ?int $navigationSort = 1;

    public ?int $selectedCategoryId = null;

    public ?int $customerId = null;

    public string $search = '';

    public array $cart = [];

    public int $discount = 0;

    public int $tax = 0;

    public string $paymentMethod = 'cash';

    public int $amountPaid = 0;

    public ?string $note = null;

    public ?int $lastTransactionId = null;

    public function mount(): void
    {
        $this->tax = $this->calculateTax();
    }

    public function getCategoriesProperty(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    public function getMenusProperty(): Collection
    {
        return Menu::query()
            ->with(['category', 'activeVariants'])
            ->where('is_active', true)
            ->when($this->selectedCategoryId, fn ($query) => $query->where('category_id', $this->selectedCategoryId))
            ->when($this->search !== '', fn ($query) => $query->where('name', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->get();
    }

    public function getCustomersProperty(): Collection
    {
        return Customer::query()
            ->orderBy('name')
            ->get();
    }

    public function selectCategory(?int $categoryId): void
    {
        $this->selectedCategoryId = $categoryId;
    }

    public function addToCartVariant(int $variantId): void
    {
        $variant = MenuVariant::query()
            ->with('menu')
            ->findOrFail($variantId);

        $cartKey = $variant->menu_id.'-'.$variant->id;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['qty']++;
        } else {
            $this->cart[$cartKey] = [
                'menu_id' => $variant->menu_id,
                'menu_variant_id' => $variant->id,
                'name' => $variant->menu->name,
                'variant_name' => $variant->name,
                'price' => $variant->selling_price,
                'qty' => 1,
                'note' => '',
            ];
        }

        $this->recalculateTax();
    }
    public function addToCart(int $menuId): void
    {
        $menu = Menu::query()->findOrFail($menuId);

        if (isset($this->cart[$menuId])) {
            $this->cart[$menuId]['qty']++;
        } else {
            $this->cart[$menuId] = [
                'menu_id' => $menu->id,
                'name' => $menu->name,
                'price' => $menu->selling_price,
                'qty' => 1,
                'note' => '',
            ];
        }

        $this->recalculateTax();
    }

    public function incrementQty(int $menuId): void
    {
        if (! isset($this->cart[$menuId])) {
            return;
        }

        $this->cart[$menuId]['qty']++;
        $this->recalculateTax();
    }

    public function decrementQty(int $menuId): void
    {
        if (! isset($this->cart[$menuId])) {
            return;
        }

        $this->cart[$menuId]['qty']--;

        if ($this->cart[$menuId]['qty'] <= 0) {
            unset($this->cart[$menuId]);
        }

        $this->recalculateTax();
    }

    public function removeItem(int $menuId): void
    {
        unset($this->cart[$menuId]);
        $this->recalculateTax();
    }

    public function updatedDiscount(): void
    {
        $this->discount = max(0, (int) $this->discount);
        $this->recalculateTax();
    }

    public function getSubtotalProperty(): int
    {
        return collect($this->cart)
            ->sum(fn (array $item): int => (int) $item['price'] * (int) $item['qty']);
    }

    public function getGrandTotalProperty(): int
    {
        return max(0, $this->subtotal - $this->discount + $this->tax);
    }

    public function getChangeAmountProperty(): int
    {
        return max(0, $this->amountPaid - $this->grandTotal);
    }

    public function saveDraft(): void
    {
        if ($this->cart === []) {
            Notification::make()->title('Keranjang masih kosong')->warning()->send();

            return;
        }

        $transaction = $this->storeTransaction('draft');
        $this->lastTransactionId = $transaction->id;
        $invoiceNumber = $transaction->invoice_number;

        $this->resetCart();

        Notification::make()
            ->title('Transaksi draft tersimpan')
            ->body($invoiceNumber)
            ->success()
            ->send();
    }

    public function pay(): void
    {
        if ($this->cart === []) {
            Notification::make()->title('Keranjang masih kosong')->warning()->send();

            return;
        }

        if ($this->amountPaid < $this->grandTotal) {
            Notification::make()->title('Uang bayar kurang')->danger()->send();

            return;
        }

        try {
            $transaction = $this->storeTransaction('draft');
            app(TransactionService::class)->pay($transaction, $this->paymentMethod, $this->amountPaid);

            $this->lastTransactionId = $transaction->id;
            $invoiceNumber = $transaction->invoice_number;

            $this->resetCart();

            Notification::make()
                ->title('Pembayaran berhasil')
                ->body($invoiceNumber)
                ->success()
                ->send();
        } catch (Throwable $exception) {
            Notification::make()
                ->title('Pembayaran gagal')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    public function voidCart(): void
    {
        $this->resetCart();

        Notification::make()->title('Keranjang dikosongkan')->success()->send();
    }

    public function printLastReceipt(): void
    {
        if (! $this->lastTransactionId) {
            Notification::make()->title('Belum ada transaksi untuk dicetak')->warning()->send();

            return;
        }

        $this->redirectRoute('admin.transactions.receipt', ['transaction' => $this->lastTransactionId], navigate: false);
    }

    private function storeTransaction(string $status): Transaction
    {
        return DB::transaction(function () use ($status): Transaction {
            $transaction = Transaction::query()->create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'transaction_date' => now(),
                'cashier_id' => auth()->id(),
                'customer_id' => $this->customerId,
                'subtotal' => $this->subtotal,
                'discount' => $this->discount,
                'tax' => $this->tax,
                'grand_total' => $this->grandTotal,
                'status' => $status,
                'note' => $this->note,
            ]);

            foreach ($this->cart as $item) {
                $transaction->items()->create([
                    'menu_id' => $item['menu_id'],
                    'menu_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                    'note' => $item['note'] ?: null,
                ]);
            }

            return $transaction;
        });
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV-'.now()->format('Ymd').'-';
        $lastInvoice = Transaction::query()
            ->where('invoice_number', 'like', $prefix.'%')
            ->lockForUpdate()
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $nextNumber = $lastInvoice ? ((int) substr($lastInvoice, -5)) + 1 : 1;

        return $prefix.str_pad((string) $nextNumber, 5, '0', STR_PAD_LEFT);
    }

    private function calculateTax(): int
    {
        $percentage = Setting::query()->value('tax_percentage') ?? 0;

        return (int) round(max(0, $this->subtotal - $this->discount) * $percentage / 100);
    }

    private function recalculateTax(): void
    {
        $this->tax = $this->calculateTax();
    }

    private function resetCart(): void
    {
        $this->cart = [];
        $this->discount = 0;
        $this->tax = 0;
        $this->amountPaid = 0;
        $this->note = null;
    }
}
