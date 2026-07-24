<!DOCTYPE html>
<html>
<head>
    <style>
        * { font-family: 'Arial', sans-serif; font-size: 9pt; }
        .ticket { width: 75mm; max-width: 75mm; }
        .centered { text-align: center; }
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
            <strong>VENTAS POR CATEGORÍA</strong><br>
            {{ date('d/m/Y', strtotime($fecin)) }} al {{ date('d/m/Y', strtotime($fecfin)) }}
        </p>
        <table>
            <thead>
                <tr class="border-top">
                    <th>CATEGORÍA</th>
                    <th class="right">CANT.</th>
                    <th class="right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @php $gran_total = 0; @endphp
                @foreach($ventas_categoria as $item)
                <tr>
                    <td>{{ $item->categoria_nombre }}</td>
                    <td class="right">{{ number_format($item->cantidad, 2) }}</td>
                    <td class="right">{{ number_format($item->total, 2) }}</td>
                </tr>
                @php $gran_total += $item->total; @endphp
                @endforeach
                <tr class="border-top">
                    <td colspan="2" class="right"><strong>TOTAL:</strong></td>
                    <td class="right total">{{ number_format($gran_total, 2) }}</td>
                </tr>
            </tbody>
        </table>
        <p class="centered">Generado: {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>