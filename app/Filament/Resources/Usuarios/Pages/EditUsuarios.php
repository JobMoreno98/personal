<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuariosResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUsuarios extends EditRecord
{
    protected static string $resource = UsuariosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
