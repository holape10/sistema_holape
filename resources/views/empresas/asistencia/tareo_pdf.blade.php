@php
    $leyendas = \MasterSoft\Attendance::getLeyendas();
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Tareo </title>
    <style>
        @page {
            margin: 15px;
            size: A4 landscape; /* Hoja echada para que entren los 31 días */
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 9px;
            color: #333;
        }
        .header-title {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header-subtitle {
            text-align: center;
            font-size: 10px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #444;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #343a40;
            color: #ffffff;
        }
        .text-left {
            text-align: left;
            padding-left: 5px;
        }
        .resumen-th {
            font-size: 8px;
        }
        @media print {
            body { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body>

    <div class="header-title">REPORTE TAREO DE ASISTENCIA - {{ $empresa->NomEmpresa ?? 'Empresa' }} <br> RUC: {{ $empresa->IdEmpresa ?? '' }}</div>
    <div class="header-subtitle">
        <strong>Desde:</strong> {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} 
        <strong>Hasta:</strong> {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
    </div>

    <table>
        <thead>
            <tr>
                <th rowspan="2" style="width: 2%;">Nro</th>
                <th rowspan="2" class="text-left" style="width: 15%;">Nombres y Apellidos</th>
                <th rowspan="2" style="width: 6%;">D.N.I.</th>
                
                @foreach($fechas as $f)
                    <th style="width: 2%;">{{ $f['dia_letra'] }}</th>
                @endforeach
                
                <th colspan="{{ count($leyendas) }}" style="background-color: #6c757d;">RESUMEN</th>
            </tr>
            <tr>
                @foreach($fechas as $f)
                    <th>{{ $f['dia_numero'] }}</th>
                @endforeach
                
                @foreach($leyendas as $key => $item)
                    <th class="resumen-th" style="background-color: {{ $item['bg'] }}; color: {{ $item['color'] }};">
                        {{ $item['texto'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matriz as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left" style="font-weight: bold; font-size: 8px;">{{ $row['empleado']->emp_nom }} {{ $row['empleado']->emp_ape_pat }}</td>
                <td>{{ $row['empleado']->emp_num_doc }}</td>
                
                @foreach($fechas as $f)
                    @php 
                        $letra = $row['dias'][$f['fecha_sql']];
                        $info = $leyendas[$letra] ?? null;
                        
                        $colorFondo = $info ? $info['bg'] : '#ffffff';
                        $colorTexto = $info ? $info['color'] : '#000000';
                        $textoMostrar = $info ? $info['texto'] : $letra;
                    @endphp
                    <td style="background-color: {{ $colorFondo }}; color: {{ $colorTexto }}; font-weight: bold;">
                        {{ $textoMostrar }}
                    </td>
                @endforeach
                
                @foreach($leyendas as $key => $item)
                    <td style="color: {{ $item['bg'] }}; font-weight: bold;">
                        {{ $row['totales'][$key] ?? 0 }}
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    <script>
        // Esto hace que la ventana de impresión se abra sola al cargar
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>