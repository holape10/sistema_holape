@if(isset($salesByCategory) && count($salesByCategory) > 0)
    <div class="table-responsive">
        <table class="table table-striped table-bordered table-condensed table-hover">
            <thead>
                <tr style="background-color:#337ab7">
                    <th style="text-align:center;">N°</th>
                    <th style="text-align:center;">CATEGORÍA</th>
                    <th style="text-align:center;">TOTAL ARTÍCULOS VENDIDOS</th>
                    <th style="text-align:center;">MONTO TOTAL VENTA (S/)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGeneralVentas = 0; $totalGeneralItems = 0; $count = 1; @endphp
                @foreach($salesByCategory as $report)
                    <tr>
                        <td style="text-align:center;">{{ $count++ }}</td>
                        <td>{{ $report->categoria_nombre }}</td>
                        <td style="text-align:right;">{{ number_format($report->total_items_vendidos_categoria, 0) }}</td>
                        <td style="text-align:right;">{{ number_format($report->total_ventas_categoria, 2) }}</td>
                    </tr>
                    @php 
                        $totalGeneralVentas += $report->total_ventas_categoria;
                        $totalGeneralItems += $report->total_items_vendidos_categoria;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2" style="text-align:right;">TOTAL GENERAL:</th>
                    <th style="text-align:right;">{{ number_format($totalGeneralItems, 0) }}</th>
                    <th style="text-align:right;">{{ number_format($totalGeneralVentas, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    </div>
@else
    <div class="alert alert-info">
        No se encontraron ventas por categoría para los filtros seleccionados.
    </div>
@endif