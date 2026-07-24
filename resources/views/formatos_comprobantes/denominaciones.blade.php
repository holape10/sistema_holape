<html>
<head>
  <style>
    * {
      font-family: "Lucida Console", "Lucida Sans Typewriter", monaco, "Bitstream Vera Sans Mono", monospace;
      font-size: 12px;
      font-style: normal;
      font-variant: normal;
      color: #000;
    }

    @media print {
      .oculto-impresion, .oculto-impresion * {
        display: none !important;
      }
    }

    .ticket {
      width: 8cm;
      max-width: 8cm;
      margin: 0 auto;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border: 0px;
      margin-bottom: 5px;
    }

    td.producto, th.producto {
      text-align: left;
      padding-right: 5px;
    }

    td.precio, th.precio {
      text-align: right;
      white-space: nowrap;
    }

    .centrado {
      text-align: center;
      align-content: center;
    }

    .divisor td {
      border-top: 1px dashed #000;
      padding-top: 5px;
      padding-bottom: 5px;
    }
    
    .titulo-seccion {
      font-weight: bold;
      text-align: center;
      padding-top: 8px;
      padding-bottom: 4px;
    }
  </style>
</head>
<body>

<div class="ticket">
  <br>
  <p class="centrado">
    <font size="3"><strong>REPORTE DENOMINACIONES</strong></font><br>
    <font size="3"><strong>{{$empresa->NomEmpresa}}</strong></font><br>
    <font size="2"><strong>{{$empresa->DirEmpresa}}</strong></font><br>
    <font size="2">R.U.C: {{$empresa->IdEmpresa}}</font>    
  </p>

  <table>
      <tbody>
        <tr>
          <td style="border-top:0px;border-bottom:0px"><strong><font size="2">CAJERO: {{$cajero->name}} - {{$cajero->apeusu}}</font></strong></td>
        </tr>
         <tr>
          <td style="border-top:0px;border-bottom:0px"><strong><font size="2">TURNO: {{$datos->apertura}} - {{$datos->cierre}}</font></strong></td>
        </tr>
    </tbody>
  </table> 
  <br>

  <table>
        <tr>
          <td colspan="2" class="titulo-seccion"><u>DESGLOSE FISICO DECLARADO</u></td>
        </tr>
        
        @php
            $total_fisico = 0;
            $monedas_billetes = [
                'Monedas 0.10' => [$denominaciones['cant_m_10_centimos'], 0.10],
                'Monedas 0.20' => [$denominaciones['cant_m_20_centimos'], 0.20],
                'Monedas 0.50' => [$denominaciones['cant_m_50_centimos'], 0.50],
                'Monedas 1.00' => [$denominaciones['cant_m_1_sol'], 1.00],
                'Monedas 2.00' => [$denominaciones['cant_m_2_soles'], 2.00],
                'Monedas 5.00' => [$denominaciones['cant_m_5_soles'], 5.00],
                'Billetes 10.00' => [$denominaciones['cant_c_10_soles'], 10.00],
                'Billetes 20.00' => [$denominaciones['cant_c_20_soles'], 20.00],
                'Billetes 50.00' => [$denominaciones['cant_c_50_soles'], 50.00],
                'Billetes 100.00' => [$denominaciones['cant_c_100_soles'], 100.00],
                'Billetes 200.00' => [$denominaciones['cant_c_200_soles'], 200.00],
            ];
        @endphp

        @foreach($monedas_billetes as $nombre => $info)
            @php 
                $cantidad = $info[0];
                $valor = $info[1];
            @endphp
            
            @if($cantidad > 0)
                @php 
                    $subtotal = $cantidad * $valor; 
                    $total_fisico += $subtotal; 
                @endphp
                <tr>
                  <td class="producto"><font size="2">{{ $nombre }} (x{{ $cantidad }})</font></td>
                  <td class="precio"><font size="2">S/. {{ number_format($subtotal, 2, '.', ',') }}</font></td>
                </tr>
            @endif
        @endforeach
        
        <tr class="divisor">
              <td class="producto"><strong><font size="2">TOTAL EFECTIVO:</font></strong></td>
              <td class="precio"><strong><font size="2">S/. {{ number_format($total_fisico, 2, '.', ',') }}</font></strong></td>
        </tr>
  </table>
  <br>
  <p class="centrado"><font size="2">--- FIN DEL REPORTE ---</font></p>
  
  <table style="margin-top: 20px; border-collapse: collapse;">
    <tr>
      <td style="border: 1px solid #000; padding: 20px 20px; text-align: center; width: 50%; font-size: 12px;">
        <span style="border-bottom: 1px solid #000; display: inline-block; width: 100%; height: 40px; margin-top: 10px;"></span>
        <div style="margin-top: 10px; font-size: 12px;"><strong>CAJERO</strong></div>
      </td>
      <td style="border: 1px solid #000; padding: 20px 20px; text-align: center; width: 50%; font-size: 12px;">
        <span style="border-bottom: 1px solid #000; display: inline-block; width: 100%; height: 40px; margin-top: 10px;"></span>
        <div style="margin-top: 10px; font-size: 12px;"><strong>SUPERVISOR</strong></div>
      </td>
    </tr>
  </table>
</div>

<script>
  // Aquí está el truco de magia:
  // 1. Apenas carga la pantalla, lanza la impresión
  window.onload = function() {
      window.print();
      
      // 2. Espera medio segundo y te regresa a la ventana de caja automáticamente
      /*setTimeout(function() {
          window.location.href = "{{ url($ruta_redirect) }}";
      }, 500);*/
  };
</script>

</body>
</html>