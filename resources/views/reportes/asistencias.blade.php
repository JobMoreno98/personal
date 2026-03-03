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

        /* --- NUEVOS ESTILOS PARA LA TABLA --- */
        .employee-card {
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-bottom: 15px !important;
            page-break-inside: avoid;
            /* Evita que la tabla se corte entre dos páginas */
            overflow: hidden;
        }

        .employee-header {
            background-color: #f4f4f4;
            color: #444;
            border-bottom: 1px solid #ddd;
            padding: 5px;
            font-size: 10pt;
            text-align: center;
        }

        .table-custom {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
        }

        .table-custom th {
            background-color: #f4f4f4;
            color: #444;
            border-bottom: 1px solid #ddd;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }

        .table-custom td {
            border-bottom: 1px solid #eee;
            padding: 8px 5px;
            text-align: center;
        }

        .table-custom tr:last-child td {
            border-bottom: none;
            /* Quita la línea inferior del último registro */
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
            <p style="line-height:.8;overflow-wrap: break-word;" class="bold-text text-center">
                REPORTE DE REGISTROS DE {{ $departamento->nombre }}, <br>
                PERIODO DEL {{ $periodo[0] }} AL {{ $periodo[1] }}
            </p>
        </div>

        <div>
            @foreach ($usuarios as $key => $value)
                <div class="employee-card">
                    {{-- Encabezado del empleado con nombre y código --}}
                    <div class="employee-header border-bottom">
                        <span class="bold-text text-center">{{ $value['codigo'] }} - {{ $value['nombre'] }} </span>
                    </div>

                    <table class="table-custom">
                        <thead>
                            <tr>
                                <th>Hora entrada</th>
                                <th>Tipo entrada</th>
                                <th>Hora salida</th>
                                <th>Tipo Salida</th>
                                <th>Tiempo trabajado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($value['dias'] as $item => $element)
                                <tr>
                                    <td>{{ $element['hora_entrada'] }}</td>
                                    <td>{{ $element['tipo_entrada'] }}</td>

                                    <td
                                        style="{{ $element['hora_salida'] === 'SIN CHECAR' ? 'color: red; font-weight: bold;' : '' }}">
                                        {{ $element['hora_salida'] }}
                                    </td>

                                    <td>{{ $element['tipo_salida'] ?? '-' }}</td>
                                    <td>{{ $element['tiempo_total'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    {{-- Ajusté el colspan a 5 porque quitamos la columna de código --}}
                                    @if ($value['es_laboral'])
                                        <td colspan="5" style="color: #dc3545; font-weight: bold; padding: 10px;">
                                            FALTA </td>
                                    @else
                                        <td colspan="5" style="color: #6c757d; font-style: italic; padding: 10px;">
                                            Día de descanso</td>
                                    @endif
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>

        <br>
        <div class="text-center" style="margin-top: 20px !important; font-size: 9pt;">
            <b> Fuente:</b> <span class="text-uppercase"> CUCSH. Secretaria Administrativa, Coordinación de Personal
            </span>
            <br>
            <b> Reporte generado:</b> {{ $fechaDia }}
        </div>
    </main>
</body>

</html>
