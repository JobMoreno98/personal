@php
    date_default_timezone_set('America/Mexico_City');
    setlocale(LC_TIME, 'es_MX.UTF-8', 'esp');
    $fechaDia = strftime('%e de %B de %Y', strtotime(date('Y-m-d')));
    $img = asset('images/logo_nuevo.png');

@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
    <style>
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 400;
            src: url('{{ asset('fonts/Montserrat-Regular.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 700;
            src: url('{{ asset('fonts/Montserrat-Bold.ttf') }}') format('truetype');
        }

        @font-face {
            font-family: 'Times New Roman';
            font-style: normal;
            font-weight: 700;
            src: url('{{ asset('fonts/Times New Roman Bold.ttf') }}') format('truetype');
        }

        body {
            font-family: 'Montserrat';
            font-size: 10pt;
        }

        @page {
            margin-top: 10px;
            size: letter;
            margin-bottom: 50mm;
        }

        .bold-text {
            font-weight: bold;
        }

        #header {
            position: fixed;
            top: 0px;
            left: 0px;
            right: 0px;
            height: 50px;
            line-height: 35px;
        }

        * {
            margin-bottom: 0px !important;
        }

        main {
            margin-bottom: 40px !important;
        }

        .pie {
            font-size: 10px;
            text-align: center;
            margin-top: 10px;
        }

        #footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid gray;
            /* Borde superior */
            padding-top: 5px;
            height: 1.5cm;
        }

        .titulo {
            font-family: "Times New Roman", "Monserrat";
            font-size: 11pt;
        }

        .text-uppercase {
            text-transform: uppercase;
        }


        .mes-container {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .titulo-mes {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 5px;
            font-weight: bold;
            text-transform: uppercase;
        }

        /* Tabla Calendario */
        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        th {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            padding: 4px;
        }

        td {
            border: 1px solid #ccc;
            height: 60px;
            /* Altura fija para la celda */
            vertical-align: top;
            padding: 2px;
            width: 14.28%;
            /* 100% / 7 días */
        }

        /* Estilos de celda */
        .dia-numero {
            font-weight: bold;
            font-size: 11px;
            text-align: right;
            display: block;
        }

        .fin-de-semana {
            background-color: #fdf2f2;
            color: #a00;
        }

        .vacio {
            background-color: #fafafa;
        }

        /* Datos de asistencia */
        .dato-hora {
            font-size: 9px;
            margin-bottom: 1px;
        }

        .entrada {
            color: #008000;
        }

        /* Verde oscuro */
        .salida {
            color: #cc0000;
        }

        /* Rojo oscuro */
        .tiempo-total {
            background-color: #e6f3ff;
            color: #0056b3;
            text-align: center;
            display: block;
            margin-top: 2px;
            border-radius: 4px;
            padding: 1px;
        }
    </style>
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
        <div style="width:100%;margin-top:1cm;">
            <p style="line-height:.8;overflow-wrap: break-word;" class="bold-text text-center">
                REPORTE DE REGISTROS DE {{ $usuario->nombre }} ({{ $usuario->usuario }}), <br>
                PERDIODO DEL {{ $periodo[0] }} AL {{ $periodo[1] }}
            </p>


        </div>
        <div>
            <div class="text-center">
                <b> Usuario {{ $usuario->nombre }}</b>
            </div>
            <hr>

            @foreach ($calendario as $nombreMes => $diasDelMes)
                <div class="contenedor-mes">
                    <div class="titulo-mes">{{ $nombreMes }}</div>

                    <table>
                        <thead>
                            <tr>
                                <th>Lun</th>
                                <th>Mar</th>
                                <th>Mié</th>
                                <th>Jue</th>
                                <th>Vie</th>
                                <th>Sáb</th>
                                <th>Dom</th>
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
                $esFin = $item['fecha']->isWeekend();
            @endphp

            <td class="{{ $esFin ? 'fin-de-semana' : '' }}">
                {{-- Número del día --}}
                <span class="dia-numero" style="color: {{ $esFin ? '#e53e3e' : '#2d3748' }}">
                    {{ $item['fecha']->day }}
                </span>

                @if ($item['detalle'])
                    {{-- CASO: SI HAY ASISTENCIA --}}
                    <div class="info-bloque">
                        <span class="hora-row"><span class="lbl">E:</span>{{ $item['detalle']['entrada'] }}</span>
                        <br>
                        <span class="hora-row"><span class="lbl">S:</span>{{ $item['detalle']['salida'] }}</span>
                    </div>

                    {{-- Badge Estado (Retardo, Salida Anticipada) --}}
                    <div class="badge" style="background-color: {{ $item['color'] }}">
                        {!! $item['estado'] !!}
                    </div>

                    {{-- Tiempo Trabajado --}}
                    <div class="tiempo-total">
                        {{ $item['detalle']['tiempo'] }} h
                    </div>
                @else
                    {{-- CASO: SIN REGISTROS --}}
                    @if ($item['estado'] && $item['estado'] != 'EN CURSO')
                        {{-- Badge Falta, Descanso, etc --}}
                        <div class="badge"
                            style="background-color: {{ $item['color'] }}; margin-top: 15px; color: {{ $item['estado'] == 'DESCANSO' ? '#555' : 'white' }}">
                            {{ $item['estado'] }}
                        </div>
                    @endif
                @endif
            </td>

            @php $contadorCeldas++; @endphp
            @endforeach

            {{-- 3. RELLENO FINAL (Estético) --}}
            {{-- Completamos la fila con vacíos hasta que sea múltiplo de 7 --}}
            @while ($contadorCeldas % 7 != 0)
                <td class="celda-vacia"></td>
                @php $contadorCeldas++; @endphp
            @endwhile
            </tr>
            </tbody>
            </table>
        </div>
        @endforeach

        </div>
        <div class="text-center">
            <b> Fuente:</b> <span class="text-uppercase"> CUCSH. Secretaria Administrativa, Coordinación de Personal
            </span>
            <br>
            <b> Corte:</b> {{ $fechaDia }}
        </div>
    </main>
</body>

</html>
