<?php

namespace App\Filament\Resources\Eventos;

use App\Filament\Resources\Eventos\Pages\CreateEvento;
use App\Filament\Resources\Eventos\Pages\EditEvento;
use App\Filament\Resources\Eventos\Pages\ListEventos;
use App\Filament\Resources\Eventos\Schemas\EventoForm;
use App\Filament\Resources\Eventos\Tables\EventosTable;
use App\Models\Evento;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EventoResource extends Resource
{
    protected static ?string $model = Evento::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $title = 'Eventos';
    protected static ?string $navigationLabel = 'Eventos';
    protected static ?string $pluralModelLabel = 'Eventos';
    protected static ?int $navigationSort = 3;
    public static function getNavigationGroup(): ?string
    {
        return 'Administrativo';
    }
    public static function form(Schema $schema): Schema
    {
        return EventoForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EventosTable::configure($table);
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
            'index' => ListEventos::route('/'),
            'create' => CreateEvento::route('/create'),
            'edit' => EditEvento::route('/{record}/edit'),
        ];
    }
}
