<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Libro Diario - {{ $mes }}/{{ $anio }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 11px; margin: 0; padding: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #000; padding-bottom: 10px; }
        .header h2 { margin: 0; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { border-bottom: 1px solid #000; border-top: 1px solid #000; padding: 5px; text-align: left; }
        td { padding: 4px 5px; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .glosa-row { font-weight: bold; background-color: #f9f9f9; }
        .cuenta-codigo { width: 100px; text-align: center; }
        .totales { font-weight: bold; border-top: 2px solid #000; border-bottom: 2px solid #000; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print()" style="padding: 5px 15px; cursor: pointer;">🖨️ Imprimir / Guardar como PDF</button>
        <button onclick="window.close()" style="padding: 5px 15px; cursor: pointer;">Cerrar</button>
    </div>

    <div class="header">
        <h2>LIBRO DIARIO - FORMATO OFICIAL</h2>
        <p>PERIODO: {{ $mes }} / {{ $anio }}</p>
    </div>

    @php 
        $totalDebeGral = 0; 
        $totalHaberGral = 0; 
    @endphp

    <table>
        <thead>
            <tr>
                <th style="width: 80px;">FECHA</th>
                <th class="cuenta-codigo">CÓDIGO CTA.</th>
                <th>DENOMINACIÓN / GLOSA</th>
                <th class="text-right" style="width: 90px;">DEBE</th>
                <th class="text-right" style="width: 90px;">HABER</th>
            </tr>
        </thead>
        <tbody>
            @foreach($asientos as $asiento)
                <tr class="glosa-row">
                    <td>{{ date('d/m/Y', strtotime($asiento->fecha)) }}</td>
                    <td></td>
                    <td>{{ $asiento->glosa }}</td>
                    <td></td>
                    <td></td>
                </tr>
                
                @foreach($asiento->detalles as $det)
                    @php 
                        $totalDebeGral += $det->debe;
                        $totalHaberGral += $det->haber;
                    @endphp
                    <tr>
                        <td></td>
                        <td class="cuenta-codigo">{{ $det->cuenta->codigo }}</td>
                        <td>{{ $det->cuenta->nombre }}</td>
                        <td class="text-right">{{ $det->debe > 0 ? number_format($det->debe, 2) : '' }}</td>
                        <td class="text-right">{{ $det->haber > 0 ? number_format($det->haber, 2) : '' }}</td>
                    </tr>
                @endforeach
                
                {{-- Espaciador --}}
                <tr><td colspan="5" style="height: 5px;"></td></tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totales">
                <td colspan="3" class="text-right">TOTALES DEL PERIODO:</td>
                <td class="text-right">{{ number_format($totalDebeGral, 2) }}</td>
                <td class="text-right">{{ number_format($totalHaberGral, 2) }}</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>