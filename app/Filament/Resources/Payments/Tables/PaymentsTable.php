<?php

namespace App\Filament\Resources\Payments\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction.invoice_number')->label('No Invoice')->searchable()->sortable(),
                TextColumn::make('method')->label('Metode')->badge()->formatStateUsing(fn (string $state): string => strtoupper($state))->searchable(),
                TextColumn::make('amount_paid')->label('Bayar')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable(),
                TextColumn::make('change_amount')->label('Kembalian')->formatStateUsing(fn (int $state): string => 'Rp '.number_format($state, 0, ',', '.'))->sortable(),
                TextColumn::make('status')->label('Status')->badge()->color(fn (string $state): string => match ($state) { 'paid' => 'success', 'failed' => 'danger', default => 'warning' }),
                TextColumn::make('paid_at')->label('Waktu Bayar')->dateTime('d M Y H:i')->sortable()->placeholder('-'),
                TextColumn::make('created_at')->label('Dibuat')->dateTime('d M Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('method')->label('Metode')->options(['cash' => 'Cash', 'qris' => 'QRIS', 'transfer' => 'Transfer', 'debit' => 'Debit']),
                SelectFilter::make('status')->label('Status')->options(['pending' => 'Pending', 'paid' => 'Paid', 'failed' => 'Failed']),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}