<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket de Merma</title>
    <style>
        @page { margin: 0; }
        body { font-family: 'Courier New', Courier, monospace; font-size: 14px; width: 80mm; padding: 5mm; margin: 0 auto; color: #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .linea { border-top: 1px dashed #000; margin: 5px 0; }
        .bold { font-weight: bold; }
    </style>
</head>
<body onload="window.print();">
    <div class="text-center">
        <h3 style="margin: 0;">REPORTE DE MERMA</h3>
        <p style="margin: 0;">Interno</p>
    </div>
    <div class="linea"></div>
    <p style="margin: 2px 0;"><strong>Fecha:</strong> {{ date('d/m/Y H:i', strtotime($merma->fecha_registro)) }}</p>
    <p style="margin: 2px 0;"><strong>Ticket N°:</strong> {{ str_pad($merma->id, 6, '0', STR_PAD_LEFT) }}</p>
    <div class="linea"></div>
    <p class="bold" style="margin: 5px 0; font-size: 16px;">{{ $merma->pronom }}</p>
    
    <table width="100%">
        <tr><td>Cant. Ingresada:</td><td class="text-right">{{ $merma->cantidad }} ({{ $merma->tipo_unidad }})</td></tr>
        <tr><td>Baja en Kardex:</td><td class="text-right">{{ $merma->cantidad_kardex }} UND/KG</td></tr>
        <tr><td>Motivo:</td><td class="text-right">{{ $merma->motivo }}</td></tr>
    </table>
    <div class="linea"></div>
    <table width="100%">
        <tr><td class="bold">PÉRDIDA CALCULADA:</td><td class="text-right bold">S/ {{ number_format($merma->costo_total, 2) }}</td></tr>
    </table>
    <div class="linea"></div>
    <p style="font-size: 12px; margin-top: 15px;">Obs: {{ $merma->observacion ?? 'Ninguna' }}</p>
    
    <br><br><br>
    <div class="text-center">
        <p>_______________________</p>
        <p style="font-size: 12px;">Firma Responsable</p>
    </div>
</body>
</html>