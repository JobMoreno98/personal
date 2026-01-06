<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bloque extends Model
{
    protected $table = "bloques";
    protected $guarded = [];
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;
}
