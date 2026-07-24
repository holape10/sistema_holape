<table id="dtHorizontalExample" class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:9pt;">
    <thead>
        <tr>
            <th colspan="6" style="text-align:center;background-color:#337ab7;color:#fff;font-weight:bold;">
                RANKING: TOP 5 PRODUCTOS MÁS VENDIDOS DESDE {{$fec_ini}} HASTA {{$fec_fin}}
            </th>
        </tr>
        <tr>
            <th style="width:5%; text-align:center; background-color:#f4f4f4;">#</th>
            <th style="width:15%; text-align:center; background-color:#f4f4f4;">CÓDIGO</th>
            <th style="width:40%; text-align:center; background-color:#f4f4f4;">PRODUCTO / DESCRIPCIÓN</th>
            <th style="width:15%; text-align:center; background-color:#f4f4f4;">CATEGORÍA</th>
            <th style="width:10%; text-align:center; background-color:#f4f4f4;">CANT. VENDIDA</th>
            <th style="width:15%; text-align:center; background-color:#f4f4f4;">TOTAL VENTAS (S/.)</th>
        </tr>
    </thead>
    <tbody>
        @if(count($top_productos) > 0)
            @foreach($top_productos as $index => $item)
            <tr>
                <td style="text-align:center;"><strong>{{ $index + 1 }}</strong></td>
                <td style="text-align:center;">{{ $item->codigo }}</td>
                <td>{{ $item->producto }}</td>
                <td style="text-align:center;">{{ $item->categoria_nom }}</td>
                <td style="text-align:right;">{{ number_format($item->cantidad_total, 2, '.', ',') }}</td>
                <td style="text-align:right;">{{ number_format($item->monto_total, 2, '.', ',') }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td colspan="6" style="text-align:center;">No se encontraron registros en este rango de fechas.</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr style="background-color:#f4f4f4; font-weight:bold;">
            <td colspan="4" style="text-align:right;">TOTALES DEL TOP 5:</td>
            <td style="text-align:right;">{{ number_format($total_cantidad, 2, '.', ',') }}</td>
            <td style="text-align:right;">{{ number_format($total_monto, 2, '.', ',') }}</td>
        </tr>
    </tfoot>
</table>