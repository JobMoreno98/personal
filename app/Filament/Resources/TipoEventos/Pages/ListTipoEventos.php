<?php

namespace App\Filament\Resources\TipoEventos\Pages;

use App\Filament\Resources\TipoEventos\TipoEventoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTipoEventos extends ListRecords
{
    protected static string $resource = TipoEventoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
