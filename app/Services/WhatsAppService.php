<?php

namespace App\Services;

use App\Models\Transaction;

interface WhatsAppService
{
    public function generateInvoiceMessage(Transaction $transaction, bool $includeQrisInstruction = false): string;

    public function generateWhatsAppUrl(Transaction $transaction, bool $includeQrisInstruction = false): string;

    public function normalizeIndonesianPhone(?string $phone): ?string;
}
