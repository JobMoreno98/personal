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
            font-family: "Montserrat";
            font-style: normal;
            font-weight: 400;
            src: url("../fonts/Montserrat-Regular.ttf") format("truetype");
        }

        @font-face {
            font-family: "Montserrat";
            font-style: normal;
            font-weight: 700;
            src: url("../fonts/Montserrat-Bold.ttf") format("truetype");
        }

        body {
            font-family: 'Montserrat';
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

        .titulo {
            font-family: "Times New Roman", "Montserrat", serif;
            font-size: 11pt;
        }

        .text-uppercase {
            text-transform: uppercase;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin-top: 20px;
        }

        .table-custom th {
            color: #000;
            border: 1px solid grey;
            padding: 10px 5px;
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

        .employee-info {
            background-color: #f4f4f4;
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        #footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            border-top: 1px solid gray;
            /* Borde superior */
            padding-top: 5px;
            height: 1.4cm;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.css">
</head>

<body>
    <footer id="footer">

    </footer>

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

    <main style="clear: both;">
        <div style="width:100%;margin-top:.5cm;">
            <p style="line-height:.8;overflow-wrap: break-word; text-align: center;"
                class="bold-text text-center text-uppercase">
                REPORTE DE INASISTENCIAS POR TRABAJADOR <br>
                PERIODO: {{ $rango }}
            </p>
        </div>

        <div class="employee-info text-center">
            <span class="bold-text" style="font-size: 11pt;">{{ mb_strtoupper($usuario->nombre, 'UTF-8') }}</span> <br>
            CÓDIGO: {{ $usuario->usuario }}
        </div>

        <div>
            @if ($faltas->isEmpty())
                <div
                    style="text-align: center; margin-top: 40px; color: #198754; font-weight: bold; font-size: 11pt; padding: 20px; border: 1px dashed #198754; border-radius: 5px;">
                    El trabajador no presenta faltas injustificadas en el periodo seleccionado.
                </div>
            @else
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th style="width: 10%;">#</th>
                            <th style="width: 30%;">Fecha de Ausencia</th>
                            <th style="width: 30%;">Día de la Semana</th>
                            <th style="width: 30%;">Horario Esperado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($faltas as $index => $falta)
                            <tr>
                                <td class="bold-text">{{ $index + 1 }}</td>
                                <td class="bold-text">{{ $falta['fecha'] }}</td>
                                <td>{{ $falta['dia_semana'] }}</td>
                                <td>{{ $falta['horario_esperado'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div style="margin-top: 15px; text-align: right; font-weight: bold;">
                    Total de faltas en el periodo: <span style="color: #dc3545;">{{ $faltas->count() }}</span>
                </div>
            @endif
        </div>

        <br>
        <div class="text-center" style="margin-top: 40px !important; font-size: 9pt;">
            <div class="text-center bold-text text-uppercase" style="text-align: center;">
                <p class="border-bottom border-dark " style="height: 100px; border-bottom:gray solid 1px;"></p>
                Firma {{ $usuario->nombre }} ({{ $usuario->usuario }}) <br>
                Fuente: CUCSH. Secretaria Administrativa, Coordinación de Personal. <br>
                Fecha: {{ $fechaDia }}
            </div>
        </div>
    </main>
</body>

</html>
