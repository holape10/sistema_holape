<?php
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Matriz_Turnos_" . $fechaInicio . ".xls");
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<table border="1" cellpadding="5" cellspacing="0">
    <thead>
        <tr>
            <th colspan="8" style="background-color: #007bff; color: white; font-size: 16px; font-weight: bold; text-align: center;">
                MATRIZ DE TURNOS - SEMANA DEL {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <th style="background-color: #343a40; color: white; font-weight: bold;">COLABORADOR</th>
            @foreach($fechas as $fecha)
                <th style="background-color: #343a40; color: white; font-weight: bold;">
                    {{ strtoupper($fecha['dia_nombre']) }}<br>{{ $fecha['vista'] }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach($empleados as $emp)
            <tr>
                <td style="font-weight: bold;">{{ $emp->emp_nom }} {{ $emp->emp_ape_pat }} {{ $emp->emp_ape_mat }}</td>
                
                @foreach($fechas as $fecha)
                    @php
                        $turnoGuardadoId = isset($matriz[$emp->emp_id][$fecha['sql']]) ? $matriz[$emp->emp_id][$fecha['sql']] : null;
                        $turnoObj = $turnos->where('id', $turnoGuardadoId)->first();
                        $codigoPintar = $turnoObj ? $turnoObj->codigo : 'LIBRE';
                        $colorFondo = $turnoObj ? '#d4edda' : '#f8d7da'; // Verde suave si hay turno, rojo suave si es LIBRE
                    @endphp
                    <td style="text-align: center; background-color: {{ $colorFondo }};">
                        {{ $codigoPintar }}
                    </td>
                @endforeach
            </tr>
        @endforeach
    </tbody>
</table>