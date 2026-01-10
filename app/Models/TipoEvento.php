<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoEvento extends Model
{
    protected $table = "eventos_tipos";
    protected $guarded = [];
    public $timestamps = false;
    protected $primaryKey = 'tipo';
}
