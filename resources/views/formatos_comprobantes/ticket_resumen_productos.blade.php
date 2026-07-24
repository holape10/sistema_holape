<!DOCTYPE html>
<html>
<head>
    <style>
        * { font-family: 'Arial', sans-serif; font-size: 9pt; }
        .ticket { width: 75mm; max-width: 75mm; }
        .centered { text-align: center; align-content: center; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; }
        .border-top { border-top: 1px dashed black; }
        .total { font-weight: bold; font-size: 10pt; }
    </style>
</head>
<body onload="window.print();">
    <div class="ticket">
        <p class="centered">
            <strong>{{ $empresa->NomEmpresa }}</strong><br>
            RUC: {{ $empresa->IdEmpresa }}<br>
            
        </p>

        <p class="centered">
            <strong>RESUMEN DE VENTAS</strong><br>
            Desde: {{ date('d/m/Y', strtotime($fecin)) }}<br>
            Hasta: {{ date('d/m/Y', strtotime($fecfin)) }}
        </p>

        <table>
            <thead>
                <tr class="border-top">
                    <th>DESCRIPCIÓN</th>
                    <th class="right">CANT.</th>
                    <th class="right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php $total_general = 0; @endphp
                @foreach($vent_res_prod as $item)
                <tr>
                    <td colspan="3" style="padding-top: 5px;">
                        <small>{{ date('d/m/Y', strtotime($item->dia)) }} - {{ $item->codigo }}</small><br>
                        {{ $item->producto }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="right">{{ number_format($item->cantidad, 2) }}</td>
                    <td class="right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @php $total_general += $item->total; @endphp
                @endforeach
                
                <tr class="border-top">
                    <td colspan="2" class="right"><strong>TOTAL GENERAL:</strong></td>
                    <td class="right total">{{ number_format($total_general, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <p class="centered" style="margin-top: 20px;">
            -----------------------------------<br>
            Reporte generado el {{ date('d/m/Y H:i:s') }}
        </p>
    </div>
</body>
</html>