<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioMateria extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    // CORRECCIÓN: Esto debe ser UN STRING, no un array.
    // Ponemos la primera columna solo para que Laravel no se queje.
    protected $primaryKey = 'usuario';
    protected $guarded = [];
    protected $table = "crn";
    public $timestamps = false;

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'usuario');
    }
    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Materia::class, 'materia');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioCRN::class, 'crn');
    }
    
    protected $casts = [
        'horarios' => 'array',
    ];
}
