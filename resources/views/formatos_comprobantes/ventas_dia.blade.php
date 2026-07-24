<!DOCTYPE html>
<html>
<head>
 <meta charset="utf-8">
 <title>Reporte de Caja del Día</title>
 <style>
    /* Estilos generales para el cuerpo del documento */
    body {
      font-family: 'Consolas', 'Courier New', monospace; /* Fuentes monoespaciadas para apariencia de ticket */
      font-size: 10px; /* Tamaño de fuente adecuado para ticket */
      margin: 0;
      padding: 0;
      width: 78mm; /* Ancho real del contenido imprimible en 80mm. Ajusta si hay márgenes de impresora */
      box-sizing: border-box; /* Para que el padding y border se incluyan en el width */
    }
    .ticket-container {
      width: 100%; /* Ocupa todo el ancho definido en body */
      padding: 2mm; /* Pequeño padding interno para que no pegue a los bordes */
      box-sizing: border-box;
    }
    .header, .footer {
      text-align: center;
      margin-bottom: 5px;
    }
    .header h1 {
      margin: 0;
      padding: 0;
      font-size: 14px;
      text-transform: uppercase;
    }
    .info {
      text-align: left;
      margin-bottom: 5px;
      font-size: 9px;
    }
    .info p {
      margin: 0;
      line-height: 1.2;
    }
    .items-table {
      width: 100%;
      border-collapse: collapse; /* Para que los bordes de celda se fusionen */
      margin-top: 2px;
      font-size: 14px;
    }
    .items-table th, .items-table td {
      padding: 1px 0; /* Espacio mínimo para filas */
      text-align: left;
      vertical-align: top; /* Alinear el texto en la parte superior de la celda */
    }
    .items-table th {
      font-weight: bold;
      border-top: 1px dashed #000; /* Línea punteada superior */
      border-bottom: 1px dashed #000; /* Línea punteada inferior */
    }
    /* Alineación específica para columnas de la tabla */
    .items-table .text-right {
      text-align: right;
    }
    .line {
      border-top: 1px dashed #000;
      margin: 5px 0; /* Espacio alrededor de la línea divisoria */
    }
    .totals {
      width: 100%;
      margin-top: 2px;
      text-align: right;
      font-size: 14px;
    }
    .totals p {
      margin: 2px 0;
      font-weight: bold;
    }

    /* Estilos para impresión */
    @media print {
      body {
        width: 80mm; /* El ancho de papel real de la impresora */
        margin: 0;
        padding: 0;
        /* Puedes intentar ajustar los márgenes de impresión a cero si el navegador lo permite */
        /* @page { margin: 0; } */
      }
      .ticket-container {
        padding: 0mm; /* Eliminar padding en impresión */
        margin: 0;
      }
      /* Opcional: Ocultar elementos de UI del navegador en impresión */
      /* button, a { display: none; } */
    }
  </style>
</head>
<body>
 <div class="ticket-container">
  <div class="header">
   <h3>{{ $empresa_nombre }}</h3>
   <p>RUC: {{ $empresa_ruc }}</p>
   <p>{{ $empresa_direccion }}</p>
   <p>{{ $empresa_telefono }}</p>
   <div class="line"></div>
   <p><strong>REPORTE DE CAJA DEL DÍA</strong></p>
   <!--<p>Turno ID: {{ $turno->id_turno }}</p>-->
   <p>Cajero: {{ $cajero_nombre }}</p>
   <p>Turno: {{ Carbon::parse($turno->apertura)->format('d/m/Y H:i:s') }} - {{ Carbon::parse($turno->cierre)->format('d/m/Y H:i:s') }}</p>
   <!--<p>Fin Turno: {{ Carbon::parse($turno->cierre)->format('d/m/Y H:i:s') }}</p>-->
   <div class="line"></div>
  </div>

    <div class="section-title">VENTAS DEL DIA</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Co.</th>
                <th>Nro.</th>
                <th>Cliente</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($venta->estadopago, 2, '') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($venta->serdoc.'-'.$venta->numdoc, 10, '') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($venta->nombre_cliente, 15, '') }}</td>
                    <td class="text-right">{{ number_format($venta->ccaitv, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="totals"> 
        <p>Ventas Contado ({{ $cantidadContado }}): <strong>S/ {{ number_format($totalContado, 2) }}</strong></p>
        <p>Ventas Crédito ({{ $cantidadCredito }}): <strong>S/ {{ number_format($totalCredito, 2) }}</strong></p>
        <div class="line"></div>
        <p>TOTAL VENTAS ({{ $cantidadVentas }}): <strong>S/ {{ number_format($totalVentas, 2) }}</strong></p>
    </div>

    <div class="line"></div> <div class="section-title">COBRANZAS DEL DIA</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Doc.</th>
                <th>Cliente</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cobranzas as $cobranza)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($cobranza->serdoc.'-'.$cobranza->numdoc, 10, '') }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($cobranza->nombre_cliente, 18, '') }}</td>
                    <td class="text-right">{{ number_format($cobranza->abono, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="totals">
        <p>TOTAL COBRANZAS ({{ $cantidadCobranzas }}): <strong>S/ {{ number_format($totalCobranzas, 2) }}</strong></p>
    </div>

    <div class="line"></div> <div class="section-title">GASTOS E INGRESOS</div>
    <table class="items-table">
        <thead>
            <tr>
                <th>Tipo</th>
                <th>Detalle</th>
                <th class="text-right">Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach($movimientosCaja as $movimiento)
                <tr>
                    <td>{{ \Illuminate\Support\Str::limit($movimiento->tipo_movimiento, 7, '') }}</td> <td>{{ \Illuminate\Support\Str::limit($movimiento->tip_gas_nom ?? $movimiento->det_gasto ?? 'N/A', 30, '') }}</td> <td class="text-right">{{ number_format($movimiento->pre_uni, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <div class="totals">
        <p>TOTAL GASTOS ({{ $cantidadGastosCaja }}): <strong>S/ {{ number_format($totalGastosCaja, 2) }}</strong></p>
        <p>TOTAL INGRESOS CAJA ({{ $cantidadIngresosCaja }}): <strong>S/ {{ number_format($totalIngresosCaja, 2) }}</strong></p>
    </div>

    <div class="line"></div>

    <div class="footer">
   <p>Reporte generado el: {{ $fecha_impresion }}</p>
   <p>Desarrollado por HOLAPE - 928 396 147</p>
   <br><br>
  </div>
 </div>

 {{-- Script para forzar la impresión al cargar la página --}}
 <script>
  window.onload = function() {
   window.print();
   // Opcional: cerrar la ventana después de la impresión (puede que algunos navegadores lo ignoren)
   // window.onafterprint = function() { window.close(); };
  };
 </script>
</body>
</html>