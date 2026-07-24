
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
    
    <br><p class="centrado"><font size="3"><strong>PRE-CUENTA<BR><br>
      @if(isset($mesa))
      {{$mesa->mes_nom}}
      @endif</strong></font><br></p>
         <p><font size="3"><BR>Fecha: {{$cab_pedido->fecha_hora}}<BR></p>
		
 <table style=" border-bottom: 0px;">
      <thead>
        <tr>
         <th class="producto"><center>Concepto</center></th>
          <th class="cantidad"><center>Cant.</center></th>
          <th class="precio"><center>PU</center></th>
           <th class="precio"><center>Importe</center></th>
        </tr>
      </thead>
      <tbody id="table">
      @foreach($detalle as $det)
        <tr>
         <td class="producto">{{$det->pronom}}</td>
         <td class="cantidad"><center>{{$det->cantidad}}</center></td>
         <td class="cantidad"><center>{{$det->propunitem}}</center></td>
         <td class="precio"><center>{{$det->totalitem}}</center></td>
		 
        </tr>
       @endforeach
        <tr id="table">
          <td colspan="2" class="producto" id="table">CONSUMO TOTAL S/</td>
          <td class="producto" id="table" ></td>
          <td class="precio" id="table" style="text-align:right;"><center>{{$cab_pedido->total}}</center></td>
        </tr>
     </tbody>
  </table>
  <br><br><font>RUC / DNI:</font><hr /><br>
  <hr />
  <font>Raz. Social / Nombre:</font><hr/><br>
  <hr />
  <font>Direcci&oacute;n:</font><hr /><br><br>
  <hr />
  <input type="checkbox"> Boleta<br>
  <input type="checkbox"> Factura<br>
</body>

</html>