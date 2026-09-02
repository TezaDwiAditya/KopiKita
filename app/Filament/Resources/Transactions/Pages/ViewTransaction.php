<?php

namespace App\Filament\Resources\Transactions\Pages;

use App\Filament\Resources\Transactions\TransactionResource;
use App\Models\Transaction;
use App\Services\QrisService;
use App\Services\WhatsAppService;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Js;
use InvalidArgumentException;

class ViewTransaction extends ViewRecord
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('send_invoice_whatsapp_missing_phone')
                ->label('💬 Kirim Tagihan WA')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->visible(fn (Transaction $record): bool => blank($record->customer?->phone_number))
                ->action(fn (): null => $this->notifyMissingPhone()),
            Action::make('send_invoice_whatsapp')
                ->label('💬 Kirim Tagihan WA')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->color('success')
                ->visible(fn (Transaction $record): bool => filled($record->customer?->phone_number))
                ->action(fn (Transaction $record): null => $this->openWhatsAppInvoice($record)),
            Action::make('view_qris_missing')
                ->label('📱 Lihat QRIS')
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->visible(fn (Transaction $record): bool => ! $this->hasQris($record))
                ->action(fn (): null => $this->notifyMissingQris()),
            Action::make('view_qris')
                ->label('📱 Lihat QRIS')
                ->icon('heroicon-o-qr-code')
                ->color('primary')
                ->visible(fn (Transaction $record): bool => $this->hasQris($record))
                ->modalHeading('QRIS Pembayaran')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn (Transaction $record) => view('filament.transactions.qris-modal', [
                    'transaction' => $record->loadMissing('payment'),
                ])),
            Action::make('send_invoice_qris_missing')
                ->label('💬 Kirim Tagihan + QRIS')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (Transaction $record): bool => blank($record->customer?->phone_number) || ! $this->hasQris($record))
                ->action(function (Transaction $record): void {
                    if (blank($record->customer?->phone_number)) {
                        $this->notifyMissingPhone();

                        return;
                    }

                    $this->notifyMissingQris();
                }),
            Action::make('send_invoice_qris')
                ->label('💬 Kirim Tagihan + QRIS')
                ->icon('heroicon-o-paper-airplane')
                ->color('success')
                ->visible(fn (Transaction $record): bool => filled($record->customer?->phone_number) && $this->hasQris($record))
                ->modalHeading('Kirim Tagihan + QRIS')
                ->modalSubmitActionLabel('Buka WhatsApp')
                ->modalContent(fn (Transaction $record) => view('filament.transactions.qris-modal', [
                    'transaction' => $record->loadMissing('payment'),
                ]))
                ->action(fn (Transaction $record): null => $this->openWhatsAppInvoice($record, includeQrisInstruction: true)),
            Action::make('share_qris_missing')
                ->label('📤 Share QRIS')
                ->icon('heroicon-o-share')
                ->color('gray')
                ->visible(fn (Transaction $record): bool => ! $this->hasQris($record))
                ->action(fn (): null => $this->notifyMissingQris()),
            Action::make('share_qris')
                ->label('📤 Share QRIS')
                ->icon('heroicon-o-share')
                ->color('gray')
                ->visible(fn (Transaction $record): bool => $this->hasQris($record))
                ->modalHeading('Share QRIS')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Tutup')
                ->modalContent(fn (Transaction $record) => view('filament.transactions.qris-modal', [
                    'transaction' => $record->loadMissing('payment'),
                ])),
            Action::make('download_qris_missing')
                ->label('⬇️ Download QRIS')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (Transaction $record): bool => ! $this->hasQris($record))
                ->action(fn (): null => $this->notifyMissingQris()),
            Action::make('download_qris')
                ->label('⬇️ Download QRIS')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->visible(fn (Transaction $record): bool => $this->hasQris($record))
                ->action(fn (Transaction $record): null => $this->openQrisDownload($record)),
            Action::make('print_order')
                ->label('Print Pesanan')
                ->icon('heroicon-o-document-text')
                ->color('gray')
                ->url(fn (Transaction $record): string => route('admin.transactions.order-print', $record))
                ->openUrlInNewTab(),
            Action::make('print_receipt')
                ->label('Print Struk')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Transaction $record): string => route('admin.transactions.receipt', $record))
                ->openUrlInNewTab(),
            EditAction::make(),
        ];
    }

    private function openWhatsAppInvoice(Transaction $transaction, bool $includeQrisInstruction = false): null
    {
        try {
            $url = app(WhatsAppService::class)->generateWhatsAppUrl($transaction, $includeQrisInstruction);

            $this->js('window.open('.Js::from($url).', "_blank", "noopener,noreferrer")');
        } catch (InvalidArgumentException $exception) {
            Notification::make()
                ->title('Tagihan WhatsApp gagal dibuat')
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }

        return null;
    }

    private function openQrisDownload(Transaction $transaction): null
    {
        $transaction->loadMissing('payment');

        if (! $this->hasQris($transaction)) {
            return $this->notifyMissingQris();
        }

        $url = route('admin.transactions.qris.download', $transaction);

        $this->js(
            'const link = document.createElement("a");'
            .'link.href = '.Js::from($url).';'
            .'link.download = '.Js::from('qris-'.$transaction->invoice_number).';'
            .'link.target = "_blank";'
            .'link.rel = "noopener noreferrer";'
            .'document.body.appendChild(link);'
            .'link.click();'
            .'link.remove();'
        );

        return null;
    }

    private function hasQris(Transaction $transaction): bool
    {
        $transaction->loadMissing('payment');

        return app(QrisService::class)->exists($transaction);
    }

    private function notifyMissingPhone(): null
    {
        Notification::make()
            ->title('Nomor WhatsApp customer belum tersedia.')
            ->warning()
            ->send();

        return null;
    }

    private function notifyMissingQris(): null
    {
        Notification::make()
            ->title('QRIS untuk transaksi ini belum tersedia.')
            ->warning()
            ->send();

        return null;
    }
}
