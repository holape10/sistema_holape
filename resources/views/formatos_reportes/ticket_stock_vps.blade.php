<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Stock</title>
    <style>
        /* Configuraciones para ticket de 80mm */
        @page {
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 78mm; 
            background-color: #fff;
            color: #000;
        }
        .center {
            text-align: center;
        }
        .left {
            text-align: left;
        }
        .right {
            text-align: right;
        }
        .divider {
            border-bottom: 1px dashed #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            vertical-align: top;
            padding: 2px 0;
        }
        
        /* Ajuste de columnas para que entren 3 en 80mm */
        .col-producto { width: 45%; }
        .col-categoria { width: 35%; text-align: left; }
        .col-stock { width: 20%; text-align: right; }
    </style>
</head>
<body onload="window.print();">

    <div class="center">
        <h3 style="margin:0;">{{ $empresa->NomEmpresa }}</h3>
        <div>REPORTE DE STOCK</div>
        @if(!empty($datosalm))
            <div>ALMACEN: {{ $datosalm->descripcion }}</div>
        @endif
        <div>FECHA: {{ date('d/m/Y H:i:s') }}</div>
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th class="left col-categoria">CATEGORIA</th>
                <th class="left col-producto">PRODUCTO</th>                
                <th class="col-stock">STOCK</th>
            </tr>
        </thead>
    </table>
    
    <div class="divider"></div>

    <table>
        <tbody>
            @foreach($productos as $pro)
                <tr>
                    <td class="left col-categoria">{{ substr($pro->categoria, 0, 12) }}</td>
                    <td class="left col-producto">{{ substr($pro->pronom, 0, 16) }}</td>                    
                    <td class="col-stock">{{ $pro->stock }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>
    <div class="center" style="margin-top: 10px; margin-bottom: 20px;">
        *** FIN DEL REPORTE ***
    </div>

</body>
</html>