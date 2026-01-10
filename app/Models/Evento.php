<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evento extends Model
{
    protected $table = "eventos";
    protected $guarded = [];
    public $timestamps = false;
    
    public function tipo_evento() // O el nombre que tengas
    {
        return $this->belongsTo(TipoEvento::class, 'tipo','tipo');
    }
}
