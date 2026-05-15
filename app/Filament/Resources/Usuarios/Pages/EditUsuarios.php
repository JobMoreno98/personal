<?php

namespace App\Filament\Resources\Usuarios\Pages;

use App\Filament\Resources\Usuarios\UsuariosResource;
use App\Filament\Widgets\CalendarioAsistenciaWidget;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditUsuarios extends EditRecord
{
    protected static string $resource = UsuariosResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
    public function getTitle(): string|Htmlable
    {
        $nombre = $this->record->nombre ?? 'Registro';
        return "Editar {$nombre}";
    }
    // AGREGA ESTE MÉTODO AQUÍ
    protected function getFooterWidgets(): array
    {
        return [
            CalendarioAsistenciaWidget::class,
        ];
    }
}
