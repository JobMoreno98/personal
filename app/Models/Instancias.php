<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Instancias extends Model
{
    use LogsActivity;

    protected $table = 'instancias';

    protected $guarded = [];

    protected $primaryKey = 'codigo';

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Usuarios::class, 'jefe');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll();
    }
}
