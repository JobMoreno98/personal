<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HorarioMateria extends Model
{
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
        return $this->hasMany(HorarioCRN::class, 'crn_id');
    }
}
