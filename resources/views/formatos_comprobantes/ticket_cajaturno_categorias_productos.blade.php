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
  
  <table style="border-bottom: 0px;">
      <tbody>
        @if(!empty($cajero))
        <tr>
          <td style="border-top:0px;border-bottom:0px"><strong><font size="2">CAJERO: {{$cajero->name}} - {{$cajero->apeusu}}</font></strong></td>
        </tr>
        @endif

        @if(!empty($datos))
         <tr>
          <td style="border-top:0px;border-bottom:0px"><strong><font size="2">TURNO: {{ \Carbon\Carbon::parse($datos->apertura)->format('Y-m-d H:i:s') }} - {{ \Carbon\Carbon::parse($datos->cierre)->format('Y-m-d H:i:s') }}</font></strong></td>
        </tr>
         @endif
    </tbody>
  </table> 
  <br>

  <table style="border-bottom: 0px;">
    <tr>
      <td colspan="4" style="text-align:center;"><STRONG><u>REPORTE POR CATEGORÍA Y PRODUCTOS</u></STRONG></td>
    </tr>
    <tr>
        <td colspan="4"><hr></td>
    </tr>

    @foreach($ventas_por_categoria as $categoria_id => $categoria_data)
        <tr>
            <td colspan="4" style="text-align:left;"><STRONG><font size="2">CATEGORÍA: {{ $categoria_data['nombre_categoria'] }}</font></STRONG></td>
        </tr>
        <tr>
            <td id="table" class="producto"><font size="2"><strong>PRODUCTO</strong></font></td>
            <td id="table" class="cantidad"><font size="2"><strong>CANT.</strong></font></td>
            <td id="table" class="precio"><font size="2"><strong>P.UNIT.</strong></font></td>
            <td id="table" class="precio"><font size="2"><strong>TOTAL</strong></font></td>
        </tr>
        @foreach($categoria_data['productos'] as $producto)
            <tr>
                <td id="table" class="producto"><font size="2">{{ $producto->pronom }}</font></td>
                <td id="table" class="cantidad"><font size="2">{{ number_format($producto->CANTIDAD, 2) }}</font></td>
                <td id="table" class="precio"><font size="2">{{ number_format($producto->PRECIO_UNITARIO, 2) }}</font></td>
                <td id="table" class="precio"><font size="2">{{ number_format($producto->TOTAL, 2) }}</font></td>
            </tr>
        @endforeach
        <tr>
            <td colspan="4"><hr></td>
        </tr>
    @endforeach

    <tr>
        <td colspan="3" style="text-align:right;"><STRONG><font size="2">GRAN TOTAL GENERAL:</font></STRONG></td>
        <td style="text-align:right;"><STRONG><font size="2">{{ number_format($gran_total_ventas, 2) }}</font></STRONG></td>
    </tr>
  </table>
</div>
</body>
</html>