<?php
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Mermas_Diario_" . $hoy . ".xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<!DOCTYPE html>
<html>
<head><meta charset="utf-8"></head>
<body>
    <h2>Reporte de Mermas del Día: {{ date('d/m/Y', strtotime($hoy)) }}</h2>
    <table border="1">
        <thead>
            <tr style="background-color: #d9534f; color: #fff;">
                <th>ID</th>
                <th>Fecha Hora</th>
                <th>Producto / Insumo</th>
                <th>Cant. Ingresada</th>
                <th>Unidad</th>
                <th>Descuento Kardex Base</th>
                <th>Motivo</th>
                <th>Costo Unitario (S/)</th>
                <th>Pérdida Total (S/)</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @php $suma_perdida = 0; @endphp
            @foreach($mermas as $m)
            <tr>
                <td>{{ $m->id }}</td>
                <td>{{ $m->fecha_registro }}</td>
                <td>{{ $m->pronom }}</td>
                <td>{{ $m->cantidad }}</td>
                <td>{{ $m->tipo_unidad }}</td>
                <td>{{ $m->cantidad_kardex }}</td>
                <td>{{ $m->motivo }}</td>
                <td>{{ $m->costo_unitario }}</td>
                <td>{{ $m->costo_total }}</td>
                <td>{{ $m->observacion }}</td>
            </tr>
            @php $suma_perdida += $m->costo_total; @endphp
            @endforeach
            <tr>
                <td colspan="8" style="text-align: right; font-weight: bold;">TOTAL PÉRDIDA DEL DÍA:</td>
                <td style="font-weight: bold; color: red;">{{ number_format($suma_perdida, 2) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>