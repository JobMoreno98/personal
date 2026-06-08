<?php

namespace App\Filament\Pages;

use App\Models\Registros;
use App\Models\Usuarios;
use BackedEnum;
use Filament\Actions\Action as ActionsAction;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;

class RegistrosPorEmpleado extends Page
{
    protected string $view = 'filament.pages.registros-por-empleado';

    public ?string $selectedUsuario = null;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'Registros por empleados';

    protected static ?string $title = 'Registros por empleados';
    protected static ?string $navigationLabel = 'Registros por empleados';
    protected static ?string $pluralModelLabel = 'Registros por empleados';
    protected static ?int $navigationSort = 5;
    
    public static function getNavigationGroup(): ?string
    {
        return 'Administrativo';
    }
    
}
