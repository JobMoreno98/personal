<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioCRN extends Model
{
    protected $table = "horarioscrn";
    protected $primaryKey = 'usuario';
    protected $guarded = [];

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';

}
