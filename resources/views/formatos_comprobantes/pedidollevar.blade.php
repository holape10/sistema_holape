
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
    <br><p><font size="4"><BR>Pedido Para Llevar: {{$cab_pedido->cliente}}<BR>

       <font size="4"><BR>Fecha: {{$cab_pedido->fecha_hora}}<BR>
        <font size="4"><BR>Tipo: {{$cab_pedido->apli_nom}}<BR></p>
		 
 <table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th class="producto"><center>Concepto</center></th>
          <th class="cantidad"><center>Cant.</center></th>
           <th class="cantidad"><center>Obser.</center></th>
        </tr>
      </thead>
      <tbody id="table">
         @foreach($detalle as $det)
        <tr>
         <td class="producto">{{$det->pronom}}</td>
         <td class="cantidad"><center>{{$det->cantidad}}<center></td>
          <td class="producto">{{$det->detalle}}</td>
        </tr>
       @endforeach
     </tbody>
</body>

</html>