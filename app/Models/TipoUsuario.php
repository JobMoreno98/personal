<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoUsuario extends Model
{
    protected $table = "tipousuarios";
    protected $primaryKey = 'tipo';

    public function users()
    {
        return $this->hasMany(Usuarios::class);
    }

    public function justificanteListas()
    {
        return $this->belongsToMany(
            JustificanteLista::class,
            'justificantes_tipousuarios',
            'tipousuario_id',
            'justificante_id'
        );
    }
}
