<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $formato == 'blanco' ? 'Formato de Control de Producción' : 'Reporte General de Stock' }}</title>
    <style>
        body {
            color: #000;
            margin: 0;
            padding: 0;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th, td {
            text-align: left;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-weight-bold { font-weight: bold; }
        
        .header-title {
            text-align: center;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .header-info {
            margin-bottom: 15px;
        }

        /* --- ESTILOS PARA TICKET 80mm --- */
        @if($formato == 'ticket')
            body {
                font-family: 'Courier New', Courier, monospace;
                font-size: 11px;
                width: 80mm;
                margin: 0 auto;
                padding: 3mm;
            }
            .header-title { font-size: 13px; }
            th, td {
                padding: 4px 1px;
                border-bottom: 1px dashed #000;
            }
            th { border-top: 1px dashed #000; }
            .hide-on-ticket { display: none; }
        
        /* --- ESTILOS PARA PDF / FORMATO BLANCO (A4) --- */
        @else
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                width: 100%;
                padding: 20px;
            }
            .header-title { font-size: 18px; }
            th, td {
                padding: 8px 6px;
                border: 1px solid #000;
            }
            th {
                background-color: #f2f2f2;
            }
            
            .celda-vacia {
                height: 30px; 
            }
        @endif

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="text-align: right; margin-bottom: 10px;">
        <button onclick="window.print()" style="padding: 5px 10px; font-size: 14px; cursor: pointer;">🖨️ Imprimir Formato</button>
    </div>

    <div class="header-title">
        @if($formato == 'blanco')
            HOJA DE TRABAJO Y CONTROL DE PRODUCCIÓN
        @elseif($formato == 'ticket')
            TICKET GENERAL DE STOCK
        @else
            REPORTE GENERAL DE STOCK
        @endif
    </div>
    
    <div class="header-info">
        <strong>Filtro Fechas:</strong> {{ date('d/m/Y', strtotime($fecha_inicio)) }} al {{ date('d/m/Y', strtotime($fecha_fin)) }}<br>
        <strong>Filtro Stock:</strong> {{ strtoupper(str_replace('_', ' ', $filtro_stock)) }}<br>
        <strong>Fecha Impresión:</strong> {{ date('d/m/Y H:i:s') }}
    </div>

    <table>
        <thead>
            <tr>
                {{-- Ocultamos columnas extras en Ticket y Formato Blanco para optimizar espacio --}}
                @if($formato != 'ticket' && $formato != 'blanco')
                    <th>ID</th>
                    <th>Línea</th>
                    <th>Sub Línea</th>
                    <th>Tipo</th>
                @endif
                
                <th>PRODUCTO</th>
                
                {{-- En el ticket le damos un ancho fijo pequeño a las columnas numéricas --}}
                <th class="text-center" style="width: {{ $formato == 'ticket' ? '65px' : '100px' }};">ST. INI</th>
                
                @if($formato == 'blanco')
                    <th class="text-center" style="width: 180px;">PRODUCCIÓN DEL DÍA</th>
                    <th class="text-center" style="width: 140px;">STOCK FINAL</th>
                @else
                    {{-- AQUÍ: Quitamos .hide-on-ticket para que aparezca SIEMPRE en el ticket --}}
                    <th class="text-center" style="width: {{ $formato == 'ticket' ? '65px' : '100px' }};">ST. ACT</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
                <tr>
                    @if($formato != 'ticket' && $formato != 'blanco')
                        <td>{{ $item->IdProducto }}</td>
                        <td>{{ $item->tip_pro_nom ?? 'N/A' }}</td>
                        <td>{{ $item->cat_nom ?? 'N/A' }}</td>
                        <td>{{ strtoupper($item->tipo_origen) }}</td>
                    @endif
                    
                    <td>
                        @if($formato == 'ticket')
                            {{-- Prefijo corto para que no ocupe mucho espacio en la tiquetera: [INS], [PRO], [PRE] --}}
                            [{{ strtoupper(substr($item->tipo_origen, 0, 3)) }}] 
                        @endif
                        {{ $item->pronom }}
                    </td>
                    
                    {{-- Stock Inicial --}}
                    <td class="text-center" style="{{ $formato != 'ticket' ? 'background-color: #fafafa;' : '' }}">
                        {{ number_format($item->stock_inicial, 2) }}
                    </td>
                    
                    @if($formato == 'blanco')
                        <td class="celda-vacia"></td>
                        <td class="celda-vacia"></td>
                    @else
                        {{-- AQUÍ: Quitamos la clase .hide-on-ticket para que se muestre en la tiquetera --}}
                        <td class="text-center font-weight-bold">
                            {{ number_format($item->stock_actual, 2) }}
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">No se encontraron productos con los filtros seleccionados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="text-align: center; margin-top: 20px; font-size: 10px;">
        --- Fin del Reporte ---
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>