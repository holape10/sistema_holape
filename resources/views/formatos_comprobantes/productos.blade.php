
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
  <center> <br><p class="centrado"><font size="4"><strong>{{$empresa->NomEmpresa}}</strong></font><br><font size="4"><strong>{{$empresa->DirEmpresa}}</strong></font><font size="4"><BR>R.U.C: {{$empresa->IdEmpresa}}<BR>BR>Desde : {{$fecin}} - Hasta : {{$fecfin}}</p></center>
	

    
  <table style=" border-bottom: 0px;">
      <thead>
        <tr>
          <th colspan="6"><CENTER><STRONG>CONSOLIDADOS DE PRODUCTOS</STRONG></CENTER></th>
        </tr>
        <tr>
         <th class="producto">COD</th>
          <th class="producto">DESCRIPCION</th>
          <th class="producto">STOCK</th>
           <th class="producto">CANTIDAD</th>
          <th class="poducto">P.UNI</th>
           <th class="producto">TOTAL</th>
        
        </tr>
      </thead>
      <tbody id="table">
        @foreach($productos as $producto)
        <tr>
          <td id="table" class="producto"><font size="4">{{$producto->procod}}</font></td>
          <td id="table" class="producto"><font size="4">{{$producto->cdedes}}</font></td>
          <td id="table" class="poducto"><font size="4">{{number_format($producto->stock,2,'.','')}}</font></td>
          <td id="table" class="poducto"><font size="4">{{number_format($producto->cantidad,2,'.','')}}</font></td>
          <td id="table" class="producto"><font size="4">{{number_format($producto->cdepuni,2,'.','')}}</font></td>
          <td id="table" class="poducto"><font size="4">{{number_format($producto->cantidad*$producto->cdepuni,2,'.','')}}</font></td>
        </tr>
    
	 @endforeach
   <tr>

          <td colspan='5' id="table" class="producto"><font size="4"><STRONG>TOTAL</STRONG></font></td>
           <td  id="table" class="producto"><font size="4">{{number_format($total,2,'.','')}}</font></td>
       
 
    </tr>
		</tbody>
	</table> <br><Br>



	
	 <table style=" border-bottom: 0px;">
      <thead>
       
      </thead>
      <tbody id="table">
         
		
		
		  
		</tbody>

   
  </div>
   
</body>

</html>