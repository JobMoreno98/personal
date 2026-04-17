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
        return $this->hasOne(JustificantePeriodo::class, 'folio', 'folio');
    }

    // app/Models/Justificacion.php

    protected static function booted()
    {
        static::deleting(function ($justificante) {
            // 1. Obtener el usuario y el periodo antes de borrar
            $usuario = $justificante->user?->usuario;
            $periodo = $justificante->periodo;

            if ($usuario && $periodo) {
                $inicio = \Carbon\Carbon::parse($periodo->fecha_inicial)->startOfDay();
                $fin = \Carbon\Carbon::parse($periodo->fecha_final)->endOfDay();

                // 2. Eliminar los registros que coincidan con los criterios
                \App\Models\Registros::where('usuario', $usuario)
                    ->where('tipo', 'justificado')
                    ->whereBetween('fechahora', [$inicio, $fin])
                    ->delete();
            }
        });
    }
}
