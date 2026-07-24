<html lang="es">

<head>
  <style>
  * {

  font-family: 'Arial Black';
}


#table {
border: 0px ;
}

td,
th,
tr,
table {
  border-top: 0px solid black;
     border-bottom: 0px solid black;
  border-collapse: collapse;

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

<body >

  <div class="ticket">
  				<table  style="width:100%;">
  					<thead>
  						<tr>
  							<th colspan="6" style="text-align:center;font-size:8pt;">{{$empresa->NomEmpresa}}<br>{{$empresa->IdEmpresa}}<br>{{$data_sucursal->direccion}}</th>
  						</tr>
  					</thead>
  				</table>

  				<table  style="width:100%;">
							<thead >
								
								<tr>
									<th colspan="5" style="text-align:center;font-size:8pt"><center><strong>BOLETAS DE VENTAS <br>DESDE {{Carbon::parse($fecin)->format('d-m-Y')}} HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}} </strong></center></th>
								</tr>
								<tr>
									<th colspan="5"><hr></th>
								</tr>
							
								
								<tr>
									<th style="text-align:center;font-size:8pt;width:400px;">FECHA</th>
									<th style="text-align:center;font-size:8pt">COMP.</th>
									<th style="text-align:center;font-size:8pt">SERIE</th>
									<th style="text-align:center;font-size:8pt">NUMERO</th>
									<th style="text-align:center;font-size:8pt">TOTAL</th>
								</tr>

								<tr>
									<th style="text-align:center;font-size:8pt;width:400px;">CANTIDAD</th>
									<th style="text-align:center;font-size:8pt">PRODUCTO</th>
								</tr>
							</thead>
							<tbody>
								@foreach($boletas as $bol)
								<tr>
									<td style="text-align:center;width:400px;font-size:8pt">{{Carbon::parse($bol->fecha)->format('d-m-Y')}}</td>
									<td style="text-align:center;width:100px;font-size:8pt">{{$bol->des_doc}}</td>
									<td style="text-align:center;font-size:8pt">{{$bol->serie}}</td>
									<td style="text-align:center;font-size:8pt">{{$bol->numero}}</td>
									<td style="text-align:right;font-size:8pt;width:200px">{{number_format($bol->cdevve,'2','.','')}}</td>

								</tr>
								<tr>
									<td style="text-align:center;font-size:8pt">{{$bol->cdecan}}</td>
									<td style="text-align:left;font-size:8pt" colspan="4">{{$bol->cdedes}}</td>
		

								</tr>
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								@endforeach
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								<tr>
									<td colspan="4" style="text-align:center;font-weight:bold;text-align:right;font-size:8pt">TOTAL</td>
									<td style="text-align:right;font-weight:bold;font-size:8pt">{{number_format($total_boletas,'2','.','')}}</td>
								</tr>
							
							</tbody>
						</table>
				

						<br>
						<table  style="width:100%;">
							<thead >
								
								<tr>
									<th colspan="5" style="text-align:center;font-size:8pt"><center><strong>FACTURAS DE VENTAS <br>DESDE {{Carbon::parse($fecin)->format('d-m-Y')}} HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}} </strong></center></th>
								</tr>
								<tr>
									<th colspan="5"><hr></th>
								</tr>
							
								<tr>
									<th style="text-align:center;font-size:8pt;width:400px;">FECHA</th>
									<th style="text-align:center;font-size:8pt">COMP.</th>
									<th style="text-align:center;font-size:8pt">SERIE</th>
									<th style="text-align:center;font-size:8pt">NUMERO</th>
									<th style="text-align:center;font-size:8pt">TOTAL</th>
								</tr>

								<tr>
									<th style="text-align:center;font-size:8pt;width:400px;">CANTIDAD</th>
									<th style="text-align:center;font-size:8pt">PRODUCTO</th>
								</tr>
							</thead>
							<tbody>
								@foreach($facturas as $fac)
								<tr>
									<td style="text-align:center;width:400px;font-size:8pt">{{Carbon::parse($fac->fecha)->format('d-m-Y')}}</td>
									<td style="text-align:center;width:100px;font-size:8pt">{{$fac->des_doc}}</td>
									<td style="text-align:center;font-size:8pt">{{$fac->serie}}</td>
									<td style="text-align:center;font-size:8pt">{{$fac->numero}}</td>
									<td style="text-align:right;font-size:8pt;width:200px">{{number_format($fac->cdevve,'2','.','')}}</td>

								</tr>
								<tr>
									<td style="text-align:center;font-size:8pt">{{$fac->cdecan}}</td>
									<td style="text-align:left;font-size:8pt" colspan="4">{{$fac->cdedes}}</td>

								</tr>
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								@endforeach
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								<tr>
									<td colspan="4" style="text-align:center;font-weight:bold;text-align:right;font-size:8pt">TOTAL</td>
									<td style="text-align:right;font-weight:bold;font-size:8pt">{{number_format($total_facturas,'2','.','')}}</td>
								</tr>
							
							</tbody>
						</table>
				


					

     
  </div>



</body>
</html>

