<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    /* Estilos globales - Tipografía más limpia y profesional */
    * {
      font-family: 'Segoe UI', Roboto, Arial, sans-serif;
      color: #000;
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      background-color: #fff;
    }

    .ticket {
      width: 75mm; /* Ajustado para ticketeras de 80mm con margen */
      margin: 0 auto;
      padding: 5px;
    }

    /* Encabezado */
    .header {
      text-align: center;
      margin-bottom: 10px;
    }

    .empresa-nombre {
      text-transform: uppercase;
      font-size: 10pt;
      font-weight: bold;
      line-height: 1.2;
    }

    .empresa-info {
      font-size: 8pt;
      margin-top: 3px;
    }

    .titulo-reporte {
      text-align: center;
      font-size: 9pt;
      font-weight: bold;
      margin: 10px 0 5px 0;
      border-top: 1px dashed #000;
      border-bottom: 1px dashed #000;
      padding: 5px 0;
    }

    /* Tablas */
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 5px;
    }

    th {
      font-size: 7.5pt;
      font-weight: bold;
      border-bottom: 1px solid #000;
      padding: 3px 0;
    }

    td {
      font-size: 8pt;
      padding: 4px 0;
      vertical-align: top;
    }

    .text-center { text-align: center; }
    .text-right { text-align: right; }
    .text-left { text-align: left; }

    .linea-punteada {
      border-top: 1px dashed #000;
      margin: 5px 0;
    }

    .total-row td {
      font-weight: bold;
      font-size: 9pt;
      padding-top: 5px;
    }

    /* Para evitar cortes feos al imprimir */
    tr {
      page-break-inside: avoid;
    }
  </style>

  <script>
    window.onload = function() {
      window.print();
    };
  </script>
</head>

<body>
  <div class="ticket">
    
    <div class="header">
      <div class="empresa-nombre">{{$empresa->NomEmpresa}}</div>
      <div class="empresa-info">
        RUC: {{$empresa->IdEmpresa}}<br>
        {{$data_sucursal->direccion}}
      </div>
    </div>

    <div class="titulo-reporte">
      BOLETAS DE VENTAS<br>
      <span style="font-weight: normal; font-size: 7.5pt;">
        Desde: {{Carbon\Carbon::parse($fecin)->format('d/m/Y')}} Hasta: {{Carbon\Carbon::parse($fecfin)->format('d/m/Y')}}
      </span>
    </div>

    <table>
      <thead>
        <tr>
          <th class="text-left">FECHA</th>
          <th class="text-center">SERIE-NUM</th>
          <th class="text-center">CLIENTE</th>
          <th class="text-right">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        @foreach($boletas as $bol)
        <tr>
          <td class="text-left" style="font-size: 7.5pt;">{{Carbon\Carbon::parse($bol->fecha)->format('d/m/y')}}</td>
          <td class="text-center">{{$bol->serie}}-{{$bol->numero}}</td>
          <td class="text-center">{{$bol->cliente}}</td>
          <td class="text-right">{{number_format($bol->total, 2, '.', ',')}}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="total-row">
          <td colspan="2" class="text-right" style="border-top: 1px dashed #000;">TOTAL BOLETAS:</td>
          <td class="text-right" style="border-top: 1px dashed #000;">{{number_format($total_boletas, 2, '.', ',')}}</td>
        </tr>
      </tfoot>
    </table>

    <br>

    <div class="titulo-reporte">
      FACTURAS DE VENTAS<br>
      <span style="font-weight: normal; font-size: 7.5pt;">
        Desde: {{Carbon\Carbon::parse($fecin)->format('d/m/Y')}} Hasta: {{Carbon\Carbon::parse($fecfin)->format('d/m/Y')}}
      </span>
    </div>

    <table>
      <thead>
        <tr>
          <th class="text-left">FECHA</th>
          <th class="text-center">SERIE-NUM</th>
          <th class="text-right">TOTAL</th>
        </tr>
      </thead>
      <tbody>
        @foreach($facturas as $fac)
        <tr>
          <td class="text-left" style="font-size: 7.5pt;">{{Carbon\Carbon::parse($fac->fecha)->format('d/m/y')}}</td>
          <td class="text-center">{{$fac->serie}}-{{$fac->numero}}</td>
          <td class="text-right">{{number_format($fac->total, 2, '.', ',')}}</td>
        </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr class="total-row">
          <td colspan="2" class="text-right" style="border-top: 1px dashed #000;">TOTAL FACTURAS:</td>
          <td class="text-right" style="border-top: 1px dashed #000;">{{number_format($total_facturas, 2, '.', ',')}}</td>
        </tr>
      </tfoot>
    </table>

    <div class="linea-punteada"></div>
    <div style="text-align: center; font-size: 7pt; margin-top: 10px;">
      Reporte generado el {{ date('d/m/Y H:i:s') }}
    </div>

  </div>
</body>
</html>