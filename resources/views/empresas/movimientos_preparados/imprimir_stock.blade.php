<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Stock - {{ ucfirst($filtro_stock) }}</title>
    <style>
        /* Estilos generales */
        body {
            font-family: Arial, sans-serif;
            /* Si es ticket la letra es más chica */
            font-size: {{ $formato == 'ticket' ? '12px' : '14px' }};
            margin: 0;
            padding: 0;
            color: #000;
        }
        
        /* Contenedor dinámico (Ticket 80mm o Normal 100%) */
        .contenedor {
            width: {{ $formato == 'ticket' ? '76mm' : '100%' }};
            max-width: {{ $formato == 'ticket' ? '76mm' : '800px' }};
            margin: 0 auto;
            padding: {{ $formato == 'ticket' ? '2mm' : '20px' }};
        }

        h3, p { margin: 5px 0; text-align: center; }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        
        /* En ticket no queremos bordes gruesos para ahorrar tinta */
        th, td { 
            border-bottom: 1px dashed #ccc; 
            padding: 5px 2px; 
            text-align: left; 
        }
        
        th { font-weight: bold; border-bottom: 1px solid #000; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }

        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="contenedor">
        <h3>REPORTE DE STOCK</h3>
        <p>
            Filtro: {{ strtoupper(str_replace('_', ' ', $filtro_stock)) }}<br>
            Fecha: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
        </p>
        
        <table>
            <thead>
                <tr>
                    @if($formato != 'ticket') 
                        <th>ID</th> 
                    @endif
                    <th>PRODUCTO</th>
                    <th class="text-right">STOCK</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productos as $producto)
                    <tr>
                        @if($formato != 'ticket') 
                            <td>{{ $producto->IdProducto }}</td> 
                        @endif
                        <td>{{ $producto->pronom }}</td>
                        <td class="text-right"><strong>{{ $producto->stock_preparados ?? 0 }}</strong></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">No hay productos con este filtro</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <p style="margin-top: 15px; font-size: 10px;">
            --- Fin del Reporte ---
        </p>

        <div class="text-center no-print" style="margin-top: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">Imprimir Nuevamente</button>
        </div>
    </div>

</body>
</html>