<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Registros extends Model
{
    protected $guarded = [];
    protected $table = "registrosfull";

    
    public function getFotoUrlAttribute()
    {
        $fecha = \Carbon\Carbon::parse($this->fechahora)
            ->format('Y-m-d_H-i-s');

        return env('SFTP_URL') . 'capturas/' .
            $this->usuario . '_' . $fecha . '.jpg';
    }
}
