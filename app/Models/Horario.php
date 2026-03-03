<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Horario extends Model
{

    protected $table = "horariousuarios";
    protected $primaryKey = 'usuario';
    protected $guarded = [];
    public $timestamps = false;

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';

    protected $casts = [
        'dias' => 'array', // esto hace que Eloquent devuelva siempre array
    ];
    // Accesorio para leer los días en formato humano automáticamente
    // Uso: $schedule->days_formatted // retorna "Lun, Mar, Mié..."
    protected function dias(): Attribute
    {
        return Attribute::make(
            get: fn($value) => $value ? str_split($value) : [],
            set: fn($value) => is_array($value)
                ? implode('', collect($value)->unique()->sort()->values()->toArray())
                : ''
        );
    }
}
