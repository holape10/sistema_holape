<?php
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Detallado_Jornadas_" . date('d-m-Y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<meta charset="utf-8">

<table border="1" style="border-collapse: collapse; width: 100%; text-align: center;">
    <thead>
        <tr>
            <th colspan="8" style="background-color: #0056b3; color: #ffffff; font-size: 18px; padding: 10px;">
                REPORTE DETALLADO DE TIEMPOS Y JORNADAS LABORALES
            </th>
        </tr>
        <tr>
            <th colspan="8" style="background-color: #e9ecef; font-weight: bold; text-align: left; padding: 5px;">
                Filtro: Desde {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} Hasta {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}
            </th>
        </tr>
        <tr style="background-color: #343a40; color: #ffffff; font-weight: bold;">
            <th>FECHA</th>
            <th>DNI</th>
            <th>COLABORADOR</th>
            <th>TURNO</th>
            <th>TIEMPO LABORADO EFECTIVO</th>
            <th>TARDANZA ACUMULADA</th>
            <th>JUSTIFICACIÓN / MOTIVO</th>
            <th>ESTADO DE JORNADA</th>
        </tr>
    </thead>
    <tbody>
        @forelse($asistencias as $asistencia)
            <tr>
                <td>{{ \Carbon\Carbon::parse($asistencia->date)->format('d/m/Y') }}</td>
                <td>{{ $asistencia->emp_num_doc }}</td>
                <td style="text-align: left;">{{ $asistencia->emp_nom }} {{ $asistencia->emp_ape_pat }}</td>
                <td>{{ $asistencia->codigo ?? 'Sin Asignar' }}</td>
                <td style="color: green; font-weight: bold;">{{ $asistencia->tiempo_laborado }}</td>
                <td style="color: {{ $asistencia->tardanza_texto != '0 min' ? 'red' : 'black' }}; font-weight: bold;">
                    {{ $asistencia->tardanza_texto }}
                </td>
                <td style="text-align: left;">
                    @if(!empty($asistencia->autorizado_por))
                        <strong>Auth: {{ $asistencia->autorizado_por }}</strong> - {{ $asistencia->motivo_tardanza }}
                    @else
                        --
                    @endif
                </td>
                <td>{{ $asistencia->estado_jornada }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="8">No hay registros para este filtro.</td>
            </tr>
        @endforelse
    </tbody>
</table>