<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mensaje extends Model
{
    protected $guarded = [];
    protected $table = "mensajes";
    protected $primaryKey = 'usuario';
    public $timestamps = false;

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'usuario');
    }
}
