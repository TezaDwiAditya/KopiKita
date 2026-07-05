<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\IngredientStock;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ReportExportController extends Controller
{
    public function sales(Request $request, string $format): SymfonyResponse
    {
        $data = $this->salesData($request);
        $filename = 'laporan-penjualan-'.$data['startDate'].'-'.$data['endDate'];

        return $this->download($format, 'reports.exports.sales', $data, $filename);
    }

    public function products(Request $request, string $format): SymfonyResponse
    {
        $data = $this->productData($request);
        $filename = 'laporan-produk-'.$data['startDate'].'-'.$data['endDate'];

        return $this->download($format, 'reports.exports.products', $data, $filename);
    }

    public function ingredients(Request $request, string $format): SymfonyResponse
    {
        $data = $this->ingredientData($request);
        $filename = 'laporan-penggunaan-bahan-'.$data['startDate'].'-'.$data['endDate'];

        return $this->download($format, 'reports.exports.ingredients', $data, $filename);
    }

    public function customerStatement(Request $request, string $format): SymfonyResponse
    {
        $data = $this->customerStatementData($request);
        $customerName = str($data['customer']?->name ?? 'customer')->slug()->value();
        $filename = 'laporan-customer-'.$customerName.'-'.$data['startDate'].'-'.$data['endDate'];

        return $this->download($format, 'reports.exports.customer-statement', $data, $filename);
    }

    private function download(string $format, string $view, array $data, string $filename): SymfonyResponse
    {
        abort_unless(in_array($format, ['pdf', 'excel'], true), 404);

        if ($format === 'pdf') {
            return Pdf::loadView($view, $data)
                ->setPaper('a4', $view === 'reports.exports.customer-statement' ? 'portrait' : 'landscape')
                ->download($filename.'.pdf');
        }

        return response()
            ->view($view, $data)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="'.$filename.'.xls"')
            ->header('Cache-Control', 'max-age=0');
    }

    private function salesData(Request $request): array
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $cashierId = $request->integer('cashier_id') ?: null;

        $transactions = Transaction::query()
            ->with(['cashier', 'customer', 'items.menu'])
            ->where('status', 'paid')
            ->whereDate('transaction_date', '>=', $startDate)
            ->whereDate('transaction_date', '<=', $endDate)
            ->when($cashierId, fn ($query) => $query->where('cashier_id', $cashierId))
            ->latest('transaction_date')
            ->get();

        $grossProfit = $transactions->sum(function (Transaction $transaction): int {
            return $transaction->items->sum(fn ($item): int => ((int) $item->price - (int) ($item->menu?->cost_price ?? 0)) * (int) $item->quantity);
        });

        $dailyRows = $transactions
            ->groupBy(fn (Transaction $transaction): string => $transaction->transaction_date->format('Y-m-d'))
            ->map(fn (Collection $transactions, string $date): array => [
                'date' => $date,
                'count' => $transactions->count(),
                'subtotal' => $transactions->sum('subtotal'),
                'discount' => $transactions->sum('discount'),
                'tax' => $transactions->sum('tax'),
                'grand_total' => $transactions->sum('grand_total'),
            ])
            ->values();

        return [
            'title' => 'Laporan Penjualan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'transactions' => $transactions,
            'dailyRows' => $dailyRows,
            'summary' => [
                'total_sales' => $transactions->sum('grand_total'),
                'total_subtotal' => $transactions->sum('subtotal'),
                'total_discount' => $transactions->sum('discount'),
                'total_tax' => $transactions->sum('tax'),
                'transaction_count' => $transactions->count(),
                'gross_profit' => $grossProfit,
            ],
        ];
    }

    private function productData(Request $request): array
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = TransactionItem::query()
            ->with(['menu', 'variant', 'transaction'])
            ->whereHas('transaction', fn ($query) => $query
                ->where('status', 'paid')
                ->whereDate('transaction_date', '>=', $startDate)
                ->whereDate('transaction_date', '<=', $endDate))
            ->get()
            ->groupBy(fn (TransactionItem $item): string => $item->menu_name.'|'.($item->variant_name ?: 'Regular'))
            ->map(function (Collection $items): array {
                $first = $items->first();
                $sales = $items->sum('subtotal');
                $cost = $items->sum(fn (TransactionItem $item): int => (int) ($item->variant?->cost_price ?? $item->menu?->cost_price ?? 0) * (int) $item->quantity);

                return [
                    'menu' => $first->menu_name,
                    'variant' => $first->variant_name ?: 'Regular',
                    'qty' => $items->sum('quantity'),
                    'sales' => $sales,
                    'cost' => $cost,
                    'gross_profit' => $sales - $cost,
                ];
            })
            ->sortByDesc('qty')
            ->values();

        return [
            'title' => 'Laporan Produk',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rows' => $rows,
            'summary' => [
                'qty' => $rows->sum('qty'),
                'sales' => $rows->sum('sales'),
                'cost' => $rows->sum('cost'),
                'gross_profit' => $rows->sum('gross_profit'),
            ],
        ];
    }

    private function ingredientData(Request $request): array
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());

        $rows = IngredientStock::query()
            ->with('ingredient')
            ->whereIn('type', ['sale', 'void'])
            ->whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->get()
            ->groupBy('ingredient_id')
            ->map(function (Collection $movements): array {
                $ingredient = $movements->first()->ingredient;
                $used = abs($movements->where('type', 'sale')->sum('quantity'));
                $restored = $movements->where('type', 'void')->sum('quantity');
                $netUsed = $used - $restored;

                return [
                    'ingredient' => $ingredient?->name,
                    'unit' => $ingredient?->unit,
                    'used' => $used,
                    'restored' => $restored,
                    'net_used' => $netUsed,
                    'value' => $netUsed * (int) ($ingredient?->price ?? 0),
                ];
            })
            ->sortByDesc('net_used')
            ->values();

        return [
            'title' => 'Laporan Penggunaan Bahan',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rows' => $rows,
            'summary' => [
                'ingredients' => $rows->count(),
                'value' => $rows->sum('value'),
            ],
        ];
    }

    private function customerStatementData(Request $request): array
    {
        $startDate = $request->query('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->query('end_date', now()->toDateString());
        $customerId = $request->integer('customer_id') ?: Customer::query()
            ->whereHas('transactions', fn ($query) => $query->whereIn('status', ['draft', 'paid']))
            ->orderBy('name')
            ->value('id');
        $customer = $customerId ? Customer::query()->find($customerId) : null;

        $transactions = collect();

        if ($customerId) {
            $transactions = Transaction::query()
                ->with(['items', 'payment', 'customer'])
                ->whereIn('status', ['draft', 'paid'])
                ->where('customer_id', $customerId)
                ->whereDate('transaction_date', '>=', $startDate)
                ->whereDate('transaction_date', '<=', $endDate)
                ->orderBy('transaction_date')
                ->get();
        }

        $runningBalance = 0;
        $rows = $transactions->map(function (Transaction $transaction) use (&$runningBalance): array {
            $amount = (int) $transaction->grand_total;
            $paid = $transaction->status === 'paid'
                ? (int) ($transaction->payment?->amount_paid ?? $transaction->grand_total)
                : 0;
            $runningBalance += $amount - $paid;

            return [
                'date' => $transaction->transaction_date->format('d M Y'),
                'invoice' => $transaction->invoice_number,
                'description' => 'Penjualan #'.$transaction->invoice_number.' ('.strtoupper($transaction->status).')',
                'amount' => $amount,
                'paid' => $paid,
                'due' => $transaction->transaction_date->format('d M Y'),
                'running_balance' => $runningBalance,
                'items' => $transaction->items->map(fn (TransactionItem $item): array => [
                    'name' => trim($item->menu_name.' '.($item->variant_name ?: '')),
                    'qty' => (int) $item->quantity,
                    'price' => (int) $item->price,
                    'discount' => 0,
                    'subtotal' => (int) $item->subtotal,
                ]),
            ];
        });

        $totalSales = (int) $transactions->sum('grand_total');
        $cashIn = (int) $transactions->sum(fn (Transaction $transaction): int => $transaction->status === 'paid'
            ? (int) ($transaction->payment?->amount_paid ?? $transaction->grand_total)
            : 0);
        $receivable = (int) $transactions->where('status', 'draft')->sum('grand_total');

        return [
            'title' => 'Laporan Customer',
            'setting' => Setting::query()->first(),
            'customer' => $customer,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rows' => $rows,
            'summary' => [
                'transaction_count' => $transactions->count(),
                'total_sales' => $totalSales,
                'total_purchase' => 0,
                'cash_in' => $cashIn,
                'cash_out' => 0,
                'receivable' => $receivable,
            ],
            'generatedAt' => now(),
        ];
    }
}


