<?php

namespace App\Filament\Resources\StatusUsuarios;

use App\Filament\Resources\StatusUsuarios\Pages\CreateStatusUsuario;
use App\Filament\Resources\StatusUsuarios\Pages\EditStatusUsuario;
use App\Filament\Resources\StatusUsuarios\Pages\ListStatusUsuarios;
use App\Filament\Resources\StatusUsuarios\Schemas\StatusUsuarioForm;
use App\Filament\Resources\StatusUsuarios\Tables\StatusUsuariosTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use App\Models\StatusUsuario;

class StatusUsuarioResource extends Resource
{
    protected static ?string $model = StatusUsuario::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StatusUsuarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatusUsuariosTable::configure($table);
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
            'index' => ListStatusUsuarios::route('/'),
            'create' => CreateStatusUsuario::route('/create'),
            'edit' => EditStatusUsuario::route('/{record}/edit'),
        ];
    }
}
