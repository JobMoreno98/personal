@php
    date_default_timezone_set('America/Mexico_City');
    setlocale(LC_TIME, 'es_MX.UTF-8', 'esp');
    $img = asset('images/logo_nuevo.png');

    function tiempoASegundos($tiempo)
    {
        $partes = explode(':', $tiempo);

        $h = (int) ($partes[0] ?? 0);
        $m = (int) ($partes[1] ?? 0);
        $s = (int) ($partes[2] ?? 0);

        return $h * 3600 + $m * 60 + $s;
    }

    function segundosAHorasMinSeg($segundos)
    {
        $h = intdiv($segundos, 3600);
        $segundos %= 3600;

        $m = intdiv($segundos, 60);
        $s = $segundos % 60;

        return sprintf('%02d:%02d:%02d', $h, $m, $s);
    }

    $total_segundos_reales = 0;
    $total_segundos_justificados = 0;
    $total_segundos_festivos = 0;
    $total_segundos_periodo = 0;
    $total_periodo = 0;

    $dias_periodo = 0;
    $dias_laborables = 0;
    $dias_libres = 0;
    $dias_asistidos = 0;
    $dias_faltados = 0;
    $dias_justificados = 0;
    $dias_festivos = 0;
    $dias_con_error = 0;
    $segundosEntrada = tiempoASegundos($usuario->horario->entrada);
    $segundosSalida = tiempoASegundos($usuario->horario->salida);

    $cargaSegundos = $segundosSalida - $segundosEntrada;
    $cargaSemana = $cargaSegundos * count($usuario->horario->dias);

@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    <header id="header">
        <div style="height: 100%">
            <img src="{{ $img }}" height="100px" style="float:left;margin-right:20px; padding-right:20px;">
            <p style="margin-top: 30px;line-height:.8;">
                <span class="bold-text titulo">UNIVERSIDAD DE GUADALAJARA </span><br>
                <span style="color:#7D91BE;font-size: 10pt;" class="bold-text"> CENTRO UNIVERSITARIO DE CIENCIAS
                    SOCIALES Y HUMANIDADES</span> <br>
                <span style="font-size: 8pt;">SECRETARÍA ADMINISTRATIVA</span> <br>
                <span style="font-size: 8pt;">COORDINACIÓN DE PERSONAL</span>
            </p>
        </div>
    </header>

    <footer id="footer">

    </footer>

    <main style="clear: both;">
        <div style="width:100%;margin-top:.5cm;">
            <p style="line-height:.8;overflow-wrap: break-word;" class="bold-text text-center">
                REPORTE DE REGISTROS DE {{ $usuario->nombre }} ({{ $usuario->usuario }}), <br>
                PERDIODO DEL {{ $periodo[0] }} AL {{ $periodo[1] }}
            </p>
        </div>
        <div>
            @foreach ($calendario as $nombreMes => $diasDelMes)
                <div class="contenedor-mes" class="mt-2">
                    <div class="titulo-mes">{{ $nombreMes }}</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Lunes</th>
                                <th>Martes</th>
                                <th>Miécoles</th>
                                <th>Jueves</th>
                                <th>Viernes</th>
                                <th>Sábado</th>
                                <th>Domingo</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                {{-- 1. LÓGICA DE RELLENO INICIAL --}}
                                @php
                                    // Tomamos la fecha del primer elemento de este grupo
                                    $primerItem = $diasDelMes[0];
                                    $fechaObj = $primerItem['fecha'];

                                    // Calculamos espacios vacíos (Lunes=1 ... Domingo=7)
                                    // Restamos 1 porque el array visual empieza en índice 0
                                    $espaciosVacios = $fechaObj->dayOfWeekIso - 1;
                                    $contadorCeldas = 0;
                                    $total_semana = 0.0;
                                    $total_mes = 0.0;

                                @endphp

                                {{-- Pintamos celdas grises vacías hasta llegar al día 1 --}}
                                @for ($i = 0; $i < $espaciosVacios; $i++)
                                    <td class="celda-vacia"></td>
                                    @php $contadorCeldas++; @endphp
                                @endfor

                                {{-- 2. DÍAS DEL MES --}}
                                @foreach ($diasDelMes as $item)
                                    {{-- Si ya llenamos 7 celdas, cerramos fila y abrimos nueva --}}
                                    @if ($contadorCeldas > 0 && $contadorCeldas % 7 == 0)
                            </tr>
                            <tr>
            @endif

            @php
                $dias_periodo++;
                $esFin = $item['fecha']->isWeekend();

                if (!$item['es_laboral']) {
                    $dias_libres++;
                } elseif ($item['es_laboral']) {
                    $dias_laborables++;
                }
            @endphp
            <td class="{{ $esFin ? 'fin-de-semana' : '' }}">
                <span class="dia-numero" style="color: {{ $esFin ? '#e53e3e' : '#2d3748' }}">
                    {{ $item['fecha']->day }}
                </span>
                @php

                    if ($item['detalle']) {
                        $dias_asistidos++;

                        $seg = tiempoASegundos($item['detalle']['tiempo']);
                        $total_segundos_reales += $seg;
                    }
                    if ($item['estado'] === 'Justificado') {
                        $dias_justificados++;
                        $total_segundos_justificados += 8 * 3600;
                    }

                    if ($item['es_laboral'] && !$item['estado'] === 'Justificado') {
                        $dias_faltados++;
                    }

                    if ($item['estado'] === 'FALTA') {
                        $dias_faltados++;
                    }
                    if ($item['festivo'] && $item['es_laboral']) {
                        $dias_festivos++;
                    }
                @endphp

                @if ($item['detalle'])
                    @php
                        $total_semana += tiempoASegundos($item['detalle']['tiempo']);
                        $total_mes += tiempoASegundos($item['detalle']['tiempo']);
                        $total_periodo += $total_semana;
                    @endphp
                    {{-- CASO: SI HAY ASISTENCIA --}}
                    <div class="info-bloque">
                        <span class="hora-row"><span class="lbl">E - </span>{{ $item['detalle']['entrada'] }}</span>
                        <br>
                        <span class="hora-row"><span class="lbl">S - </span>{{ $item['detalle']['salida'] }}</span>
                    </div>

                    {{-- Badge Estado (Retardo, Salida Anticipada) --}}
                    <div class="badge-multiline" style="background-color: {{ $item['color'] }}">
                        {!! $item['estado'] !!}
                    </div>
                    <div class="tiempo-total">
                        {{ $item['detalle']['tiempo'] }} h
                    </div>
                @else
                    {{-- CASO: SIN REGISTROS --}}
                    @if ($item['estado'] && $item['estado'] != 'EN CURSO' && $item['es_laboral'])
                        <div class="badge-multiline"
                            style="background-color: {{ $item['color'] }}; margin-top: 15px; color: {{ $item['estado'] == 'DESCANSO' ? '#555' : 'white' }}">
                            {{ $item['estado'] }}
                        </div>
                    @else
                        <div class="badge-multiline"
                            style="background-color: {{ $item['color'] }}; margin-top: 15px; color: {{ $item['estado'] == 'DESCANSO' ? '#555' : 'white' }}">
                            {{ $item['estado'] }}
                        </div>
                    @endif
                @endif
            </td>
            @php
                $esDomingo = $item['fecha']->dayOfWeekIso == 7;
            @endphp

            @if ($esDomingo)
                <td class="total-semana-col">
                    {{ segundosAHorasMinSeg($total_semana) }} hrs.
                </td>
                @php
                    $contadorCeldas++; // ← MUY IMPORTANTE
                    $total_semana = 0;
                @endphp
            @endif

            @php
                if (!$esDomingo) {
                    $contadorCeldas++;
                }
            @endphp
            @endforeach

            {{-- 3. RELLENO FINAL (Estético) --}}
            {{-- Completamos la fila con vacíos hasta que sea múltiplo de 7 --}}
            @if ($contadorCeldas % 7 != 0)
                {{-- celdas vacías hasta llegar al domingo --}}
                @while ($contadorCeldas % 7 != 0)
                    <td class="celda-vacia"></td>
                    @php $contadorCeldas++; @endphp
                @endwhile

                {{-- columna total semana --}}
                <td class="total-semana-col">
                    {{ segundosAHorasMinSeg($total_semana) }} hrs.
                </td>
                @php
                    $contadorCeldas++;
                    $total_semana = 0;
                @endphp
            @endif


            </tr>
            </tbody>
            </table>
        </div>
        <div class="text-right">
            Total mes: {{ segundosAHorasMinSeg($total_mes) }} hrs.
        </div>
        @endforeach
        </div>
        <div class="table-new">
            <div class="table-header-new">
                <div class="table-row-new">
                    <div class="table-cell-new" colspan="4">TOTALES</div>
                </div>
            </div>
            <div class="table-body-new">
                <div class="table-row-new">
                    <div class="table-cell-new" colspan="2">DIAS</div>
                    <div class="table-cell-new" colspan="2">HORAS</div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new">Días Periodo</div>
                    <div class="table-cell-new">{{ $dias_periodo }}</div>
                    <div class="table-cell-new">Carga horaria por semana</div>
                    <div class="table-cell-new">{{ segundosAHorasMinSeg($cargaSemana) }}</div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new">Días laborales</div>
                    <div class="table-cell-new">{{ $dias_laborables }}</div>
                    <div class="table-cell-new">Carga por periodo</div>
                    <div class="table-cell-new">
                        {{ segundosAHorasMinSeg(($dias_periodo - $dias_libres) * $cargaSegundos) }}</div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new"> Días libres</div>
                    <div class="table-cell-new">{{ $dias_libres }}</div>
                    <div class="table-cell-new">Reales registradas (sin contar dias con errores)</div>
                    <div class="table-cell-new">
                        {{ segundosAHorasMinSeg($total_segundos_reales) }}</div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new">Días asistidos</div>
                    <div class="table-cell-new">{{ $dias_asistidos }} </div>
                    <div class="table-cell-new">Justificadas</div>
                    <div class="table-cell-new"> {{ segundosAHorasMinSeg($total_segundos_justificados) }}</div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new">Días faltados</div>
                    <div class="table-cell-new">{{ $dias_faltados }} </div>
                    <div class="table-cell-new">Días Festivos o especiales </div>
                    <div class="table-cell-new"> {{ segundosAHorasMinSeg($dias_festivos * $cargaSegundos) }}</div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new"> Días Justificados</div>
                    <div class="table-cell-new">{{ $dias_justificados }} </div>
                    <div class="table-cell-new">Reales + justificadas + festivos </div>
                    <div class="table-cell-new">
                        {{ segundosAHorasMinSeg(($dias_justificados + $dias_festivos) * $cargaSegundos + $total_segundos_reales) }}
                    </div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new"> Días Festivos</div>
                    <div class="table-cell-new">{{ $dias_festivos }} </div>
                    <div class="table-cell-new">Reales + justificadas + festivos </div>
                    <div class="table-cell-new">
                        {{ segundosAHorasMinSeg(($dias_justificados + $dias_festivos) * $cargaSegundos + $total_segundos_reales) }}
                    </div>
                </div>
                <div class="table-row-new">
                    <div class="table-cell-new" colspan="2"> </div>

                    <div class="table-cell-new" style="text-align: end"> Carga horaria por día </div>
                    <div class="table-cell-new">{{ segundosAHorasMinSeg($cargaSegundos) }} </div>
                </div>
            </div>
        </div>

        <div class="text-center bold-text text-uppercase">
            <p class="border-bottom border-dark " style="height: 100px; "></p>
            Firma {{ $usuario->nombre }} ({{ $usuario->usuario }})
        </div>
    </main>
</body>

</html>
