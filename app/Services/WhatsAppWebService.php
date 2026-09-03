<?php

namespace App\Services;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class WhatsAppWebService implements WhatsAppService
{
    public function __construct(
        private readonly RupiahFormatter $rupiah,
    ) {}

    public function generateInvoiceMessage(Transaction $transaction, bool $includeQrisInstruction = false): string
    {
        $transaction->loadMissing(['customer', 'items']);

        $lines = [
            'KOPIKITA',
            'Tagihan Pembayaran',
            '',
            "Invoice: {$transaction->invoice_number}",
            'Tanggal: '.$this->formatDate($transaction->transaction_date),
        ];

        if ($transaction->customer?->name) {
            $lines[] = "Customer: {$transaction->customer->name}";
        }

        $lines[] = '';
        $lines[] = 'Pesanan:';

        foreach ($transaction->items as $item) {
            $lines[] = $this->formatItemLine($item);
        }

        $subtotal = (int) ($transaction->items->sum('subtotal') ?: $transaction->subtotal);
        $discount = (int) ($transaction->discount ?? 0);
        $total = (int) ($transaction->grand_total ?? max(0, $subtotal - $discount));

        $lines = array_merge($lines, [
            '',
            'Subtotal '.$this->rupiah->format($subtotal),
            'Diskon '.$this->rupiah->format($discount),
            'TOTAL '.$this->rupiah->format($total),
            '',
            $includeQrisInstruction
                ? 'Silakan scan QRIS yang kami lampirkan/kirim terpisah untuk melakukan pembayaran.'
                : 'Silakan melakukan pembayaran melalui QRIS.',
            '',
            'Terima kasih telah melakukan pemesanan di KopiKita.',
        ]);

        return implode("\n", $lines);
    }

    public function generateWhatsAppUrl(Transaction $transaction, bool $includeQrisInstruction = false): string
    {
        return $this->generateUrl($transaction, $this->generateInvoiceMessage($transaction, $includeQrisInstruction));
    }

    public function generateOrderConfirmationMessage(Transaction $transaction): string
    {
        $transaction->loadMissing(['customer', 'items']);

        $lines = [
            'KOPIKITA',
            'Konfirmasi Pesanan',
            '',
            'Halo '.($transaction->customer?->name ?: 'Customer').', pesanan Anda sudah kami terima.',
            "Invoice: {$transaction->invoice_number}",
            'Tanggal: '.$this->formatDate($transaction->transaction_date),
            '',
            'Pesanan:',
        ];

        foreach ($transaction->items as $item) {
            $lines[] = $this->formatItemLine($item);
        }

        $lines = array_merge($lines, [
            '',
            'TOTAL '.$this->rupiah->format($transaction->grand_total),
            '',
            'Mohon konfirmasi apakah pesanan sudah sesuai.',
            'Terima kasih.',
        ]);

        return implode("\n", $lines);
    }

    public function generateOrderConfirmationUrl(Transaction $transaction): string
    {
        return $this->generateUrl($transaction, $this->generateOrderConfirmationMessage($transaction));
    }

    public function normalizeIndonesianPhone(?string $phone): ?string
    {
        $phone = trim((string) $phone);

        if ($phone === '') {
            return null;
        }

        $phone = preg_replace('/[^\d+]/', '', $phone) ?: '';

        if (str_starts_with($phone, '+62')) {
            return '62'.substr($phone, 3);
        }

        if (str_starts_with($phone, '62')) {
            return $phone;
        }

        if (str_starts_with($phone, '0')) {
            return '62'.substr($phone, 1);
        }

        if (str_starts_with($phone, '8')) {
            return '62'.$phone;
        }

        return $phone;
    }

    private function generateUrl(Transaction $transaction, string $message): string
    {
        $transaction->loadMissing('customer');

        $phone = $this->normalizeIndonesianPhone($transaction->customer?->phone_number);

        if ($phone === null) {
            throw new InvalidArgumentException('Nomor WhatsApp customer belum tersedia.');
        }

        if (! $this->isValidIndonesianPhone($phone)) {
            throw new InvalidArgumentException('Nomor WhatsApp customer tidak valid.');
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }

    private function formatItemLine(mixed $item): string
    {
        $name = trim(implode(' ', array_filter([$item->menu_name, $item->variant_name ? "({$item->variant_name})" : null])));

        return sprintf(
            '%s x %s @ %s = %s',
            $name ?: 'Item',
            $item->quantity ?? 0,
            $this->rupiah->format($item->price),
            $this->rupiah->format($item->subtotal),
        );
    }

    private function isValidIndonesianPhone(string $phone): bool
    {
        return preg_match('/^628\d{7,12}$/', $phone) === 1;
    }

    private function formatDate(mixed $date): string
    {
        $date = $date ? Carbon::parse($date) : now();

        $months = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        return $date->format('d').' '.$months[(int) $date->format('n')].' '.$date->format('Y');
    }
}
