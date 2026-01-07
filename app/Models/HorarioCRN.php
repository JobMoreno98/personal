<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HorarioCRN extends Model
{
    protected $table = "horarioscrn";
    protected $guarded = [];
    public $timestamps = false;

    public function crnRelacion() // O el nombre que tengas
    {
        return $this->belongsTo(HorarioMateria::class, 'crn_id');
    }
}
