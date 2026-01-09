<?php

namespace App\Filament\Resources\Registros;

use App\Filament\Resources\Registros\Pages\CreateRegistros;
use App\Filament\Resources\Registros\Pages\EditRegistros;
use App\Filament\Resources\Registros\Pages\ListRegistros;
use App\Filament\Resources\Registros\Schemas\RegistrosForm;
use App\Filament\Resources\Registros\Tables\RegistrosTable;
use App\Models\Registros;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class RegistrosResource extends Resource
{
    protected static ?string $model = Registros::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return RegistrosForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RegistrosTable::configure($table);
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
            'index' => ListRegistros::route('/'),
            'create' => CreateRegistros::route('/create'),
            'edit' => EditRegistros::route('/{record}/edit'),
        ];
    }
}
