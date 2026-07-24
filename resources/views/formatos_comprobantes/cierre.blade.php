
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
<body>
  <?php       $fecha=now();
        $hora = date("h:j:s", strtotime($fecha));?>
<div style="margin-top:-45px" class="ticket">
   <br><p class="centrado"><font size="3"><strong>{{$empresa->NomEmpresa}}</strong></font><br><font size="2"><strong>{{$empresa->DirEmpresa}}</strong></font><font size="2"><BR>R.U.C: {{$empresa->IdEmpresa}}<BR>VENTAS DEL D&IACUTE;A<BR>Desde : {{$fecin}} - Hasta : {{$fecfin}}</p>
  

    
  <table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th colspan="2"><CENTER><STRONG>VENTAS</STRONG></CENTER></th>
        </tr>
        <tr>
          <th class="producto">DESCRIPCION</th>
          <th class="cantidad">TOTAL</th>
        
        </tr>
      </thead>
      <tbody id="table">
        
        <tr>
          <td id="table" class="producto"><font size="2">MASTERCARD</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalmastercard,2,'.','')}}</font></td>
 
        </tr>
        <tr>
          <td id="table" class="producto"><font size="2">VISA</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalvisa,2,'.','')}}</font></td>
 
        </tr>
        <tr>
          <td id="table" class="producto"><font size="2">EFECTIVO</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalefectivo,2,'.','')}}</font></td>
        </tr>
        <tr>
          <td id="table" class="producto"><strong><font size="2">TOTAL</font></strong></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($total,2,'.','')}}</font></td>
        </tr>
         <tr>
          <td id="table" class="producto"><strong><font size="2"></font></strong></td>
          <td id="table" class="cantidad"><font size="2"></font></td>
        </tr>

        <tr>
          <td id="table" class="producto"><font size="2">MASTERCARD CAJA</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalcajamastercard,2,'.','')}}</font></td>
 
        </tr>
        <tr>
          <td id="table" class="producto"><font size="2">VISA CAJA</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalcajavisa,2,'.','')}}</font></td>
 
        </tr>
        <tr>
          <td id="table" class="producto"><font size="2">EFECTIVO CAJA</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalcajaefectivo,2,'.','')}}</font></td>
        </tr>
        <tr>
          <td id="table" class="producto"><strong><font size="2">TOTAL CAJA</font></strong></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalcaja,2,'.','')}}</font></td>
        </tr>
         <tr>
          <td id="table" class="producto"><strong><font size="2"></font></strong></td>
          <td id="table" class="cantidad"><font size="2"></font></td>
        </tr>

        <tr>
          <td id="table" class="producto"><font size="2">DELIVERY</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totaldelivery,2,'.','')}}</font></td>
 
        </tr>
  
    </tbody>
  </table> <br><Br>

   <table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th colspan="2"><CENTER><STRONG>INGRESOS</STRONG></CENTER></th>
        </tr>
        <tr>
          <th class="producto">DESCRIPCION</th>
          <th class="cantidad">TOTAL</th>
        
        </tr>
      </thead>
      <tbody id="table">
        
        @foreach($totalingresosdetalle as $totalingresodetalle)
        <tr>
          <td id="table" class="producto"><font size="2">{{$totalingresodetalle->det_gasto}}</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalingresodetalle->total,2,'.','')}}</font></td>
 
        </tr>
        @endforeach
        <tr>
          <td id="table" class="producto"><font size="2"><STRONG>TOTAL INGRESOS</STRONG></font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalingresos,2,'.','')}}</font></td>
        </tr>
    </tbody>
  </table><br><br>


   <table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th colspan="2"><CENTER><STRONG>SALIDAS</STRONG></CENTER></th>
        </tr>
        <tr>
          <th class="producto">DESCRIPCION</th>
          <th class="cantidad">TOTAL</th>
        
        </tr>
      </thead>
      <tbody id="table">
        
        @foreach($totalgastosdetalle as $totalgastodetalle)
        <tr>
          <td id="table" class="producto"><font size="2">{{$totalgastodetalle->det_gasto}}</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalgastodetalle->total,2,'.','')}}</font></td>
 
        </tr>
        @endforeach
        <tr>
          <td id="table" class="producto"><font size="2"><STRONG>TOTAL GASTOS</STRONG></font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalgastos,2,'.','')}}</font></td>
        </tr>
    </tbody>
  </table><br><br>

  <table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th colspan="2"><CENTER><STRONG>RESUMEN</STRONG></CENTER></th>
        </tr>
        <tr>
          <th class="producto">DESCRIPCION</th>
          <th class="cantidad">TOTAL</th>
        
        </tr>
      </thead>
      <tbody id="table">
        <tr>
          <td id="table" class="producto"><font size="2">INGRESOS</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalingresos+$totalefectivo,2,'.','')}}</font></td>
        </tr>
        <tr>
          <td id="table" class="producto"><font size="2">SALIDAS</font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format($totalgastos,2,'.','')}}</font></td>
        </tr>
        <tr>
          <td id="table" class="producto"><font size="2"><STRONG>TOTAL FINAL</STRONG></font></td>
          <td id="table" class="cantidad"><font size="2">{{number_format(($totalcajaefectivo+$totalcajavisa+$totalcajamastercard+$totalingresos)-$totalgastos,2,'.','')}}</font></td>
        </tr>
    </tbody>
  </table>


  
   <table style=" border-bottom: 0px;">
      <thead>
       
      </thead>
      <tbody id="table">
         
    
    
      
    </tbody>

   
  </div>
   
</body>

</html>