
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

  <div style="margin-top:-20px" class="ticket">
   

 <table style=" border-bottom: 0px;">
      <thead>
        <tr>
           <td colspan="3"><center><strong><font size="3">NOTA DE GASTO</font></strong></center></td>
        </tr>
        <tr>
          <th class="cantidad"><font size="2"><center>FECHA</center></font></th>
          <th class="producto"><font size="2"><center>DETALLE</center></font></th>
           <th class="precio"><font size="2"><center>TOTAL</center></font></th>
        </tr>
      </thead>
      <tbody id="table">
         @foreach($detalle as $det)
        <tr>
         <td class="cantidad"><font size="2">{{$det->gast_fec}}</font></td>
         <td class="producto"><center><font size="2">{{$det->det_gasto}}</font><center></td>
          <td class="precio"><font size="2">{{$det->total}}</font></td>
        </tr>
       @endforeach
       <br><br><br>
     </tbody>
   </table>
</body>

</html>