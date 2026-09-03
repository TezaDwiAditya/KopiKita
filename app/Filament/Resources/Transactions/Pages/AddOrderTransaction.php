<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Category;
use App\Models\ItemCustomOption;
use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Setting;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AddOrderTransaction extends Page
{
    use InteractsWithRecord;

    protected static string $resource = TransactionResource::class;

    protected string $view = 'filament.resources.transactions.pages.add-order-transaction';

    protected static ?string $title = 'Tambah Pesanan';

    public ?int $selectedCategoryId = null;

    public string $search = '';

    public array $cart = [];

    public int|string $discount = 0;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        abort_unless($this->record->status === 'draft', 403);
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

    public function getItemCustomOptionsProperty(): Collection
    {
        return ItemCustomOption::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->pluck('name');
    }

    public function getSubtotalProperty(): int
    {
        return collect($this->cart)
            ->sum(fn (array $item): int => (int) $item['price'] * (int) $item['qty']);
    }

    public function getDiscountAmountProperty(): int
    {
        return max(0, $this->moneyToInt($this->discount));
    }

    public function getCurrentGrandTotalProperty(): int
    {
        return (int) $this->record->grand_total;
    }

    public function getEstimatedGrandTotalProperty(): int
    {
        $subtotal = (int) $this->record->subtotal + $this->subtotal;
        $discount = max(0, (int) $this->record->discount + $this->discountAmount);
        $taxPercentage = Setting::query()->value('tax_percentage') ?? 0;
        $tax = (int) round(max(0, $subtotal - $discount) * $taxPercentage / 100);

        return max(0, $subtotal - $discount + $tax);
    }

    public function updatedDiscount(): void
    {
        $this->discount = $this->discountAmount;
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

            return;
        }

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

    public function addToCart(int $menuId): void
    {
        $menu = Menu::query()->findOrFail($menuId);
        $cartKey = (string) $menu->id;

        if (isset($this->cart[$cartKey])) {
            $this->cart[$cartKey]['qty']++;

            return;
        }

        $this->cart[$cartKey] = [
            'menu_id' => $menu->id,
            'menu_variant_id' => null,
            'name' => $menu->name,
            'variant_name' => null,
            'price' => $menu->selling_price,
            'qty' => 1,
            'note' => '',
        ];
    }

    public function incrementQty(string|int $cartKey): void
    {
        if (! isset($this->cart[$cartKey])) {
            return;
        }

        $this->cart[$cartKey]['qty']++;
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
    }

    public function removeItem(string|int $cartKey): void
    {
        unset($this->cart[$cartKey]);
    }

    public function toggleItemCustom(string|int $cartKey, string $custom): void
    {
        if (! isset($this->cart[$cartKey])) {
            return;
        }

        $customs = $this->parseItemCustoms($this->cart[$cartKey]['note'] ?? '');

        if (in_array($custom, $customs, true)) {
            $customs = array_values(array_filter($customs, fn (string $value): bool => $value !== $custom));
        } else {
            $customs[] = $custom;
        }

        $this->cart[$cartKey]['note'] = implode(', ', $customs);
    }

    public function itemHasCustom(array $item, string $custom): bool
    {
        return in_array($custom, $this->parseItemCustoms($item['note'] ?? ''), true);
    }

    public function clearCart(): void
    {
        $this->cart = [];
        $this->discount = 0;

        Notification::make()->title('Keranjang dikosongkan')->success()->send();
    }

    public function saveOrder(): void
    {
        if ($this->cart === []) {
            Notification::make()->title('Keranjang masih kosong')->warning()->send();

            return;
        }

        DB::transaction(function (): void {
            /** @var Transaction $transaction */
            $transaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($this->record->id);

            abort_unless($transaction->status === 'draft', 403);

            $transaction->discount = max(0, (int) $transaction->discount + $this->discountAmount);
            $transaction->save();

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

            $transaction->recalculateTotals();
        });

        Notification::make()
            ->title('Pesanan berhasil ditambahkan')
            ->success()
            ->send();

        $this->redirect(TransactionResource::getUrl('view', ['record' => $this->record]));
    }

    private function parseItemCustoms(?string $note): array
    {
        return collect(explode(',', (string) $note))
            ->map(fn (string $value): string => trim($value))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function moneyToInt(mixed $value): int
    {
        return (int) preg_replace('/\D/', '', (string) ($value ?? ''));
    }
}
