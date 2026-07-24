<div class="table-responsive">
    <table class="table table-bordered table-sm" style="width:100%; font-size:9pt;">
        <thead>
            <tr>
                <th colspan="5" style="text-align:center; background-color:#337ab7; color:#fff;">
                    <strong>TOP 5 PRODUCTOS MÁS VENDIDOS POR CATEGORÍA</strong><br>
                    Desde: {{$fec_ini}} Hasta: {{$fec_fin}}
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($reporte_top_categoria as $id_cat => $datos)
                <tr style="background-color: #f4f4f4;">
                    <td colspan="5" style="padding-left: 10px;">
                        <strong>CATEGORÍA: {{ $datos['cat_nom'] }}</strong>
                    </td>
                </tr>
                <tr style="font-weight: bold; text-align: center; background-color: #ffffff;">
                    <td style="width: 5%;">#</td>
                    <td style="width: 15%;">CÓDIGO</td>
                    <td style="width: 50%;">PRODUCTO</td>
                    <td style="width: 15%;">CANTIDAD</td>
                    <td style="width: 15%;">TOTAL (S/.)</td>
                </tr>
                
                @php $i = 1; @endphp
                @foreach($datos['productos'] as $prod)
                <tr>
                    <td style="text-align: center;">{{ $i++ }}</td>
                    <td style="text-align: center;">{{ $prod->codigo }}</td>
                    <td>{{ $prod->producto }}</td>
                    <td style="text-align: right;">{{ number_format($prod->cantidad_total, 2) }}</td>
                    <td style="text-align: right;">{{ number_format($prod->monto_total, 2) }}</td>
                </tr>
                @endforeach

                <tr><td colspan="5" style="border:none; height: 10px;"></td></tr>
            @endforeach
        </tbody>
    </table>
</div>