<?php

namespace App\Filament\Resources\TipoEventos\Pages;

use App\Filament\Resources\TipoEventos\TipoEventoResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTipoEvento extends EditRecord
{
    protected static string $resource = TipoEventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
