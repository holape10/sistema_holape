
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
  width: 8cm;
  text-align:left;
  max-width: 8cm;
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
     <br><p class="centrado"><font size="3"><strong>{{$empresa->NomEmpresa}}</strong></font><br><font size="2"><strong>{{$sucursal->direccion}}</strong></font><font size="2"><BR>R.U.C: {{$empresa->IdEmpresa}}<BR>TICKET {{$cab_pedido->ped_id}}<br></p>
    
    <p><center><font size="3"><BR>{{$mesa->mes_nom}}</center></p>
    	 <p><font size="3">Fecha: {{$cab_pedido->fecha_hora}}</p>
		   <p><font size="3">Cliente: {{$cab_pedido->ped_cli_nom}}</p>

 <table style=" border-bottom: 0px;">
      <thead>

        <tr>
          <th class="producto" colspan="3"><center>Concepto</center></th>

        </tr>
      </thead>
      <tbody id="table">
         @foreach($detalle as $det)
        <tr>
                  <td class="producto" colspan="3"><font size="3">{{$det->ped_det_can}} | {{$det->descripcion}}</font></td>

        </tr>
       @endforeach
     </tbody>
   </table>
</body>

</html>