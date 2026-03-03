<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Horario extends Model
{

    protected $table = "horariousuarios";
    protected $guarded = [];
    public $timestamps = false;

    public $incrementing = false;

    protected function dias(): Attribute
    {
        return Attribute::make(
            get: fn(?string $value) => $value ? str_split($value) : [],
            set: fn(mixed $value) => is_array($value)
                ? implode('', collect($value)->unique()->sort()->values()->toArray())
                : (string) $value
        );
    }
}
