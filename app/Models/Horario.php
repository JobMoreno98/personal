<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Horario extends Model
{

    protected $table = "horariousuarios";
    protected $primaryKey = 'usuario';
    protected $guarded = [];

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';
    

    // Accesorio para leer los días en formato humano automáticamente
    // Uso: $schedule->days_formatted // retorna "Lun, Mar, Mié..."
    protected function dias(): Attribute
    {
        return Attribute::make(
            // GET: Igual que antes
            get: fn(?string $value) => $value ? str_split($value) : [],

            // SET: Aquí agregamos la lógica de ordenamiento
            set: function ($value) {
                if (is_array($value)) {
                    sort($value); // <--- Esto ordena los números (1, 2, 3...)
                    return implode('', $value);
                }
                return $value;
            },
        );
    }

}
