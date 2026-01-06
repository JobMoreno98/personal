<?php

namespace App\Filament\Resources\StatusUsuarios\Pages;

use App\Filament\Resources\StatusUsuarios\StatusUsuarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStatusUsuarios extends ListRecords
{
    protected static string $resource = StatusUsuarioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
