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
                REPORTE DE REGISTROS DE {{ $departamento->nombre }}, <br>
                PERDIODO DEL {{ $periodo[0] }} AL {{ $periodo[1] }}
            </p>
        </div>
        <div>
            @foreach ($usuarios as $key => $value)
                <div class="border my-2">
                    <div class="text-center border-bottom py-1 my-0">
                        <b>{{ $value['nombre'] }}</b>
                    </div>
                    <table class="m-auto px-2 text-center" style="width:100%">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Hora entarda</th>
                                <th>Tipo entarda</th>
                                <th>Hora salida</th>
                                <th>Tipo Salida</th>
                                <th>Tiempo trabajado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($value['dias'] as $item => $element)
                                <tr>
                                    <td>{{ $element['codigo'] }}</td>
                                    <td>{{ $element['hora_entrada'] }}</td>
                                    <td>{{ $element['tipo_entrada'] }}</td>
                                    <td>{{ $element['hora_salida'] }}</td>
                                    <td>{{ $element['tipo_salida'] }}</td>
                                    <td>{{ $element['tiempo_total'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td>{{ $value['codigo'] }}</td>
                                    <td>{{ 'Sin registro' }}</td>
                                    <td>{{ 'Sin registro' }}</td>
                                    <td>{{ 'Sin registro' }}</td>
                                    <td>{{ 'Sin registro' }}</td>
                                    <td>{{ 'Sin registro' }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endforeach
        </div>
        <div class="text-center">
            <b> Fuente:</b> <span class="text-uppercase"> CUCSH. Secretaria Administrativa, Coordinación de Personal
            </span>
            <br>
            <b> Reporte realizado:</b> {{ $fechaDia }}
        </div>
    </main>
</body>

</html>
