<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Flujo de Pedidos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #334155; padding-bottom: 10px; }
        .titulo { font-size: 18px; font-weight: bold; text-transform: uppercase; }
        .info { margin-bottom: 20px; width: 100%; }
        .info td { padding: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th { background-color: #334155; color: white; padding: 8px; text-align: left; text-transform: uppercase; }
        td { border-bottom: 1px solid #e2e8f0; padding: 8px; }
        .fila-pico { background-color: #fef3c7; font-weight: bold; }
        .resaltado { color: #f59e0b; }
        .total-box { margin-top: 20px; text-align: right; font-size: 14px; font-weight: bold; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="header">
        <div class="titulo">Hola P - Reporte de Flujo de Pedidos</div>
        <div>Análisis de Ventas por Franja Horaria</div>
    </div>

    <table class="info">
        <tr>
            <td><strong>Desde:</strong> {{ $fec_ini }}</td>
            <td><strong>Sucursal:</strong> {{ $sucursal->nombre ?? 'Todas' }}</td>
        </tr>
        <tr>
            <td><strong>Hasta:</strong> {{ $fec_fin }}</td>
            <td><strong>Fecha Emisión:</strong> {{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Hora</th>
                <th>Cantidad de Pedidos</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data_reporte as $item)
                @if($item['cantidad'] > 0)
                <tr class="{{ $item['es_pico'] ? 'fila-pico' : '' }}">
                    <td>{{ $item['hora'] }}</td>
                    <td>{{ $item['cantidad'] }}</td>
                    <td>
                        @if($item['es_pico'])
                            <span class="resaltado">★ HORA PICO</span>
                        @else
                            -
                        @endif
                    </td>
                </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        Total General de Pedidos: {{ $total_pedidos }}
    </div>

    <div class="footer">
        Generado automáticamente por el Sistema de Gestión Hola P.
    </div>
</body>
</html>