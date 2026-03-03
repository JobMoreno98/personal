<?php

namespace App\Filament\Resources\Usuarios\Schemas;

use App\Models\User;
use App\Models\Usuarios;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UsuariosForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información Personal')
                    ->schema([
                        FileUpload::make('photo_path')
                            ->label('Foto de Perfil')
                            ->avatar()
                            ->columnSpanFull()
                            ->disk('servidor_fotos')
                            ->acceptedFileTypes(['image/jpg', 'image/jpeg', 'image/png'])
                            ->imageEditor()

                            ->alignCenter()
                            ->image()
                            ->getUploadedFileNameForStorageUsing(function ($file, $get) {
                                $nombre = $get('usuario') ?: time();
                                $fileName = (string)$nombre . '.' . $file->getClientOriginalExtension();
                                $tmpPath = $file->getRealPath(); // NO usar move()
                                $img = Image::read($tmpPath);
                                $img->orient()

                                    ->encodeByExtension($file->getClientOriginalExtension(), 90)
                                    ->save($tmpPath);
                                $sftpDisk = Storage::disk('servidor_fotos');
                                $sftpDisk->putFileAs('', new \Illuminate\Http\File($tmpPath), $fileName);

                                return $fileName;
                            })
                            ->live(),
                        TextInput::make('usuario')->required()->unique(ignoreRecord: true)->label('Código'),
                        TextInput::make('nombre')->required(),

                        // Relación con Tipos de Usuario
                        Select::make('tipo')->label('Tipo')
                            ->relationship('userType', 'descripcion')
                            ->required()
                            ->reactive(),

                        Select::make('status')->label('Estatus')
                            ->relationship('estatus', 'descripcion')
                            ->required()
                            ->reactive(), // Para ocultar/mostrar cosas según la selección
                        Select::make('departamento')
                            ->relationship('instance', 'nombre')->label('Instancia')
                            ->searchable()
                            ->preload(),
                    ])->columns(2)->columnSpanFull(),

                Repeater::make('horarios')
                    ->relationship('horarios')
                    ->schema([
                        CheckboxList::make('dias')
                            ->label('Días de trabajo')
                            ->options([
                                '1' => 'Domingo',
                                '2' => 'Lunes',
                                '3' => 'Martes',
                                '4' => 'Miércoles',
                                '5' => 'Jueves',
                                '6' => 'Viernes',
                                '7' => 'Sábado',
                            ])
                            ->columns(2)
                            ->required()
                            // Añadimos la validación para evitar días duplicados en el repeater
                            ->rule(
                                fn($get) => function (string $attribute, $value, \Closure $fail) use ($get) {
                                    // Obtenemos todos los horarios del repeater
                                    $todosLosHorarios = $get('../../horarios') ?? [];

                                    // CORRECCIÓN: Buscamos el ID correcto de forma dinámica.
                                    // El atributo siempre termina en "...UUID.dias", así que tomamos el penúltimo elemento.
                                    $partesPath = explode('.', $attribute);
                                    $currentId = $partesPath[count($partesPath) - 2] ?? null;

                                    foreach ($todosLosHorarios as $id => $horario) {
                                        // Ahora sí ignoramos correctamente el bloque que estamos editando
                                        if ((string) $id === (string) $currentId) continue;

                                        $diasEnOtroBloque = $horario['dias'] ?? [];

                                        // Comparamos los días
                                        $diasRepetidos = array_intersect($value ?? [], $diasEnOtroBloque);

                                        if (!empty($diasRepetidos)) {
                                            $nombresDias = [
                                                '1' => 'Domingo',
                                                '2' => 'Lunes',
                                                '3' => 'Martes',
                                                '4' => 'Miércoles',
                                                '5' => 'Jueves',
                                                '6' => 'Viernes',
                                                '7' => 'Sábado'
                                            ];

                                            $nombres = array_map(fn($d) => $nombresDias[$d] ?? $d, $diasRepetidos);

                                            $fail('Día(s) duplicado(s): ' . implode(', ', $nombres) . '. Ya están en otro bloque.');
                                        }
                                    }
                                }
                            ),

                        Section::make()->label('Horas')->schema([
                            TimePicker::make('entrada')->label('Hora Entrada')->seconds(false)->required(),
                            TimePicker::make('salida')->label('Hora Salida')->seconds(false)->required(),
                            TextInput::make('diasig')->label('Termina al día siguiente'),
                        ])->collapsible()->collapsed()
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->itemLabel(function (array $state): ?string {
                        $diasMap = ['1' => 'Domingo', '2' => 'Lunes', '3' => 'Martes', '4' => 'Miércoles', '5' => 'Jueves', '6' => 'Viernes', '7' => 'Sábado'];
                        $dias = array_map(fn($d) => $diasMap[$d] ?? $d, $state['dias'] ?? []);
                        return implode(',', $dias) . ' → ' . ($state['entrada'] ?? '') . ' a ' . ($state['salida'] ?? '');
                    })->columnSpanFull()
                    ->collapsed()
                    ->columns(2)
                    ->collapsible()
            ]);
    }
}
