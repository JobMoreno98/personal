<?php

namespace App\Filament\Resources\StatusUsuarios\Pages;

use App\Filament\Resources\StatusUsuarios\StatusUsuarioResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStatusUsuario extends EditRecord
{
    protected static string $resource = StatusUsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
