
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
  <div class="ticket">
        <br><p class="centrado"><font size="3"><strong>RESTAURANT "PUEBLO LIBRE"</strong></font><br><font size="2">HUANG HE S.A.C<br><strong>Av. Sucre, Mariscal Antonio Jo NRO. 908</strong></font><font size="2"><BR>Pueblo Libre - Lima<BR>R.U.C: 20550174333<br>Tel: 449-2867<br>Nota de Crédito Electrónica<br>{{$cabecera->serdoc}}-{{$cabecera->numdoc}}<br>
		<br> <table id="table">
		<tr id="table">
			<td id="table">Fecha:</td>
			<td id="table">{{date('d-m-Y',strtotime($cabecera->ccafem))}}</td>
		</tr>
		<tr id="table">
			<td id="table">DNI:</td>
			<td id="table">{{$cabecera->clinum}}</td>
		</tr>
		<tr id="table">
			<td id="table">Señor:</td>
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
          <td class="precio" id="table" style="text-align:right;">{{$cabecera->ccatvg}}</td>
        </tr>
		<tr id="table">
          <td id="table" class="producto"></td>
          <td id="table" class="cantidad" style="width: 4cm;">IGV {{$cabecera->simbolo}}</td>
          <td id="table" class="precio">{{$cabecera->ccaigv}}</td>
        </tr>
		 <tr id="table">
          <td class="producto" id="table" ></td>
          <td class="cantidad" id="table" style="width: 4cm;">TOTAL {{$cabecera->simbolo}}</td>
          <td class="precio" id="table" style="text-align:right;">{{$cabecera->ccaitv}}</td>
        </tr>
      </tbody>
    </table>
     <p class="centrado" style="margin-top:160px;">Representación Impresa de la NOTA DE CRÉDITO ELECTRÓNICA<br>
	Para ver el documento visita https://econosystemsperu.pse.pe/20505641770<br>
	Emitido mediante un PROVEEDOR Autorizado por la SUNAT<br>
	Mediante Resolución de Intendencia No. 034-005-0005315<br>
	</p><br>
	<p>{{$cabecera->codhash}}</p>
  </div>
   
</body>

</html>