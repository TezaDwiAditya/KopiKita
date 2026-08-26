<?php

namespace App\Filament\Resources\ItemCustomOptions;

use App\Filament\Resources\ItemCustomOptions\Pages\CreateItemCustomOption;
use App\Filament\Resources\ItemCustomOptions\Pages\EditItemCustomOption;
use App\Filament\Resources\ItemCustomOptions\Pages\ListItemCustomOptions;
use App\Filament\Resources\ItemCustomOptions\Schemas\ItemCustomOptionForm;
use App\Filament\Resources\ItemCustomOptions\Tables\ItemCustomOptionsTable;
use App\Models\ItemCustomOption;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class ItemCustomOptionResource extends Resource
{
    protected static ?string $model = ItemCustomOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Custom Item';

    protected static ?string $modelLabel = 'Custom Item';

    protected static ?string $pluralModelLabel = 'Custom Item';

    protected static ?string $recordTitleAttribute = 'name';

    protected static string|UnitEnum|null $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return ItemCustomOptionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ItemCustomOptionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListItemCustomOptions::route('/'),
            'create' => CreateItemCustomOption::route('/create'),
            'edit' => EditItemCustomOption::route('/{record}/edit'),
        ];
    }
}
