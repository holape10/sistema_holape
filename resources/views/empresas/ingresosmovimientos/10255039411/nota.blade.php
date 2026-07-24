
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
  max-width: 8cm;
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
  <div style="margin-top:-45px" class="ticket">
      <br><p class="centrado"><font size="5"><strong>BOTICA T-PHARMA</strong></font>
    <br><p class="centrado"><font size="2"><strong> LUQUE UTURUNCO LOLI PILAR</strong></font><br><font size="2"></font><font size="2"><BR><br>R.U.C: 10255039411<br>TICKET<br>{{$cabecera->serdoc}}-{{$cabecera->numdoc}}<br></p>
		
    <br><table id="table">
		<tr id="table">
			<td id="table">Fecha:</td>
			<td id="table">{{date('d-m-Y',strtotime($cabecera->ccafem))}}</td>
		</tr>
		<tr id="table">
			<td id="table">DNI:</td>
			<td id="table">{{$cabecera->clinum}}</td>
		</tr>
		<tr id="table">
			<td id="table">Se&ntilde;or:</td>
			<td id="table">{{$cabecera->clinom}}</td>
		</tr>
		<tr id="table">
			<td id="table">Direcci&oacute;n:</td>
			<td id="table">{{$cabecera->clidir}}</td>
		</tr>
	</table><br><br>
	<table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th class="producto">Concepto</th>
          <th class="cantidad">Cantidad</th>
          <th class="precio">Importe</th>
        </tr>
      </thead>
      <tbody id="table">
         @foreach($detalle as $det)
        <tr>
          <td id="table" class="producto">{{$det->cdedes}}</td>
          <td id="table" class="cantidad">{{number_format($det->cdecan,2,'.','')}}</td>
          <td id="table" class="precio">{{number_format($det->cdevve,2,'.','')}}</td>
        </tr>
       @endforeach
	   <tr id="table">
          <td class="producto" id="table"></td>
          <td class="cantidad"id="table" style="width: 4cm;">SUBTOTAL {{$cabecera->simbolo}}</td>
          <td class="precio" id="table" style="text-align:right;">{{$cabecera->ccaitv}}</td>
        </tr>
		 <tr id="table">
          <td class="producto" id="table" ></td>
          <td class="cantidad" id="table" style="width: 4cm;">TOTAL {{$cabecera->simbolo}}</td>
          <td class="precio" id="table" style="text-align:right;">{{$cabecera->ccaitv}}</td>
        </tr>
      </tbody>
    </table>
    <p class="centrado" style="margin-top:160px;">Este ticket no es comprobante de pago, si requiere por favor solicitarlo en caja
  </div>
   
</body>

</html>