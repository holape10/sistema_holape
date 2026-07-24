<!DOCTYPE html>
<html>
<head>
    <style>
        * { font-family: 'Arial', sans-serif; font-size: 8pt; color: #000; }
        .ticket { width: 75mm; max-width: 75mm; margin: 0 auto; }
        .centered { text-align: center; }
        .right { text-align: right; }
        .border-dashed { border-top: 1px dashed #000; margin: 5px 0; }
        .cat-header { font-weight: bold; text-decoration: underline; margin-top: 8px; font-size: 9pt; }
        table { width: 100%; border-collapse: collapse; }
        .total-final { font-weight: bold; font-size: 10pt; margin-top: 10px; }
    </style>
</head>
<body onload="window.print();">
    <div class="ticket">
        <p class="centered">
            <strong>{{ $empresa->NomEmpresa }}</strong><br>
            RUC: {{ $empresa->IdEmpresa }}<br>
            
        </p>

        <p class="centered">
            <strong>RANKING TOP 5 POR CATEGORÍA</strong><br>
            {{ date('d/m/Y', strtotime($fecin)) }} al {{ date('d/m/Y', strtotime($fecfin)) }}
        </p>

        @php $suma_final = 0; @endphp

        @foreach($reporte_top as $datos)
            <div class="cat-header">{{ $datos['cat_nom'] }}</div>
            <table>
                <thead>
                    <tr style="border-bottom: 0.5px solid #000;">
                        <th align="left">PRODUCTO</th>
                        <th class="right">CANT.</th>
                        <th class="right">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($datos['productos'] as $index => $p)
                    <tr>
                        <td colspan="3" style="padding-top: 3px;">
                            {{ $index + 1 }}. {{ $p->nombre_producto }}
                        </td>
                    </tr>
                    <tr>
                        <td><small>Cod: {{ $p->codigo_prod }}</small></td>
                        <td class="right">{{ number_format($p->cantidad_total, 2) }}</td>
                        <td class="right">{{ number_format($p->monto_total, 2) }}</td>
                    </tr>
                    @php $suma_final += $p->monto_total; @endphp
                    @endforeach
                </tbody>
            </table>
        @endforeach

        <div class="border-dashed"></div>
        <table class="total-final">
            <tr>
                <td class="right">SUMA TOTAL TOP 5:</td>
                <td class="right">S/ {{ number_format($suma_final, 2) }}</td>
            </tr>
        </table>

        <p class="centered" style="margin-top: 15px; font-size: 7pt;">
            -----------------------------------<br>
            Reporte generado el {{ date('d/m/Y H:i:s') }}<br>
            <strong>Sistema Hola P</strong>
        </p>
    </div>
</body>
</html>