<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JustificanteFracciones extends Model
{
    protected $table = 'justificantes_fracciones';

    public function tipo(): BelongsTo
    {
        return $this->belongsTo(JustificanteLista::class, 'justificante_id', 'id');
    }
}
