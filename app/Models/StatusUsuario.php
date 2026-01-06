<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusUsuario extends Model
{
    protected $table = 'statususuarios';

    protected $gaurded = [];

    protected $primaryKey = 'status';

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';
    public $timestamps = false;
}
