@php
    date_default_timezone_set('America/Mexico_City');
    setlocale(LC_TIME, 'es_MX.UTF-8', 'esp');
    $img = asset('images/logo_nuevo.png');
    $fechaDia = strftime('%e de %B de %Y', strtotime(date('Y-m-d')));
@endphp
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
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
            font-family: 'Montserrat', sans-serif;
            font-size: 10pt;
            color: #333;
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

        #footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid gray;
            padding-top: 5px;
            height: 1.5cm;
        }

        .titulo {
            font-family: "Times New Roman", "Montserrat", serif;
            font-size: 11pt;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        /* ESTILOS DE LA TABLA DE FALTAS */
        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-top: 20px;
        }

        .table-custom th {
            background-color: #fff;
            /* Rojo para denotar las faltas */
            color: #000;
            border: 1px solid grey;
            padding: 5px 2px;
            text-align: center;
            font-weight: bold;
        }

        .table-custom td {
            border: 1px solid #ddd;
            padding: 5px 2px;
            text-align: center;
        }

        .table-custom tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .text-center {
            text-align: center;
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
        <div style="width:100%;margin-top:1cm; margin-bottom: 20px !important;">
            <p style="line-height:.8;overflow-wrap: break-word;" class="bold-text text-center text-uppercase">
                REPORTE DE ASISTENCIA DIARIA - {{ mb_strtoupper($departamento->nombre, 'UTF-8') }} <br>
                CORRESPONDIENTE AL DÍA {{ strftime('%e de %B de %Y', strtotime(date($fecha)))  }}
            </p>
        </div>

        <div>
            @if ($usuarios->isEmpty())
                <div style="text-align: center; margin-top: 40px; color: #6c757d; font-weight: bold; font-size: 11pt;">
                    Ningún trabajador de este departamento tiene horario laboral asignado para este día.
                </div>
            @else
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 15%; color:#000;">Código</th>
                            <th
                                style="width: 45%; text-align: left; padding-left: 15px; color:#000;">
                                Nombre del Trabajador</th>
                            <th style="width: 20%; color:#000;">Horario Esperado
                            </th>
                            <th style="width: 20%; color:#000;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $user)
                            <tr>
                                <td class="bold-text">{{ $user['codigo'] }}</td>
                                <td style="text-align: left; padding-left: 15px;">{{ $user['nombre'] }}</td>
                                <td>{{ $user['horario_esperado'] }}</td>

                                {{-- Imprimimos el estado con su respectivo color (Verde, Rojo o Gris) --}}
                                <td class="bold-text" style="color: {{ $user['color'] }};">
                                    {{ mb_strtoupper($user['estado'], 'UTF-8') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
        <div class="text-center" style="margin-top: 40px !important; font-size: 9pt;">
            <div class="text-center bold-text text-uppercase" style="text-align: center;">
                <p class="border-bottom border-dark " style="height: 100px; border-bottom:gray solid 1px;"></p>
                Firma Jefe Inmediato<br>
                Fuente: CUCSH. Secretaria Administrativa, Coordinación de Personal. <br>
                Fecha: {{ $fechaDia }}
            </div>
        </div>
    </main>
</body>

</html>
