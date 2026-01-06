<?php

namespace App\Filament\Resources\Instancias\Pages;

use App\Filament\Resources\Instancias\InstanciasResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditInstancias extends EditRecord
{
    protected static string $resource = InstanciasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
