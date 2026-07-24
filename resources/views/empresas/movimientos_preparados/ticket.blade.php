<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket Kardex</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Courier New', Courier, monospace; }
        /* Ancho exacto para ticketera térmica de 80mm */
        body { width: 80mm; margin: 0 auto; padding: 5px; font-size: 11px; color: #000; }
        .text-center { text-align: center; }
        .bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-3 { margin-top: 15px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 4px 1px; text-align: left; vertical-align: top; }
        th { border-bottom: 1px dashed #000; border-top: 1px dashed #000; }
        td { border-bottom: 1px dotted #ccc; }
        
        /* Ajuste para que imprima sin márgenes blancos por defecto en el navegador */
        @media print {
            body { width: 80mm; }
            @page { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center bold mb-1" style="font-size: 16px;">SISTEMA</div>
    <div class="text-center mb-1">Historial de Preparados</div>
    @if($fecha_inicio == $fecha_fin)
        <div class="text-center mb-2">Fecha: {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }}</div>
    @else
        <div class="text-center mb-2">Del {{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m') }} al {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</div>
    @endif
    
    <table>
        <thead>
            <tr>
                <th width="15%">Fecha Hora</th>
                <th width="50%">Prod/Usuario</th>
                <th width="15%" class="text-center">Cant</th>
                <th width="20%" class="text-center">Stk</th>
            </tr>
        </thead>
        <tbody>
            @forelse($movimientos as $mov)
            <tr>
                <td>{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m H:i') }}</td>
                <td>
                    <span class="bold">{{ substr($mov->pronom, 0, 18) }}</span><br>
                    <small style="font-size: 9px;">👤 {{ substr($mov->nombre_usuario ?? 'SISTEMA', 0, 18) }}</small>
                </td>
                <td class="text-center bold">
                    {{ $mov->cantidad > 0 ? '+' : '' }}{{ rtrim(rtrim(number_format($mov->cantidad, 2), '0'), '.') }}
                </td>
                <td class="text-center">
                    {{ rtrim(rtrim(number_format($mov->stock_resultante, 2), '0'), '.') }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center mt-3">No hay registros</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="text-center mt-3 mb-2 text-muted">
        --- FIN DEL REPORTE ---<br>
        <small>Generado: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}</small>
    </div>
</body>
</html>