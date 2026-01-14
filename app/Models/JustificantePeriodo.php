<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JustificantePeriodo extends Model
{

    protected $table = 'justificantes_periodo';
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'folio';
    
    public function justificanteFolio()
    {
        return $this->belongsTo(Justificacion::class, 'folio', 'folio');
    }
}
