<?php

namespace App\Filament\Resources\TipoEventos;

use App\Filament\Resources\TipoEventos\Pages\CreateTipoEvento;
use App\Filament\Resources\TipoEventos\Pages\EditTipoEvento;
use App\Filament\Resources\TipoEventos\Pages\ListTipoEventos;
use App\Filament\Resources\TipoEventos\Schemas\TipoEventoForm;
use App\Filament\Resources\TipoEventos\Tables\TipoEventosTable;
use App\Models\TipoEvento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;


class TipoEventoResource extends Resource
{
    protected static ?string $model = TipoEvento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;
    protected static ?string $title = 'Tipos de Eventos';
    protected static ?string $navigationLabel = 'Tipos de Eventos';
    protected static ?string $pluralModelLabel = 'Tipos de Eventos';
    protected static ?int $navigationSort = 4;
    public static function getNavigationGroup(): ?string
    {
        return 'Administrativo';
    }
    public static function form(Schema $schema): Schema
    {
        return TipoEventoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TipoEventosTable::configure($table);
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
            'index' => ListTipoEventos::route('/'),
            'create' => CreateTipoEvento::route('/create'),
            'edit' => EditTipoEvento::route('/{record}/edit'),
        ];
    }
}
