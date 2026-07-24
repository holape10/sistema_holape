<div class="box-body table-responsive">
    <h4 class="text-center">REPORTE DE VENTAS POR CATEGORÍA DETALLADA</h4>
    <h5 class="text-center">Desde: {{ Carbon::parse($fec_ini)->format('d/m/Y') }} Hasta: {{ Carbon::parse($fec_fin)->format('d/m/Y') }}</h5>
    @if(!empty($dato_vendedor))
        <h5 class="text-center">Vendedor: {{ $dato_vendedor->name }} {{ $dato_vendedor->apeusu }}</h5>
    @endif
    @if(!empty($dato_cliente))
        <h5 class="text-center">Cliente: {{ $dato_cliente->clinom }} ({{ $dato_cliente->clinum }})</h5>
    @endif
    <h5 class="text-center">Sucursal: {{ $sucursal->tipo_negocio }} ({{ $sucursal->IdEmpresa }})</h5>
    <h5 class="text-center">Generado el: {{ now()->format('d/m/Y H:i:s') }}</h5>
    <br>

    @forelse($reporte_agrupado as $categoria_id => $data_categoria)
        <table class="table table-striped table-bordered table-condensed">
            <thead>
                <tr style="background-color: #5bc0de; color: white;">
                    <th colspan="4" class="text-left">Categoría: {{ $data_categoria['cat_nom'] }}</th>
                </tr>
                <tr style="background-color: #d9edf7; color: #333;"> {{-- Color más claro para subencabezado --}}
                    <th>Producto</th>
                    <th>Código</th>
                    <th class="text-center">Cantidad</th>
                    <th class="text-right">Total (S/.)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($data_categoria['productos'] as $producto)
                <tr>
                    <td>{{ $producto->pronom }}</td>
                    <td>{{ $producto->procod }}</td>
                    <td class="text-center">{{ number_format($producto->cantidad_total_producto, 0) }} {{ $producto->umecod }}</td>
                    <td class="text-right">S/. {{ number_format($producto->total_venta_producto, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #f5f5f5; font-weight: bold;">
                    <td colspan="2" class="text-right">Total Categoría {{ $data_categoria['cat_nom'] }}:</td>
                    <td class="text-center">{{ number_format($data_categoria['total_categoria_cantidad'], 0) }}</td>
                    <td class="text-right">S/. {{ number_format($data_categoria['total_categoria_ventas'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
        <br> {{-- Espacio entre tablas de categoría para mejor visualización --}}
    @empty
        <div class="alert alert-warning text-center">No hay ventas detalladas por categoría en el rango de fechas seleccionado.</div>
    @endforelse

    <hr>
    <div class="text-right">
        <h4 style="font-weight: bold;">TOTAL GENERAL DE VENTAS: S/. {{ number_format($total_general_ventas, 2) }}</h4>
        <h5 style="font-weight: bold;">CANTIDAD TOTAL DE PRODUCTOS: {{ number_format($total_general_cantidad, 0) }}</h5>
    </div>
</div>