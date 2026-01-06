<?php

namespace App\Filament\Resources\Usuarios\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;


class UsuariosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Foto de Perfil')->avatar()->columnSpanFull()
                            ->disk('servidor_fotos')
                             ->acceptedFileTypes(['image/jpg'])
                            ->imageEditor()->imageCropAspectRatio('1:1')
                            ->visibility('public')
                            ->alignCenter()
                            ->image()
                            ->getUploadedFileNameForStorageUsing(function (TemporaryUploadedFile $file, $get) {

                                // Obtenemos el valor del campo 'codigo' del formulario
                                $nombre = $get('usuario');

                                // Si el código está vacío (por ejemplo en creación), usamos un timestamp
                                if (!$nombre) {
                                    $nombre = time();
                                }

                                // Retornamos: codigo + punto + extensión original (jpg, png, etc)
                                return (string) $nombre . '.' . $file->getClientOriginalExtension();
                            })
                            ->live(), // Para la foto
                        TextInput::make('usuario')->required()->unique(ignoreRecord: true)->label('Código'),
                        TextInput::make('nombre')->required(),


                        // Relación con Tipos de Usuario
                        Select::make('tipo')->label('Tipo')
                            ->relationship('userType', 'descripcion')
                            ->required()
                            ->reactive(), // Para ocultar/mostrar cosas según la selección

                        // Relación con Instancia
                        Select::make('departamento')
                            ->relationship('instance', 'nombre')
                            ->searchable()
                            ->preload(),
                    ])->columns(1),
                Section::make('Horario Laboral')
                    ->relationship('horario') // <--- Vincula esta sección al modelo Horario
                    ->schema([

                        // Ahora sí, este 'days' se busca en la tabla 'horarios'
                        CheckboxList::make('dias')
                            ->label('Días de trabajo')
                            ->options([
                                '1' => 'Domingo',
                                '2' => 'Lunes',
                                '3' => 'Martes',
                                '4' => 'Miércoles',
                                '5' => 'Jueves',
                                '6' => 'Viernes',
                                '7' => 'Sábado'
                            ])
                            ->columns(4), // Opcional: para que se vean horizontales

                        TimePicker::make('entrada')->label('Hora Entrada'),
                        TimePicker::make('salida')->label('Hora Salida'),
                        TextInput::make('diasig'),

                    ])
                    ->collapsed(),
            ]);
    }
}
