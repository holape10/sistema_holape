<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas por Categoría</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 10px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 4px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .header { text-align: center; margin-bottom: 15px; }
        .header h4, .header h5 { margin: 2px 0; }
        .total-row th, .total-row td { font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        @if(isset($empresa))
            <h4>{{ $empresa->NomEmpresa ?? 'Nombre de Empresa no disponible' }}</h4>
            <h5>RUC: {{ $empresa->IdEmpresa ?? 'RUC no disponible' }}</h5>
        @endif
        @if(isset($sucursal))
            <h5>Sucursal: {{ $sucursal->tipo_negocio ?? 'N/A' }}</h5>
        @endif
        <h4>REPORTE DE VENTAS POR CATEGORÍA</h4>
        <h5>Periodo: {{ \Carbon\Carbon::parse($fec_ini)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fec_fin)->format('d/m/Y') }}</h5>
    </div>

    @if(isset($salesByCategory) && count($salesByCategory) > 0)
        <table>
            <thead>
                <tr>
                    <th class="text-center">N°</th>
                    <th>CATEGORÍA</th>
                    <th class="text-right">TOTAL ARTÍCULOS VENDIDOS</th>
                    <th class="text-right">MONTO TOTAL VENTA (S/)</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGeneralVentas = 0; $totalGeneralItems = 0; $count = 1; @endphp
                @foreach($salesByCategory as $report)
                    <tr>
                        <td class="text-center">{{ $count++ }}</td>
                        <td>{{ $report->categoria_nombre }}</td>
                        <td class="text-right">{{ number_format($report->total_items_vendidos_categoria, 0) }}</td>
                        <td class="text-right">{{ number_format($report->total_ventas_categoria, 2) }}</td>
                    </tr>
                    @php 
                        $totalGeneralVentas += $report->total_ventas_categoria;
                        $totalGeneralItems += $report->total_items_vendidos_categoria;
                    @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <th colspan="2" class="text-right">TOTAL GENERAL:</th>
                    <th class="text-right">{{ number_format($totalGeneralItems, 0) }}</th>
                    <th class="text-right">{{ number_format($totalGeneralVentas, 2) }}</th>
                </tr>
            </tfoot>
        </table>
    @else
        <p>No se encontraron ventas por categoría para los filtros seleccionados.</p>
    @endif
</body>
</html>