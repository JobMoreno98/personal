<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registros extends Model
{
    protected $guarded = [];
    protected $table = "registrosfull";


    public function getFotoUrlAttribute()
    {
        $fecha = \Carbon\Carbon::parse($this->fechahora)
            ->format('Y-m-d_H-i-s');

        return env('SFTP_CAPTURAS_URL') . 'capturas/' .
            $this->usuario . '_' . $fecha . '.jpg';
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'usuario', 'usuario')->groupBy('usuario');
    }

    protected $casts = [
        'fechahora' => 'datetime',
    ];
}
