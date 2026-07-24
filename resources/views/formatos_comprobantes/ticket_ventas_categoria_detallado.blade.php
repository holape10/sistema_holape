<!DOCTYPE html>
<html>
<head>
    <style>
        * { font-family: 'Arial', sans-serif; font-size: 8pt; }
        .ticket { width: 75mm; max-width: 75mm; }
        .centered { text-align: center; }
        .right { text-align: right; }
        .cat-header { background: #eee; font-weight: bold; padding: 2px; margin-top: 10px; border: 1px solid #000; }
        table { width: 100%; border-collapse: collapse; }
        .border-bottom { border-bottom: 1px dashed black; }
        .total { font-weight: bold; font-size: 9pt; }
    </style>
</head>
<body onload="window.print();">
    <div class="ticket">
        <p class="centered">
            <strong>{{ $empresa->NomEmpresa }}</strong><br>
            RUC: {{ $empresa->IdEmpresa }}<br>
        </p>
        <p class="centered"><strong>DETALLE POR CATEGORÍA</strong></p>

        @foreach($productos as $categoria => $items)
            <div class="cat-header centered">{{ $categoria }}</div>
            <table>
                @foreach($items as $prod)
                <tr>
                    <td colspan="2">{{ $prod->producto }}</td>
                </tr>
                <tr class="border-bottom">
                    <td class="right">{{ number_format($prod->cantidad, 2) }} x {{ number_format($prod->total / ($prod->cantidad > 0 ? $prod->cantidad : 1), 2) }}</td>
                    <td class="right">S/ {{ number_format($prod->total, 2) }}</td>
                </tr>
                @endforeach
            </table>
        @endforeach

        <table style="margin-top: 15px;">
            <tr>
                <td class="right"><strong>TOTAL GENERAL:</strong></td>
                <td class="right total">S/ {{ number_format($total_general, 2) }}</td>
            </tr>
        </table>
        <p class="centered">Impreso: {{ date('d/m/Y H:i') }}</p>
    </div>
</body>
</html>