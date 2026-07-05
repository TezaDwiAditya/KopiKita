<?php

namespace App\Filament\Pages;

use App\Models\Customer;
use App\Models\Transaction;
use App\Services\TransactionService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;
use UnitEnum;

class CashIn extends Page
{
    protected string $view = 'filament.pages.cash-in';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'POS';

    protected static ?string $navigationLabel = 'Uang Masuk';

    protected static ?string $title = 'Uang Masuk';

    protected static ?int $navigationSort = 2;

    public ?int $customerId = null;

    public string $customerSearch = '';

    public string $paymentMethod = 'cash';

    public int|string $amountPaid = 0;

    public function getFilteredCustomersProperty(): Collection
    {
        return Customer::query()
            ->whereHas('transactions', fn ($query) => $query->where('status', 'draft'))
            ->when($this->customerSearch !== '', fn ($query) => $query
                ->where(function ($query): void {
                    $query->where('name', 'like', '%'.$this->customerSearch.'%')
                        ->orWhere('phone_number', 'like', '%'.$this->customerSearch.'%');
                }))
            ->orderBy('name')
            ->limit(10)
            ->get();
    }

    public function getSelectedCustomerProperty(): ?Customer
    {
        return $this->customerId ? Customer::query()->find($this->customerId) : null;
    }

    public function getUnpaidTransactionsProperty(): Collection
    {
        if (! $this->customerId) {
            return collect();
        }

        return Transaction::query()
            ->withCount('items')
            ->where('customer_id', $this->customerId)
            ->where('status', 'draft')
            ->orderBy('transaction_date')
            ->get();
    }

    public function getTotalBillProperty(): int
    {
        return (int) $this->unpaidTransactions->sum('grand_total');
    }

    public function getTotalTransactionsProperty(): int
    {
        return $this->unpaidTransactions->count();
    }

    public function getChangeAmountProperty(): int
    {
        return max(0, (int) ($this->amountPaid ?: 0) - $this->totalBill);
    }

    public function getRemainingAmountProperty(): int
    {
        return max(0, $this->totalBill - (int) ($this->amountPaid ?: 0));
    }

    public function updatedCustomerSearch(): void
    {
        if ($this->selectedCustomer?->name !== $this->customerSearch) {
            $this->customerId = null;
            $this->amountPaid = 0;
        }
    }

    public function selectCustomer(int $customerId): void
    {
        $this->customerId = $customerId;
        $this->customerSearch = (string) Customer::query()->whereKey($customerId)->value('name');
        $this->amountPaid = $this->totalBill;
    }

    public function clearCustomer(): void
    {
        $this->customerId = null;
        $this->customerSearch = '';
        $this->amountPaid = 0;
    }

    public function fillExactAmount(): void
    {
        $this->amountPaid = $this->totalBill;
    }

    public function payAll(): void
    {
        if (! $this->customerId) {
            Notification::make()->title('Pilih customer terlebih dahulu')->warning()->send();

            return;
        }

        if ($this->unpaidTransactions->isEmpty()) {
            Notification::make()->title('Tidak ada tagihan belum dibayar')->warning()->send();

            return;
        }

        if ((int) $this->amountPaid < $this->totalBill) {
            Notification::make()
                ->title('Uang bayar kurang')
                ->body('Kekurangan '.$this->rupiah($this->remainingAmount).'.')
                ->danger()
                ->send();

            return;
        }

        $paidAmount = (int) $this->amountPaid;
        $remainingPaid = $paidAmount;
        $paidCount = 0;

        try {
            DB::transaction(function () use (&$remainingPaid, &$paidCount): void {
                foreach ($this->unpaidTransactions as $transaction) {
                    $amountForTransaction = min($remainingPaid, (int) $transaction->grand_total);

                    app(TransactionService::class)->pay($transaction, $this->paymentMethod, $amountForTransaction);

                    $remainingPaid -= (int) $transaction->grand_total;
                    $paidCount++;
                }
            });

            Notification::make()
                ->title('Tagihan customer berhasil dibayar')
                ->body($paidCount.' transaksi lunas. Kembalian '.$this->rupiah(max(0, $remainingPaid)).'.')
                ->success()
                ->send();

            $this->amountPaid = 0;
            $this->customerSearch = $this->selectedCustomer?->name ?? '';
        } catch (RuntimeException $exception) {
            Notification::make()->title('Gagal memproses pembayaran')->body($exception->getMessage())->danger()->send();
        } catch (Throwable $exception) {
            report($exception);

            Notification::make()->title('Gagal memproses pembayaran')->body('Terjadi kesalahan sistem.')->danger()->send();
        }
    }

    public function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }
}
