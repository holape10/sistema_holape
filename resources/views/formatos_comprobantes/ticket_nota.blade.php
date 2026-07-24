
<html>

<head>
  <style>
  * {

  font-family: 'Times New Roman';
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
  border-top: 1px solid black;
     border-bottom: 1px solid black;
  border-collapse: collapse;
  
}

td.producto,
th.producto {
  width: 6cm;
  text-align:left;
  max-width: 7cm;
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
  width: 2cm;
  max-width: 2cm;
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

<?php       
    $fecha=now();
    $hora = date("d-m-Y h:m:s", strtotime($fecha));
		$hoy = date("d-m-Y")

?>

<body>
  <div style="margin-top:-45px" class="ticket">
  <br><p class="centrado"><font size="3"><strong>{{$empresa->NomEmpresa}}</strong></font><br><font size="2"><strong>{{$sucursal->direccion}}</strong></font><font size="2"><BR>R.U.C:klkl {{$empresa->IdEmpresa}}<BR>Nota de Venta<br>{{$cabecera->serdoc}}-{{$cabecera->numdoc}}<br></p>
		

    <br><table id="table">
    <tr id="table">
      <td id="table"><font size="2">Fecha:</font></td>
        <td id="table"><font size="2"><?php echo $hoy;?></font></td>
        <td id="table"><font size="2">RUC:</font></td>
      <td id="table"><font size="2">{{$cabecera->clinum}}</font></td>
    </tr>
    <tr id="table">
      <td id="table"><font size="2">Raz. Social:</font></td>
      <td colspan="3" id="table"><font size="2"> {{$cabecera->clinom}}</font></td>  <tr id="table">
    </tr>
    </tr>
    <br>

  </table><br>
  
  <table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th class="producto"><font size="2">Concepto</font></th>
          <th class="cantidad"><font size="2">Cant.</font></th>
          <th class="precio"><font size="2">Importe</font></th>
        </tr>
      </thead>
      <tbody id="table">
         @foreach($detalle as $det)
        <tr>
          <td id="table" class="producto"><font size="2">{{$det->cdedes}}</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($det->cdecan,2,'.','')}}</font></td>
          <td id="table" class="precio"><font size="2">{{number_format($det->cdevve,2,'.','')}}</font></td>
        </tr>
       @endforeach
     @if( $cabecera->visa != 0.00)
    <tr id="table">
          <td class="producto" id="table"><font size="2">P.C VISA {{$cabecera->simbolo}}</font></td>
          <td class="cantidad"id="table" style="width: 4cm;"> </td>
          <td class="precio" id="table" style="text-align:right;">@if(empty($cabecera->visa)) 0.00 @else {{$cabecera->visa}} @endif</td>
        </tr>
     @endif
    @if($cabecera->mastercard != 0.00)
    <tr id="table">
          <td class="producto" id="table"><font size="2">P.C MASTER. {{$cabecera->simbolo}}</font></td>
          <td class="cantidad"id="table" style="width: 4cm;"> </td>
          <td class="precio" id="table" style="text-align:right;">{{$cabecera->mastercard}}</td>
        </tr>
         @endif
    @if($cabecera->efectivo != 0.00)
    <tr id="table">
          <td class="producto" id="table"><font size="2">P.C EFECTIVO {{$cabecera->simbolo}}</font> </td>
          <td class="cantidad" id="table" style="width: 4cm;"></td>
          <td class="precio" id="table" style="text-align:right;"><font size="2">@if(empty($cabecera->efectivo)) 0.00 @else {{$cabecera->efectivo}} @endif</font></td>
        </tr>
    @endif
     <tr id="table">
           <td class="producto" id="table"><font size="2">SUBTOTAL {{$cabecera->simbolo}}</font> </td>
          <td class="cantidad" id="table" style="width: 4cm;"></td>
          <td class="precio" id="table" style="text-align:right;"><font size="2">{{$cabecera->ccatvg}}</font></td>
        </tr>
    <tr id="table">
          <td class="producto" id="table"><font size="2">IGV {{$cabecera->simbolo}}</font> </td>
          <td class="cantidad" id="table" style="width: 4cm;"></td>
          <td id="table" class="precio"><font size="2">{{$cabecera->ccaigv}}</font></td>
        </tr>
     <tr id="table">
            <td class="producto" id="table"><font size="2">TOTAL {{$cabecera->simbolo}}</font> </td>
          <td class="cantidad" id="table" style="width: 4cm;"></td>
          <td class="precio" id="table" style="text-align:right;"><font size="2">{{$cabecera->ccaitv}}</font></td>
        </tr>
      </tbody>
    </table><br>
  <p><font size="2">{{$totalletras}}</p></font><br> 
  </div><br>.
</body>
</html>