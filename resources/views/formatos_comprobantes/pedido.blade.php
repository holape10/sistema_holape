
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
     <br>PEDIDO{{$cabecera->numdoc}}<br></p>
    
   
    	 <p><font size="3">Fecha: {{$cabecera->ccafem}}</p>
		   <p><font size="3">Cliente: {{$cabecera->ccanom}}</p>
          <p><font size="3"><strong>TOTAL: S/. {{$cabecera->ccaitv}}</strong></p>
     
 <table style=" border-bottom: 0px;">
      <thead>

        <tr>
          <th class="producto" colspan="3"><center><strong>Concepto</strong></center></th>
        </tr>
        <tr>
          <th class="cantidad" ><center><strong>Cantidad</strong></center></th>
             
                <th class="precio"><center><strong>P.U</strong></center></th>
                   <th class="precio" ><center><strong>TOTAL</strong></center></th>

        </tr>
      </thead>
      <tbody id="table">
         @foreach($detalle as $det)

         <tr>
              <td class="producto" colspan="3"><font size="2">{{$det->cdedes}}</font></td>
         </tr>
        <tr>

        <td class="cantidad" ><font size="3"><center>{{$det->cdecan}}</center></font></td>
      
          <td class="precio" ><font size="3">{{number_format($det->cdepuni,2,'.','')}}</font></td>
           <td class="precio" ><font size="3">{{number_format($det->cdepve,2,'.','')}}</font></td>

        </tr>
       @endforeach
     </tbody>
   </table>
</body>

</html>