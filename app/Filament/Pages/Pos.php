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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;
use UnitEnum;

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

    public string $customerSearch = '';

    public string $search = '';

    public array $cart = [];

    public int $discount = 0;

    public int $tax = 0;

    public string $paymentMethod = 'cash';

    public int|string $amountPaid = 0;

    public ?string $note = null;

    public string $transactionDate;

    public ?int $lastTransactionId = null;

    public function mount(): void
    {
        $this->transactionDate = today()->toDateString();
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

    public function getFilteredCustomersProperty(): Collection
    {
        return Customer::query()
            ->when($this->customerSearch !== '', fn ($query) => $query
                ->where('name', 'like', '%'.$this->customerSearch.'%')
                ->orWhere('phone_number', 'like', '%'.$this->customerSearch.'%'))
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        return $this->customerId ? Customer::query()->find($this->customerId) : null;
    }

    public function updatedCustomerSearch(): void
    {
        if ($this->selectedCustomer?->name !== $this->customerSearch) {
            $this->customerId = null;
        }
    }

    public function selectCustomer(?int $customerId): void
    {
        $this->customerId = $customerId;
        $this->customerSearch = $customerId ? (string) Customer::query()->whereKey($customerId)->value('name') : '';
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
        $this->customerSearch = '';
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
        $cartKey = (string) $menu->id;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['qty']++;
        } else {
            $this->cart[$cartKey] = [
                'menu_id' => $menu->id,
                'name' => $menu->name,
                'price' => $menu->selling_price,
                'qty' => 1,
                'note' => '',
            ];
        }

        $this->recalculateTax();
    }

    public function incrementQty(string|int $cartKey): void
    {
        if (! isset($this->cart[$cartKey])) {
            return;
        }

        $this->cart[$cartKey]['qty']++;
        $this->recalculateTax();
    }

    public function decrementQty(string|int $cartKey): void
    {
        if (! isset($this->cart[$cartKey])) {
            return;
        }

        $this->cart[$cartKey]['qty']--;

        if ($this->cart[$cartKey]['qty'] <= 0) {
            unset($this->cart[$cartKey]);
        }

        $this->recalculateTax();
    }

    public function removeItem(string|int $cartKey): void
    {
        unset($this->cart[$cartKey]);
        $this->recalculateTax();
    }

    public function updatedDiscount(): void
    {
        $this->discount = max(0, (int) $this->discount);
        $this->recalculateTax();
    }

    public function updatedPaymentMethod(): void
    {
        $this->syncNonCashAmountPaid();
    }

    public function updatedAmountPaid(): void
    {
        if ($this->paymentMethod !== 'cash') {
            $this->syncNonCashAmountPaid();
        }
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
        return max(0, (int) ($this->amountPaid ?? 0) - $this->grandTotal);
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

        $this->syncNonCashAmountPaid();

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
                'transaction_date' => Carbon::parse($this->transactionDate)->setTimeFrom(now()),
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
                    'menu_variant_id' => $item['menu_variant_id'] ?? null,
                    'menu_name' => $item['name'],
                    'variant_name' => $item['variant_name'] ?? null,
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                    'note' => $item['note'] ?: null,
                    'kitchen_status' => 'pending',
                ]);
            }

            return $transaction;
        });
    }

    private function generateInvoiceNumber(): string
    {
        $invoiceDate = Carbon::parse($this->transactionDate);
        $prefix = 'INV-'.$invoiceDate->format('Ymd').'-';
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
        $this->syncNonCashAmountPaid();
    }

    private function syncNonCashAmountPaid(): void
    {
        if ($this->paymentMethod !== 'cash') {
            $this->amountPaid = $this->grandTotal;
        }
    }

    private function resetCart(): void
    {
        $this->cart = [];
        $this->discount = 0;
        $this->tax = 0;
        $this->amountPaid = 0;
        $this->note = null;
        $this->transactionDate = today()->toDateString();
    }
}
