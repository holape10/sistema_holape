<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Mermas - {{ date('d/m/Y', strtotime($hoy)) }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .cabecera { text-align: center; margin-bottom: 20px; }
        .cabecera h2 { margin: 0; color: #d9534f; }
        .cabecera p { margin: 5px 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #fee; }
        .total-amount { color: #d9534f; font-size: 14px; }
    </style>
</head>
<body>
    <div class="cabecera">
        <h2>REPORTE DIARIO DE MERMAS Y DESPERDICIOS</h2>
        <p><strong>Fecha de Consulta:</strong> {{ date('d/m/Y', strtotime($hoy)) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">#ID</th>
                <th width="12%">Hora</th>
                <th width="30%">Producto / Insumo</th>
                <th width="10%">Cantidad</th>
                <th width="18%">Motivo</th>
                <th width="10%">Und. Medida</th>
                <th width="15%" class="text-right">Pérdida (S/)</th>
            </tr>
        </thead>
        <tbody>
            @php $suma_perdida = 0; @endphp
            @foreach($mermas as $m)
            <tr>
                <td class="text-center">{{ $m->id }}</td>
                <td>{{ date('H:i', strtotime($m->fecha_registro)) }}</td>
                <td>{{ $m->pronom }}</td>
                <td class="text-center">{{ $m->cantidad }}</td>
                <td>{{ $m->motivo }}</td>
                <td class="text-center">{{ ucfirst($m->tipo_unidad) }}</td>
                <td class="text-right">{{ number_format($m->costo_total, 2) }}</td>
            </tr>
            @php $suma_perdida += $m->costo_total; @endphp
            @endforeach
            <tr class="total-row">
                <td colspan="6" class="text-right">TOTAL PÉRDIDA DEL DÍA:</td>
                <td class="text-right total-amount">S/ {{ number_format($suma_perdida, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>