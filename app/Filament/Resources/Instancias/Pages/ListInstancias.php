<?php

namespace App\Filament\Resources\Instancias\Pages;

use App\Filament\Resources\Instancias\InstanciasResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListInstancias extends ListRecords
{
    protected static string $resource = InstanciasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
