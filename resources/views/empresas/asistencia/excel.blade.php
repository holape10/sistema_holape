<?php
// Forzamos las cabeceras para que el navegador lo detecte como archivo Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Asistencia_" . $fecha . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<table>
    <thead>
        <tr>
            <th style="background-color: #007bff; color: white; font-weight: bold;">Fecha</th>
            <th style="background-color: #007bff; color: white; font-weight: bold;">DNI</th>
            <th style="background-color: #007bff; color: white; font-weight: bold;">Empleado</th>
            <th style="background-color: #007bff; color: white; font-weight: bold;">Hora Entrada</th>
            <th style="background-color: #007bff; color: white; font-weight: bold;">Hora Salida</th>
        </tr>
    </thead>
    <tbody>
        @foreach($asistencias as $asistencia)
        <tr>
            <td>{{ $asistencia->date }}</td>
            <td>{{ $asistencia->emp_num_doc }}</td>
            <td>{{ $asistencia->emp_nom }} {{ $asistencia->emp_ape_pat }}</td>
            <td>{{ $asistencia->check_in ? \Carbon\Carbon::parse($asistencia->check_in)->format('H:i:s') : '--' }}</td>
            <td>{{ $asistencia->check_out ? \Carbon\Carbon::parse($asistencia->check_out)->format('H:i:s') : 'Sin marcar' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>