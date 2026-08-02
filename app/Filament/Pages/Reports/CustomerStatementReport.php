<?php

namespace App\Filament\Pages\Reports;

use App\Models\Customer;
use App\Models\Setting;
use App\Models\Transaction;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class CustomerStatementReport extends Page
{
    protected string $view = 'filament.pages.reports.customer-statement-report';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|UnitEnum|null $navigationGroup = 'Laporan';

    protected static ?string $navigationLabel = 'Laporan Customer';

    protected static ?string $title = 'Laporan Customer';

    protected static ?int $navigationSort = 115;

    public ?string $startDate = null;

    public ?string $endDate = null;

    public int|string|null $customerId = null;

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->toDateString();
        $this->endDate = now()->toDateString();
        $this->customerId = Customer::query()
            ->whereHas('transactions', fn ($query) => $query->whereIn('status', ['draft', 'paid']))
            ->orderBy('name')
            ->value('id') ?? Customer::query()->orderBy('name')->value('id');
    }

    public function getCustomersProperty(): Collection
    {
        return Customer::query()->orderBy('name')->get();
    }

    public function getCustomerProperty(): ?Customer
    {
        return filled($this->customerId) ? Customer::query()->find((int) $this->customerId) : null;
    }

    public function getSettingProperty(): ?Setting
    {
        return Setting::query()->first();
    }

    public function getTransactionsProperty(): Collection
    {
        if (! filled($this->customerId) || blank($this->startDate) || blank($this->endDate)) {
            return collect();
        }

        return Transaction::query()
            ->with(['items', 'payment'])
            ->whereIn('status', ['draft', 'paid'])
            ->where('customer_id', (int) $this->customerId)
            ->whereDate('transaction_date', '>=', $this->startDate)
            ->whereDate('transaction_date', '<=', $this->endDate)
            ->orderBy('transaction_date')
            ->get();
    }

    public function getRowsProperty(): Collection
    {
        $runningBalance = 0;

        return $this->transactions->map(function (Transaction $transaction) use (&$runningBalance): array {
            $amount = (int) $transaction->grand_total;
            $paid = $this->paidAmount($transaction);
            $unpaid = max($amount - $paid, 0);
            $runningBalance += $unpaid;

            return [
                'date' => $transaction->transaction_date->format('d M Y'),
                'invoice' => $transaction->invoice_number,
                'description' => 'Penjualan #'.$transaction->invoice_number.' ('.strtoupper($transaction->status).')',
                'status' => $unpaid > 0 ? 'Belum Lunas' : 'Lunas',
                'amount' => $amount,
                'paid' => $paid,
                'unpaid' => $unpaid,
                'due' => $transaction->transaction_date->format('d M Y'),
                'running_balance' => $runningBalance,
                'items' => $transaction->items->map(fn ($item): array => [
                    'name' => trim($item->menu_name.' '.($item->variant_name ?: '')),
                    'qty' => $item->quantity,
                    'price' => $item->price,
                    'discount' => 0,
                    'subtotal' => $item->subtotal,
                ]),
            ];
        });
    }

    public function getSummaryProperty(): array
    {
        $transactions = $this->transactions;
        $totalSales = (int) $transactions->sum('grand_total');
        $cashIn = (int) $transactions->sum(fn (Transaction $transaction): int => $this->paidAmount($transaction));
        $receivable = max($totalSales - $cashIn, 0);

        return [
            'transaction_count' => $transactions->count(),
            'total_sales' => $totalSales,
            'total_purchase' => 0,
            'total_paid' => $cashIn,
            'total_unpaid' => $receivable,
            'cash_in' => $cashIn,
            'cash_out' => 0,
            'receivable' => $receivable,
        ];
    }

    public function exportUrl(string $format): string
    {
        return route('admin.report-exports.customer-statement', [
            'format' => $format,
            'customer_id' => $this->customerId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
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
