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
      margin: 0 auto; /* Centra el ticket si se ve en pantalla */
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border: 0px;
      margin-bottom: 5px;
    }

    td.producto,
    th.producto {
      text-align: left;
      padding-right: 5px;
    }

    td.precio,
    th.precio {
      text-align: right;
      white-space: nowrap; /* Evita que los precios se partan en dos líneas */
    }

    .centrado {
      text-align: center;
      align-content: center;
    }

    /* Clases para mejorar apariencia en tiquetera */
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

    img {
      max-width: inherit;
      width: inherit;
    }
  </style>
  <script>
    window.print();  
  </script>
</head>

<body>

<div style="margin-top:-25px" class="ticket">
    
  <br>
  <p class="centrado">
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
      <tbody>
        <tr class="divisor">
          <td class="producto"><font size="2">FONDO DE CAJA</font></td>
          <td class="precio"><font size="2">S/. {{number_format($datos->monto, 2)}}</font></td>
        </tr>
         <tr>
          <td class="producto"><font size="2">INGRESOS</font></td>
          <td class="precio"><font size="2">S/. {{number_format($ingresos, 2)}}</font></td>
        </tr>
         <tr>
          <td class="producto"><font size="2">GASTOS</font></td>
          <td class="precio"><font size="2">S/. {{number_format($gastos, 2)}}</font></td>
        </tr>
    </tbody>
  </table> 
  
  <!-- TABLA OCULTA MANTENIDA -->
  <table hidden="hidden">
        <tr>
          <td colspan="2" class="titulo-seccion"><u>REGISTRO DE DINERO</u></td>
        </tr>
        @foreach($medios_reg as $mreg)
          <tr>
            <td class="producto"><font size="2">{{$mreg->nom_med_pag}}</font></td>
            <td class="precio"><font size="2">S/. {{$mreg->monto}}</font></td>
          </tr>
        @endforeach
  </table> 
  
  <table>
        <tr>
          <td colspan="2" class="titulo-seccion"><u>VENTAS POR MEDIO DE PAGO</u></td>
        </tr>

          @php
          $totalventasmp = 0;
            foreach($medios as $vmp){
              $totalventasmp = $totalventasmp + $vmp->monto;
            }
            $totalcontado = $efectivo + $visa + $mastercard + $transferencia;
          @endphp

        @foreach($medios as $mp)
          <tr>
            <td class="producto"><font size="2">{{$mp->nom_med_pag}}</font></td>
            <td class="precio"><font size="2">S/. {{number_format($mp->monto, 2)}}</font></td>
          </tr>
        @endforeach
        
        <tr class="divisor">
            <td class="producto"><strong><font size="2">TOTAL CONTADO</font></strong></td>
            <td class="precio"><strong><font size="2">S/. {{number_format($totalcontado,2,'.',',')}}</font></strong></td>
        </tr>
        <tr class="divisor">
            <td class="producto"><strong><font size="2">TOTAL MEDIOS DE PAGO</font></strong></td>
            <td class="precio"><strong><font size="2">S/. {{number_format($totalventasmp,2,'.',',')}}</font></strong></td>
        </tr>
  </table> 
  
  <br>

  <table>
        <tr>
          <td colspan="2" class="titulo-seccion"><u>VENTAS A CRÉDITO</u></td>
        </tr>
        <tr>
          <td class="producto"><font size="2">Cantidad de documentos</font></td>
          <td class="precio"><font size="2">{{ number_format($cantidadcredito,0,'.',',') }}</font></td>
        </tr>
        <tr>
          <td class="producto"><font size="2">Total a crédito</font></td>
          <td class="precio"><font size="2">S/. {{ number_format($totalcredito,2,'.',',') }}</font></td>
        </tr>
  </table>
  
  <br>

  <table>
        <tr>
          <td colspan="2" class="titulo-seccion"><u>TOTAL DOCUMENTOS EMITIDOS</u></td>
        </tr>

        @php
          $totalventas = 0;
          foreach($comprobantes as $comp){
            $totalventas = $totalventas + $comp->monto;
          }
        @endphp

        @foreach($comprobantes as $comp)
        <tr>
          <td class="producto"><font size="2">TOTAL {{$comp->tdodes}} - CANT: {{$comp->cantidad}}</font></td>
          <td class="precio"><font size="2">S/. {{number_format($comp->monto, 2)}}</font></td>
        </tr>
        @endforeach
        
        <tr class="divisor">
              <td class="producto"><strong><font size="2">TOTAL CAJA TEÓRICA:</font></strong></td>
              <td class="precio"><strong><font size="2">S/. {{number_format($datos->monto+$totalventas+$ingresos-$gastos, 2)}}</font></strong></td>
        </tr>
  </table>

  <br>

  <!-- TABLA: ARQUEO FÍSICO DECLARADO -->
  <table>
        <tr>
          <td colspan="2" class="titulo-seccion"><u>ARQUEO FÍSICO DECLARADO</u></td>
        </tr>
        
        @php
            $total_fisico = 0;
            // Estructura: 'Nombre a mostrar' => [cantidad_ingresada, valor_real_moneda]
            $denominaciones = [
                'Monedas 0.10' => [$datos->cant_m_10_centimos, 0.10],
                'Monedas 0.20' => [$datos->cant_m_20_centimos, 0.20],
                'Monedas 0.50' => [$datos->cant_m_50_centimos, 0.50],
                'Monedas 1.00' => [$datos->cant_m_1_sol, 1.00],
                'Monedas 2.00' => [$datos->cant_m_2_soles, 2.00],
                'Monedas 5.00' => [$datos->cant_m_5_soles, 5.00],
                'Billetes 10.00' => [$datos->cant_c_10_soles, 10.00],
                'Billetes 20.00' => [$datos->cant_c_20_soles, 20.00],
                'Billetes 50.00' => [$datos->cant_c_50_soles, 50.00],
                'Billetes 100.00' => [$datos->cant_c_100_soles, 100.00],
                'Billetes 200.00' => [$datos->cant_c_200_soles, 200.00],
            ];
        @endphp

        @foreach($denominaciones as $nombre => $info)
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
              <td class="producto"><strong><font size="2">TOTAL EFECTIVO CAJA: </font></strong></td>
              <td class="precio"><strong><font size="2">S/. {{ number_format($total_fisico, 2, '.', ',') }}</font></strong></td>
        </tr>
  </table>

  <!-- NUEVA TABLA: RESULTADO DEL CUADRE (Diferencia) -->
  <br>
  <table>
        <tr>
          <td colspan="2" class="titulo-seccion"><u>RESULTADO DEL CUADRE</u></td>
        </tr>
        
        @php
            // 1. Buscamos específicamente las ventas hechas en "EFECTIVO"
            $ventas_efectivo = 0;
            foreach($medios as $mp){
                // Usamos stripos para que valide la palabra "EFECTIVO" sin importar si está en mayúsculas o minúsculas
                if(stripos(trim($mp->nom_med_pag), 'EFECTIVO') !== false){
                    $ventas_efectivo += $mp->monto;
                }
            }

            // 2. Calculamos cuánto efectivo debería haber según el sistema
            $efectivo_sistema = $datos->monto + $ingresos - $gastos + $ventas_efectivo;
            
            // 3. Calculamos la diferencia (Físico declarado menos el Sistema)
            $diferencia = $total_fisico - $efectivo_sistema;
        @endphp
        
        <tr>
              <td class="producto"><font size="2">EFECTIVO SISTEMA:</font></td>
              <td class="precio"><font size="2">S/. {{ number_format($efectivo_sistema, 2, '.', ',') }}</font></td>
        </tr>
        <tr>
              <td class="producto"><font size="2">EFECTIVO DECLARADO:</font></td>
              <td class="precio"><font size="2">S/. {{ number_format($total_fisico, 2, '.', ',') }}</font></td>
        </tr>
        <tr class="divisor">
              <td class="producto"><strong><font size="2">
                  @if($diferencia > 0)
                      SOBRANTE (A FAVOR)
                  @elseif($diferencia < 0)
                      FALTANTE (EN CONTRA)
                  @else
                      CUADRE EXACTO
                  @endif
              </font></strong></td>
              <!-- Usamos abs() para que el valor faltante/sobrante se muestre siempre en positivo (ej. S/. 90.00 en vez de S/. -90.00) -->
              <td class="precio"><strong><font size="2">S/. {{ number_format(abs($diferencia), 2, '.', ',') }}</font></strong></td>
        </tr>
  </table>

  <!-- TABLAS COMENTADAS MANTENIDAS EXACTAMENTE IGUAL -->
    <!--<table align="center" style=" border-bottom: 0px;">
        <tr>
          <td colspan="2"><CENTER><STRONG><u>SOBRANTES</u></STRONG></CENTER></td>
        </tr>
        @foreach($medios as $m)
          @foreach($medios_reg as $mr)
            @if($mr->id_med_pag == $m->id_med_pag)
            <tr>
              <td id="table" class="producto"><font size="2">{{$m->nom_med_pag}} </font></td>
               @if(($mr->monto - $m->monto) > 0 )
               <td id="table" class="producto" colspan="2"><font size="2">S/. {{$mr->monto - $m->monto}}</font></td>
               @else
                <td id="table" class="producto" colspan="2"><font size="2">S/. 0.00</font></td>
               @endif
            </tr>
            @endif
          @endforeach
        @endforeach
  </table><br>

    <table align="center" style=" border-bottom: 0px;">
        <tr>
          <td colspan="2"><CENTER><STRONG><u>FALTANTES</u></STRONG></CENTER></td>
        </tr>
        @foreach($medios as $m)
          @foreach($medios_reg as $mr)
            @if($mr->id_med_pag == $m->id_med_pag)
            <tr>
              <td id="table" class="producto"><font size="2">{{$m->nom_med_pag}} </font></td>
               @if(($mr->monto - $m->monto) < 0 )
               <td id="table" class="producto" colspan="2"><font size="2">S/. {{$mr->monto - $m->monto}}</font></td>
               @else
                <td id="table" class="producto" colspan="2"><font size="2">S/. 0.00</font></td>
               @endif
            </tr>
            @endif
          @endforeach
        @endforeach
  </table><br>-->

  </div>
   
</body>
</html>