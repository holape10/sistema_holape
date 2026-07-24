{{-- resources/views/empresas/reportes/ventas/formatos/reporte_ventas_por_categoria_excel.blade.php --}}
<table>
    <thead>
        {{-- Fila para el título principal del reporte --}}
        <tr>
            <th colspan="4" style="text-align:center; font-weight:bold; font-size:14px;">REPORTE DE VENTAS POR CATEGORÍA</th>
        </tr>

        {{-- Fila para información de la empresa y sucursal (si aplica) --}}
        @if(isset($empresa) || isset($sucursal))
        <tr>
            <th colspan="4" style="text-align:center; font-weight:bold;">
                @if(isset($empresa))
                    {{ $empresa->NomEmpresa ?? 'Nombre de Empresa no disponible' }}
                    @if(isset($empresa->IdEmpresa))
                        (RUC: {{ $empresa->IdEmpresa }})
                    @endif
                    <br>
                @endif
                @if(isset($sucursal))
                    Sucursal: {{ $sucursal->tipo_negocio ?? 'N/A' }}
                @endif
            </th>
        </tr>
        @endif

        {{-- Fila para el período del reporte --}}
        <tr>
            <th colspan="4" style="text-align:center; font-weight:bold;">
                Periodo: {{ \Carbon\Carbon::parse($fec_ini)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fec_fin)->format('d/m/Y') }}
            </th>
        </tr>

        {{-- Fila vacía para espaciado --}}
        <tr>
            <th colspan="4"></th>
        </tr>

        {{-- Encabezados de la tabla de datos --}}
        <tr>
            <th style="text-align:center; border:1px solid black; font-weight:bold; background-color:#A9D0F5;">N°</th>
            <th style="text-align:center; border:1px solid black; font-weight:bold; background-color:#A9D0F5;">CATEGORÍA</th>
            <th style="text-align:center; border:1px solid black; font-weight:bold; background-color:#A9D0F5;">TOTAL ARTÍCULOS VENDIDOS</th>
            <th style="text-align:center; border:1px solid black; font-weight:bold; background-color:#A9D0F5;">MONTO TOTAL VENTA (S/)</th>
        </tr>
    </thead>
    <tbody>
        @php $totalGeneralVentas = 0; $totalGeneralItems = 0; $count = 1; @endphp
        @if(isset($salesByCategory) && count($salesByCategory) > 0)
            @foreach($salesByCategory as $report)
                <tr>
                    <td style="text-align:center; border:1px solid black;">{{ $count++ }}</td>
                    <td style="border:1px solid black;">{{ $report->categoria_nombre }}</td>
                    <td style="text-align:right; border:1px solid black;">{{ $report->total_items_vendidos_categoria }}</td>
                    <td style="text-align:right; border:1px solid black;">{{ number_format($report->total_ventas_categoria, 2, '.', '') }}</td>
                </tr>
                @php 
                    $totalGeneralVentas += $report->total_ventas_categoria;
                    $totalGeneralItems += $report->total_items_vendidos_categoria;
                @endphp
            @endforeach
        @else
            <tr>
                <td colspan="4" style="text-align:center; border:1px solid black;">No se encontraron ventas por categoría para los filtros seleccionados.</td>
            </tr>
        @endif
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" style="text-align:right; border:1px solid black; font-weight:bold; background-color:#A9D0F5;">TOTAL GENERAL:</th>
            <th style="text-align:right; border:1px solid black; font-weight:bold; background-color:#A9D0F5;">{{ $totalGeneralItems }}</th>
            <th style="text-align:right; border:1px solid black; font-weight:bold; background-color:#A9D0F5;">{{ number_format($totalGeneralVentas, 2, '.', '') }}</th>
        </tr>
    </tfoot>
</table>