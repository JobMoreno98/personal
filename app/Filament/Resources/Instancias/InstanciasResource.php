<?php

namespace App\Filament\Resources\Instancias;

use App\Filament\Resources\Instancias\Pages\CreateInstancias;
use App\Filament\Resources\Instancias\Pages\EditInstancias;
use App\Filament\Resources\Instancias\Pages\ListInstancias;
use App\Filament\Resources\Instancias\Schemas\InstanciasForm;
use App\Filament\Resources\Instancias\Tables\InstanciasTable;
use App\Models\Instancias;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class InstanciasResource extends Resource
{
    protected static ?string $model = Instancias::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Instancias';

    public static function form(Schema $schema): Schema
    {
        return InstanciasForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return InstanciasTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListInstancias::route('/'),
            'create' => CreateInstancias::route('/create'),
            'edit' => EditInstancias::route('/{record}/edit'),
        ];
    }
}
