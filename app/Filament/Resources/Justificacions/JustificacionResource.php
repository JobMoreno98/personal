<?php

namespace App\Filament\Resources\Justificacions;

use App\Filament\Resources\Justificacions\Pages\CreateJustificacion;
use App\Filament\Resources\Justificacions\Pages\EditJustificacion;
use App\Filament\Resources\Justificacions\Pages\ListJustificacions;
use App\Filament\Resources\Justificacions\Schemas\JustificacionForm;
use App\Filament\Resources\Justificacions\Tables\JustificacionsTable;
use App\Models\Justificacion;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JustificacionResource extends Resource
{
    protected static ?string $model = Justificacion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return JustificacionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JustificacionsTable::configure($table);
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
            'index' => ListJustificacions::route('/'),
            'create' => CreateJustificacion::route('/create'),
            'edit' => EditJustificacion::route('/{record}/edit'),
        ];
    }
}
