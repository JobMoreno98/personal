<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instancias extends Model
{
    protected $table = "instancias";
    protected $guarded = [];
    protected $primaryKey = 'codigo';

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'jefe');
    }
}
