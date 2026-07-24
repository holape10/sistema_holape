<html>

<head>
  <style>
  * {

  font-family: "Lucida Console", "Lucida Sans Typewriter", monaco, "Bitstream Vera Sans Mono",monospace; font-size: 12px; font-style: normal; font-variant: normal;
}

@media print{
  .oculto-impresion, .oculto-impresion *{
    display: none !important;
  }
}
#table {
border: 0px;
}

td,
th,
tr,
table {
 
}

td.producto,
th.producto {
  width: 4cm;
  text-align:left;
  max-width: 4cm;
}

td.cantidad,
th.cantidad {
  width: 2cm;
  max-width: 2cm;
  text-align:right;
  word-break: break-all;
}

td.precio,
th.precio {
  width: 3cm;
  max-width: 3cm;
  text-align:right;
  word-break: break-all;
}


.centrado {
  text-align: center;
  align-content: center;
}

.ticket {
  width: 8cm;
  max-width: 9cm;
}

#alinear
   padding-left:1500px;
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
   <br><p class="centrado"><font size="3"><strong>{{$empresa->NomEmpresa}}</strong></font><br><font size="2"><strong>{{$empresa->DirEmpresa}}</strong></font><font size="2"><BR>R.U.C: {{$empresa->IdEmpresa}}</p>
  
  <table  style=" border-bottom: 0px;">
      <tbody>

        @if(!empty($cajero))
        <tr>
          <td  style="border-top:0px;border-bottom:0px"><strong><font size="2">CAJERO: {{$cajero->name}} - {{$cajero->apeusu}}</font></strong></td>
         
        </tr>
        @endif

        @if(!empty($datos))
         <tr>
          <td  style="border-top:0px;border-bottom:0px"><strong><font size="2">TURNO: {{$datos->apertura}} - {{$datos->cierre}}</font></strong></td>
         
        </tr>
         @endif

         @if(!empty($fecin))
          <tr>
            <td  style="border-top:0px;border-bottom:0px"><strong><font size="2">Fecha: {{Carbon::parse($fecin)->format('d-m-Y')}} - {{Carbon::parse($fecfin)->format('d-m-Y')}}</font></strong></td>
           
          </tr>
         @endif
    </tbody>
  </table> <br>


     <table  style=" border-bottom: 0px;">
     
        <tr>
          <td colspan="3" style="text-align:center;"><STRONG><u>PRODUCTOS VENDIDOS</u></STRONG></td>
        </tr>

       <tr>
          <td id="table" class="producto"><font size="2"><strong><center>PRODUCTOS.</center></strong></font></td>
           <td id="table" class="producto"><font size="2"><strong><center>CANT.</center></strong></font></td>
           <td id="table" class="producto"><font size="2"><strong><center>PRECIO</center></strong></font></td>
       
       
      
         
            <td id="table" class="producto"><font size="2"><strong><center>TOTAL</center></strong></font></td>
       
           </tr>
        @php
          $sum_tot_productos = 0; // INICIALIZACIÓN DE LA SUMA TOTAL DE PRODUCTOS
          $sum_tot = 0; // Variable existente para insumos (la dejamos)
        @endphp
        @foreach($ven_prod as $vp)
    @php
      // Es una presentación si IdProducto_rel no es nulo o 0 y es diferente del producto actual.
      // Asumo que si IdProducto_rel está definido, p.IdProducto es diferente a IdProducto_rel para las presentaciones.
      $is_presentation = !empty($vp->IdProducto_rel) && $vp->IdProducto != $vp->IdProducto_rel;

      // Aplicar una indentación (cuatro espacios no separables) si es una presentación.
      // Usamos {!! !!} porque estamos inyectando HTML (&nbsp;).
      $product_name = $is_presentation ? '&nbsp;&nbsp;&nbsp;&nbsp;' . $vp->pronom : $vp->pronom;
      
      // SUMAR AL TOTAL GENERAL DE PRODUCTOS
      $sum_tot_productos += $vp->TOTAL; 
    @endphp
    <tr>
      <td id="table" class="producto" colspan="3"><font size="2">{!! $product_name !!}</font></td>
    </tr>
        <tr>
      <td ></td>
      <td id="table" style="width:250px;text-align:right;"><font size="2">{{$vp->CANTIDAD}}</font></td>

      <td id="table" style="width:250px;text-align:right;">
        <font size="2">
            {{ $vp->CANTIDAD > 0 ? number_format($vp->TOTAL / $vp->CANTIDAD, 2, '.', '') : '0.00' }}
        </font>
    </td>


       <!--<td id="table" style="width:250px;text-align:right;"><font size="2">{{number_format($vp->TOTAL/$vp->CANTIDAD,'2','.','')}}</font></td>-->
      <td id="table" style="width:250px;text-align:right;" ><font size="2">{{number_format($vp->TOTAL,'2','.','')}}</font></td>
       </tr>
@endforeach
        <tr>
          <td colspan="4"><hr></td>
        </tr>
        
        {{-- MOSTRAR EL TOTAL GENERAL DE PRODUCTOS VENDIDOS --}}
        <tr>
          <td colspan="3" style="text-align:right;"><STRONG><font size="2">TOTAL VENTA PRODUCTOS:</font></STRONG></td>
          <td id="table" style="text-align:right;"><STRONG><font size="2">{{number_format($sum_tot_productos,'2','.','')}}</font></STRONG></td>
        </tr>
        <tr>
          <td colspan="4"><hr></td>
        </tr>
         
          @foreach($ven_ins as $in)
            
            @php
              $sum_tot = $sum_tot+$in->TOT_INS;

            @endphp
        @endforeach

         @php
              $enteros = 0;
              $medios = 0;
              $cuartos = 0;
              $octavos = 0;

              

              $decimales_enteros = $sum_tot-intval($sum_tot);
              $residuo_medios = fmod($decimales_enteros,0.50);
              $cal_medios = $decimales_enteros/0.50;

              if($residuo_medios==0){
                $medios = intval($cal_medios);
              }

              $decimales_medios = $decimales_enteros-($medios*0.50);
              $residuo_cuartos = fmod($decimales_medios,0.25);
              $cal_cuartos = $decimales_medios/0.25;
              $cuartos = intval($cal_cuartos);

              $decimales_cuartos = $decimales_medios-($cuartos*0.25);
              $residuo_octavos = fmod($decimales_cuartos,0.125);
              $cal_octavos = $decimales_cuartos/0.125;

              $octavos = intval($cal_octavos);


          @endphp

        <tr hidden="hidden">
          <td colspan="3" style="text-align:center;"><STRONG><u>TOTAL POLLOS VENDIDOS: {{$sum_tot}}</u></STRONG></td>
        </tr>
        <tr hidden="hidden">
          <td colspan="3" style="text-align:left;">ENTEROS: {{intval($sum_tot)}} </td>
        </tr>
        <tr hidden="hidden">
          <td colspan="3" style="text-align:left;">MEDIOS: {{$medios}}</td>
        </tr>
        <tr hidden="hidden">
          <td colspan="3" style="text-align:left;">CUARTOS: {{$cuartos}} </td>
        </tr>
        <tr hidden="hidden">
          <td colspan="3" style="text-align:left;">OCTAVOS: {{$octavos}} </td>
        </tr>

  </table><br>


   
  </div>
   
</body>

</html>