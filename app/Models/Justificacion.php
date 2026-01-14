<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Justificacion extends Model
{
    protected $guarded = [];
    protected $table = 'justificantes_folios';
    public $timestamps = false;
    protected $primaryKey = 'folio';


    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'usuario', 'usuario');
    }
    public function tipo(): BelongsTo
    {
        return $this->belongsTo(JustificanteLista::class, 'justificante');
    }

    public function justificanteLista()
    {
        return $this->belongsTo(JustificanteLista::class);
    }

    public function periodo()
    {
        return $this->hasOne(JustificantePeriodo::class,'folio','folio');
    }
}
