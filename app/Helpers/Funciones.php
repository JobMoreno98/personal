<?php

namespace App\Helpers;

class TiempoHelper
{
    public static function tiempoASegundos($tiempo)
    {
        if (!$tiempo) return 0;
        $partes = explode(':', $tiempo);
        $h = (int) ($partes[0] ?? 0);
        $m = (int) ($partes[1] ?? 0);
        $s = (int) ($partes[2] ?? 0);
        return $h * 3600 + $m * 60 + $s;
    }

    public static function segundosAHorasMinSeg($segundos)
    {
        $h = intdiv($segundos, 3600);
        $segundos %= 3600;
        $m = intdiv($segundos, 60);
        $s = $segundos % 60;
        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }
}
