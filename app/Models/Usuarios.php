<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Usuarios extends Model
{
    protected $table = 'usuarios';

    protected $primaryKey = 'usuario';

    protected $guarded = [];

    // Si NO es autoincremental
    public $incrementing = false;

    // Si NO es integer (uuid, string, etc.)
    protected $keyType = 'string';

    // 1. Instancia (Relación a otro modelo)
    public function instance(): BelongsTo
    {
        return $this->belongsTo(Instancias::class, 'departamento');
    }

    // 2. Horario de Trabajo
    public function horario(): HasOne
    {
        return $this->hasOne(Horario::class, 'usuario');
    }

    // 3. Asignatura (Condicional)
    // Solo relevante si el usuario es de tipo académico
    public function subject(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'academic_loads')
            ->withPivot(['crn', 'year', 'cycle']) // Importante para leer datos extra
            ->withTimestamps();
    }

    // --- RELACIONES (HasMany) ---

    // 4. Registros de entrada y salidas
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(Registros::class);
    }

    // 5. Justificantes
    public function justifications(): HasMany
    {
        return $this->hasMany(Justificacion::class);
    }

    // --- LÓGICA DE NEGOCIO ---

    // Helper para saber si lleva asignatura
    public function isAcademic(): bool
    {
        return $this->type === 'academic'; // O UserType::Academic si usas enums
    }

    // Relación: Un usuario pertenece a un Tipo
    public function userType(): BelongsTo
    {
        return $this->belongsTo(TipoUsuario::class, 'tipo');
    }

    // Helper útil: Verifica si es de cierto tipo usando el Slug
    public function isType(string $slug): bool
    {
        // Cargamos la relación si no está cargada para optimizar
        if ($this->relationLoaded('userType')) {
            return $this->userType->slug === $slug;
        }

        // O consulta directa si prefieres
        return $this->userType()->where('slug', $slug)->exists();
    }

    public function estatus(): BelongsTo
    {
        return $this->belongsTo(StatusUsuario::class, 'status');
    }
    public function registros(): HasMany
    {
        // Asumiendo que tu tabla de registros tiene 'usuario' como llave foránea
        return $this->hasMany(Registros::class, 'usuario', 'usuario');
    }
}
