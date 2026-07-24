{{-- resources/views/formatos_comprobantes/ticket_cajaturno_autoconsumo.blade.php --}}

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

  td, th, tr, table {
    /* No border-collapse here as per your original style, but consider if needed */
  }

  td.producto, th.producto {
    width: 5cm;
    text-align:left;
    max-width: 8cm;
  }

  td.cantidad, th.cantidad {
    width: 2cm;
    max-width: 2cm;
    text-align:right;
    word-break: break-all;
  }

  td.precio, th.precio {
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

  #alinear {
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
          <td  style="border-top:0px;border-bottom:0px"><strong><font size="2">TURNO: {{ \Carbon\Carbon::parse($datos->apertura)->format('Y-m-d H:i:s') }} - {{ \Carbon\Carbon::parse($datos->cierre)->format('Y-m-d H:i:s') }}</font></strong></td>
        </tr>
         @endif
    </tbody>
  </table> <br><Br>

  <table style=" border-bottom: 0px;">
    <tr>
      <td colspan="2" style="text-align:center;"><STRONG><u>REPORTE DE AUTOCONSUMOS</u></STRONG></td>
    </tr>
    <tr>
      <td id="table" class="producto"><font size="2"><strong>DOCUMENTO</strong></font></td>
      <td id="table" class="precio"><font size="2"><strong>TOTAL</strong></font></td>
    </tr>
    @foreach($autoconsumos as $ac)
    <tr>
      <td id="table" class="producto"><font size="2">{{$ac->tdodes}} {{$ac->serdoc}}-{{$ac->numdoc}}</font></td>
      <td id="table" class="precio"><font size="2">S/. {{number_format($ac->ccaitv, 2, '.', ',')}}</font></td>
    </tr>
    @endforeach
    <tr>
      <td colspan="2"><hr></td>
    </tr>
    <tr>
      <td id="table" class="producto"><strong><font size="2">TOTAL AUTOCONSUMOS</font></strong></td>
      <td id="table" class="precio"><strong><font size="2">S/. {{number_format($totalAutoconsumos, 2, '.', ',')}}</font></strong></td>
    </tr>
  </table> <br>

  <table style=" border-bottom: 0px;">
    <tr>
      <td colspan="3" style="text-align:center;"><STRONG><u>DETALLE PRODUCTOS AUTOCONSUMO</u></STRONG></td>
    </tr>
    <tr>
      <td id="table" class="producto"><font size="2"><strong>PRODUCTO</strong></font></td>
      <td id="table" class="cantidad"><font size="2"><strong>CANT.</strong></font></td>
      <td id="table" class="precio"><font size="2"><strong>TOTAL</strong></font></td>
    </tr>
    @foreach($ven_prod_autoconsumo as $vpa)
    <tr>
      <td id="table" class="producto"><font size="2">{{$vpa->pronom}}</font></td>
      <td id="table" class="cantidad"><font size="2">{{$vpa->CANTIDAD}}</font></td>

      <td id="table" class="precio"><font size="2">S/. {{number_format($vpa->CANTIDAD * $vpa->precio_unitario, 2, '.', ',')}}</font></td>


      
    </tr>
    @endforeach
  </table>
  <br>

</div>

</body>

</html>