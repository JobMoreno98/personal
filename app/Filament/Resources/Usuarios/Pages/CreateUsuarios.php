<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuariosResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Contracts\Support\Htmlable;

class CreateUsuarios extends CreateRecord
{
    protected static string $resource = UsuariosResource::class;

    public function getTitle(): string|Htmlable
    {
        return 'Crear Empleado';
    }
}
