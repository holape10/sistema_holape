@php
    $leyendas = \MasterSoft\Attendance::getLeyendas();
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <table>
        <tr>
            <td colspan="5"><b>REPORTE TAREO DE ASISTENCIA - {{ strtoupper($empresa->NomEmpresa ?? 'EMPRESA') }}</b></td>
        </tr>
        <tr>
            <td colspan="5"><b>RUC:</b> {{ $empresa->IdEmpresa ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="5"><b>DIRECCIÓN:</b> {{ $empresa->DirEmpresa ?? '' }}</td>
        </tr>
        <tr>
            <td colspan="5"><b>Desde:</b> {{ $fecha_inicio }} <b>Hasta:</b> {{ $fecha_fin }}</td>
        </tr>
        <tr></tr>
    </table>

    <table border="1">
        <thead>
            <tr>
                <th rowspan="2" style="background-color: #343a40; color: #fff;">Nro.</th>
                <th rowspan="2" style="background-color: #343a40; color: #fff;">Nombres y Apellidos</th>
                <th rowspan="2" style="background-color: #343a40; color: #fff;">D.N.I.</th>
                
                @foreach($fechas as $f)
                    <th style="background-color: #343a40; color: #fff; width: 40px;">{{ $f['dia_letra'] }}</th>
                @endforeach
                
                <th colspan="{{ count($leyendas) }}" style="background-color: #6c757d; color: #fff;">RESUMEN</th>
            </tr>
            <tr>
                @foreach($fechas as $f)
                    <th style="background-color: #343a40; color: #fff;">{{ $f['dia_numero'] }}</th>
                @endforeach
                
                @foreach($leyendas as $key => $item)
                    <th style="background-color: {{ $item['bg'] }}; color: {{ $item['color'] }};">
                        {{ $item['texto'] }}
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($matriz as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row['empleado']->emp_nom }} {{ $row['empleado']->emp_ape_pat }}</td>
                <td>{{ $row['empleado']->emp_num_doc }}</td>
                
                @foreach($fechas as $f)
                    @php 
                        $letra = $row['dias'][$f['fecha_sql']];
                        $info = $leyendas[$letra] ?? null;
                        
                        $colorFondo = $info ? $info['bg'] : '#ffffff';
                        $colorTexto = $info ? $info['color'] : '#000000';
                        $textoMostrar = $info ? $info['texto'] : $letra;
                    @endphp
                    <td style="background-color: {{ $colorFondo }}; color: {{ $colorTexto }}; text-align: center; font-weight: bold;">
                        {{ $textoMostrar }}
                    </td>
                @endforeach
                
                @foreach($leyendas as $key => $item)
                    <td style="text-align: center; font-weight: bold; background-color: #f8f9fa; color: {{ $item['bg'] }};">
                        {{ $row['totales'][$key] ?? 0 }}
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>