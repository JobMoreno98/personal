<?php

namespace App\Filament\Resources\Justificacions\Schemas;

use App\Models\JustificanteLista;
use App\Models\JustificanteTipo;
use App\Models\Usuarios;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class JustificacionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('fechayhora')
                    ->default(fn() => Carbon::now()),
                Select::make('usuario')->label('Usuario')->disabledOn('edit')
                    ->relationship('user', 'nombre')
                    ->searchable()
                    ->preload()->required()->reactive()
                    ->afterStateUpdated(
                        fn($set) =>
                        $set('justificante', null)
                    ),
                Select::make('justificante')
                    ->label('justificante')
                    ->options(function ($get) {

                        $userId = $get('usuario');

                        if (! $userId) {
                            return [];
                        }

                        $user = Usuarios::where('usuario', $userId)->first();

                        if (! $user || ! $user->tipo) {
                            return [];
                        }

                        return JustificanteLista::whereHas(
                            'tipoUsuarios',
                            fn($q) => $q->where(
                                'tipousuarios.tipo',
                                $user->tipo
                            )
                        )->pluck('nombre', 'id');
                    })
                    ->disabled(fn($get) => ! $get('usuario'))
                    ->searchable()->disabled(fn ($record) => $record->aprobado)
                    ->required(),

                Section::make('Periodo del justificante')
                    ->relationship('periodo')
                    ->schema([
                        DatePicker::make('fecha_inicial')
                            ->label('Fecha inicio')
                            ->required()->disabled(fn ($record) => $record->aprobado),

                        DatePicker::make('fecha_final')
                            ->label('Fecha fin')
                            ->required()
                            ->afterOrEqual('fecha_final')->disabled(fn ($record) => $record->aprobado),
                    ])
                    ->columns(2)->disabled(fn ($record) => $record->aprobado),
                Toggle::make('aprobado')
                    ->label('Justificante aprobado')
                    ->helperText('Una vez aprobado no podrá editarse')->disabled(fn ($record) => $record->aprobado)
                    ->onColor('success')
                    ->offColor('danger')
                    ->reactive()
            ]);
    }
}
