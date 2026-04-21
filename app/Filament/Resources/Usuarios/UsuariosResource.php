<?php

namespace App\Filament\Resources\Usuarios;

use App\Filament\Resources\Usuarios\Pages\CreateUsuarios;
use App\Filament\Resources\Usuarios\Pages\EditUsuarios;
use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\Resources\Usuarios\RelationManagers\RegistrosRelationManager;
use App\Filament\Resources\Usuarios\Schemas\UsuariosForm;
use App\Filament\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\Usuarios;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UsuariosResource extends Resource
{
    protected static ?string $model = Usuarios::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Empleados';

    protected static ?string $title = 'Empleado';
    protected static ?string $navigationLabel = 'Empleados';
    protected static ?string $pluralModelLabel = 'Empleados';
protected static ?int $navigationSort = 1;
    public static function getNavigationGroup(): ?string
    {
        return 'Administrativo';
    }

    public static function form(Schema $schema): Schema
    {
        return UsuariosForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [RegistrosRelationManager::class];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuarios::route('/create'),
            'edit' => EditUsuarios::route('/{record}/edit'),
        ];
    }
}
