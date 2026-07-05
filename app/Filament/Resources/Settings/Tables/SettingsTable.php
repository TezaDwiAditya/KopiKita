<?php

namespace App\Filament\Resources\Settings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')->label('Logo')->square(),
                TextColumn::make('store_name')->label('Nama Toko')->searchable()->sortable(),
                TextColumn::make('phone_number')->label('No HP')->searchable()->placeholder('-'),
                TextColumn::make('tax_percentage')->label('Pajak')->suffix('%')->sortable(),
                TextColumn::make('updated_at')->label('Diubah')->dateTime('d M Y H:i')->sortable(),
            ])
            ->filters([])
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