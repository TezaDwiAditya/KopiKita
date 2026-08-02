<?php

namespace App\Filament\Pages\Reports;

use App\Models\Customer;
use App\Models\Setting;
use App\Models\Transaction;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use UnitEnum;

class CustomerProductSalesReport extends Page
{
    protected string $view = 'filament.pages.reports.customer-product-sales-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Penjualan per Customer';

    protected static ?string $title = 'Laporan Penjualan per Customer';

    protected static ?int $navigationSort = 112;

    public string $startDate;

    public string $endDate;

    public int|string|null $customerId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
    }

    public function getCustomersProperty(): Collection
    {
        return Customer::query()->orderBy('name')->get();
    }

    public function getSettingProperty(): ?Setting
    {
        return Setting::query()->first();
    }

    public function getRowsProperty(): Collection
    {
        return Transaction::query()
            ->with(['customer', 'payment', 'items'])
            ->whereIn('status', ['draft', 'paid'])
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate)
            ->when(filled($this->customerId), fn ($query) => $query->where('customer_id', (int) $this->customerId))
            ->latest('transaction_date')
            ->get()
            ->map(function (Transaction $transaction): array {
                $paid = $this->paidAmount($transaction);
                $balance = max((int) $transaction->grand_total - $paid, 0);

                return [
                    'invoice' => $transaction->invoice_number,
                    'customer_name' => $transaction->customer?->name ?? 'Walk-in Customer',
                    'date' => $transaction->transaction_date->format('d M Y'),
                    'qty' => (int) $transaction->items->sum('quantity'),
                    'amount' => (int) $transaction->grand_total,
                    'paid' => $paid,
                    'balance' => $balance,
                    'items' => $transaction->items->map(fn ($item): array => [
                        'name' => trim($item->menu_name.' '.($item->variant_name ?: '')),
                        'qty' => (int) $item->quantity,
                        'price' => (int) $item->price,
                        'subtotal' => (int) $item->subtotal,
                    ]),
                ];
            });
    }

    public function getSummaryProperty(): array
    {
        return [
            'customer_count' => $this->rows->pluck('customer_name')->unique()->count(),
            'transaction_count' => $this->rows->count(),
            'qty' => $this->rows->sum('qty'),
            'sales' => $this->rows->sum('amount'),
            'paid' => $this->rows->sum('paid'),
            'unpaid' => $this->rows->sum('balance'),
        ];
    }

    public function generatedAt(): Carbon
    {
        return now();
    }

    public function exportUrl(string $format): string
    {
        return route('admin.report-exports.customer-product-sales', [
            'format' => $format,
            'customer_id' => $this->customerId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'v' => now()->timestamp,
        ]);
    }

    public function rupiah(int|float $amount): string
    {
        return 'Rp '.number_format((int) $amount, 0, ',', '.');
    }

    private function paidAmount(Transaction $transaction): int
    {
        if ($transaction->status !== 'paid') {
            return 0;
        }

        $paid = (int) ($transaction->payment?->amount_paid ?? $transaction->grand_total);
        $change = (int) ($transaction->payment?->change_amount ?? 0);

        return min(max($paid - $change, 0), (int) $transaction->grand_total);
    }
}
