<?php

namespace App\Filament\Resources\Bloques;

use App\Filament\Resources\Bloques\Pages\CreateBloque;
use App\Filament\Resources\Bloques\Pages\EditBloque;
use App\Filament\Resources\Bloques\Pages\ListBloques;
use App\Filament\Resources\Bloques\Schemas\BloqueForm;
use App\Filament\Resources\Bloques\Tables\BloquesTable;
use App\Models\Bloque;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class BloqueResource extends Resource
{
    protected static ?string $model = Bloque::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return BloqueForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BloquesTable::configure($table);
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
            'index' => ListBloques::route('/'),
            'create' => CreateBloque::route('/create'),
            'edit' => EditBloque::route('/{record}/edit'),
        ];
    }
}
