<!DOCTYPE html>
<html>
<head>
    <style>
        * { font-family: 'Arial', sans-serif; font-size: 8pt; }
        .ticket { width: 75mm; max-width: 75mm; }
        .centered { text-align: center; }
        .right { text-align: right; }
        table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .border-top { border-top: 1px dashed black; }
        .border-bottom { border-bottom: 1px dashed black; }
        .total { font-weight: bold; font-size: 9pt; }
        .header-section { background: #eee; font-weight: bold; padding: 2px; }
    </style>
</head>
<body onload="window.print();">
    <div class="ticket">
        <p class="centered">
            <strong>{{ $empresa->NomEmpresa }}</strong><br>
            RUC: {{ $empresa->IdEmpresa }}<br>
            
        </p>

        <p class="centered">
            <strong>REPORTE DETALLADO</strong><br>
            {{ date('d/m/Y', strtotime($fecin)) }} al {{ date('d/m/Y', strtotime($fecfin)) }}
        </p>

        <div class="header-section centered">VENTAS (BOLETAS/FACTURAS)</div>
        <table>
            <thead>
                <tr class="border-bottom">
                    <th>DOC / PRODUCTO</th>
                    <th class="right">CANT.</th>
                    <th class="right">TOTAL</th>
                </tr>
            </thead>
            <tbody>
                @foreach($ventas as $v)
                <tr>
                    <td colspan="3" style="padding-top: 5px;">
                        <strong>{{ $v->serdoc }}-{{ $v->numdoc }}</strong> | {{ date('d/m/y', strtotime($v->ccafem)) }}<br>
                        {{ $v->cdedes }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="right">{{ number_format($v->cantidad * $v->cpe_det_factor, 2) }}</td>
                    <td class="right">{{ number_format($v->cdevve, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        @if(count($notas) > 0)
        <div class="header-section centered" style="margin-top: 10px;">NOTAS DE CRÉDITO</div>
        <table>
            <tbody>
                @foreach($notas as $n)
                <tr>
                    <td colspan="3" style="padding-top: 5px;">
                        <strong>{{ $n->serdoc }}-{{ $n->numdoc }}</strong><br>
                        {{ $n->cdedes }}
                    </td>
                </tr>
                <tr>
                    <td></td>
                    <td class="right">-{{ number_format($n->cantidad * $n->cpe_det_factor, 2) }}</td>
                    <td class="right">-{{ number_format($n->cdevve, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        <table style="margin-top: 10px;">
            <tr class="border-top">
                <td class="right"><strong>TOTAL NETO:</strong></td>
                <td class="right total">S/ {{ number_format($total_final, 2) }}</td>
            </tr>
        </table>

        <p class="centered" style="margin-top: 15px;">
            -----------------------------------<br>
            Impreso el {{ date('d/m/Y H:i:s') }}
        </p>
    </div>
</body>
</html>