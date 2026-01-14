<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JustificanteLista extends Model
{
    protected $guarded = [];
    protected $table = 'justificantes_lista';
    public $timestamps = false;

    public function tipoUsuarios()
    {
        return $this->belongsToMany(
            TipoUsuario::class,
            'justificantes_tipousuarios',
            'justificante_id',            
            'tipousuario_id'
        );
    }
}
