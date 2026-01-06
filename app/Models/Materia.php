<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materia extends Model
{
    protected $guarded = [];
    protected $table = "materias";
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';

    public function instance(): BelongsTo
    {
        return $this->belongsTo(Instancias::class, 'departamento');
    }
}
