<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket de Reserva</title>
    <style>
        @page { margin: 0; }
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente de ticketera */
            width: 80mm;
            margin: 0;
            padding: 5mm;
            font-size: 12px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-2 { margin-bottom: 5px; }
        .border-bottom { border-bottom: 1px dashed #000; padding-bottom: 5px; margin-bottom: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; }
        
        /* Ocultar elementos en la impresión real */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    
    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 5px 10px;">Imprimir</button>
        <a href="{{ route('reservas.create') }}" style="padding: 5px 10px;">Volver</a>
    </div>

    <div class="text-center font-bold mb-2" style="font-size: 16px;">
        {{ $reserva->NomEmpresa }}
    </div>
    <div class="text-center border-bottom">
        TICKET DE RESERVA #{{ str_pad($reserva->res_id, 5, '0', STR_PAD_LEFT) }}
    </div>

    <div class="mb-2 border-bottom">
        <div><span class="font-bold">Fecha Res:</span> {{ date('d/m/Y', strtotime($reserva->fecha_reserva)) }}</div>
        <div><span class="font-bold">Inicio:</span> {{ date('H:i', strtotime($reserva->hora_inicio)) }}</div>
        <div><span class="font-bold">Fin:</span> {{ date('H:i', strtotime($reserva->hora_fin)) }}</div> <div><span class="font-bold">Personas:</span> {{ $reserva->cantidad_personas }}</div>
        <div><span class="font-bold">Zona/Mesa:</span> {{ $reserva->pis_nom }} / {{ $reserva->mes_nom }}</div>
    </div>

    <div class="mb-2 border-bottom">
        <div><span class="font-bold">Cliente:</span> {{ substr($reserva->clinom, 0, 25) }}</div>
        @if($reserva->clinum)
            <div><span class="font-bold">Doc:</span> {{ $reserva->clinum }}</div>
        @endif
    </div>

    <div class="mb-2 border-bottom">
        <table>
            <thead>
                <tr>
                    <th style="text-align: left;">Desc</th>
                    <th style="text-align: center;">Cant</th>
                    <th style="text-align: right;">P.Unit</th> <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detalles as $det)
                <tr>
                    <td>
                        {{ substr($det->pronom, 0, 15) }}
                        @if($det->observacion_producto)
                            <br><small>*{{ $det->observacion_producto }}</small>
                        @endif
                    </td>
                    <td class="text-center">{{ number_format($det->cantidad, 0) }}</td>
                    <td class="text-right">S/ {{ number_format($det->precio_unitario, 2) }}</td> <td class="text-right">S/ {{ number_format($det->subtotal, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="text-right font-bold" style="font-size: 14px; margin-bottom: 10px;">
        TOTAL ESTIMADO: S/ {{ number_format($reserva->total, 2) }}
    </div>

    @if($reserva->observacion)
    <div class="border-bottom" style="font-size: 11px;">
        <span class="font-bold">Nota:</span> {{ $reserva->observacion }}
    </div>
    @endif

    <div class="text-center" style="margin-top: 15px; font-size: 10px;">
        Powered By www.holape.app
        <br>
        Documento referencial de reserva.
    </div>

</body>
</html>