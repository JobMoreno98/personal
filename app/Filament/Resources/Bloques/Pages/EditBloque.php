<?php

namespace App\Filament\Resources\Bloques\Pages;

use App\Filament\Resources\Bloques\BloqueResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditBloque extends EditRecord
{
    protected static string $resource = BloqueResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
