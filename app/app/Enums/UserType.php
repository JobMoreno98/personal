<?php
namespace App\Enums;

enum UserType: string
{
    case ACADEMIC = 'academico';
    case ADMINISTRATIVE = 'administrativo';
    case GENERAL = 'general';

    public function label(): string
    {
        return match ($this) {
            self::ACADEMIC => 'Académico',
            self::ADMINISTRATIVE => 'Administrativo',
            self::GENERAL => 'General',
        };
    }
}
