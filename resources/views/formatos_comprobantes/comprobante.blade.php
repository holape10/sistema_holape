<!doctype html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
	       
	    <link rel="stylesheet" href="css/bootstrap1.min.css">
	    <style>
			.page-break {
				page-break-after: always;
			}

	 		.panel-default > .panel-heading {
				  background-color: #9C640C;
			}

			.width-table {
				width:30px;
			}

			.margen-div {
				margin-top:-20px;
			}

			th, td {
			    padding: 1px;
			}

			

			.margen-top-obs {
				position:relative;
				top: 688px;
				
			}

			
			.margen-top-pago {
				position:relative;
				top: 690px;
			}

			
			.margen-top-total {
				position:relative;
				left:82px;
				
			}

			.margen-top-hash {
				position:fixed;
				bottom:0px;
				/*top: 72.5em;*/
			}

		


	 	</style>
 	</head>
	<body style="font-family:sans-serif;font-size:9";>
		<div class="margen-div">
			<div class="row">

		     	<div class="row">
					<div class="col-xs-6 text-center" style="font-family:sans-serif;font-size:9";>
						<img src="{{asset('imagenes/logos/logo.png')}}"  style="padding-left:0px;" width="80%"><br>
						<font style="font-family:sans-serif;font-size:11";><STRONG>{{$empresa->NomEmpresa}}</STRONG></font><br>
						<font style="font-family:sans-serif;font-size:8">{{$sucursal->direccion}} </font><br>
						<font style="font-family:sans-serif;font-size:8">TEL&Eacute;FONO: {{$sucursal->telefono}}</font><br>
						<font style="font-family:sans-serif;font-size:8">E-MAIL: {{$sucursal->correo}}</font>
						
					</div>
					<div class="col-xs-4 col-xs-offset-1 text-center">
						<div class="panel panel-default" style="border-color:black;height:140px;">
							<div class="panel-body">
								<font style="font-family:sans-serif;" size='17'><strong>RUC {{$empresa->IdEmpresa}}</strong></font><br>
								 <font style="font-family:sans-serif;" size='17'><strong>{{$cabpdf->tdodes}}</strong></font><br>
								<font style="font-family:sans-serif;" size='17'><strong>{{$cabpdf->serdoc}}-{{str_pad($cabpdf->numdoc,8,"0",STR_PAD_LEFT)}}</strong></font><br>
							</div>
						</div>
 					</div>
 				</div><br>
		     	

				<div class="row margen-top-det">
					<div class="col-xs-12">
						<div class="panel panel-default" style="border-color:black;width:728px;">
							<div class="panel-body">
							 	<table style="width:100%">
							 		<tr>
							 		   <td align="top" style="width:25%;"><font style="font-family:sans-serif;"><strong>Fecha de emisión:</strong></font><font style="font-family:sans-serif;"> {{date('d-m-Y',strtotime($cabpdf->ccafem))}}</font></td>
							 		   <td align="top" style="width:25%;"><font style="font-family:sans-serif;"><strong>Placa:</strong></font><font style="font-family:sans-serif;"> {{$cabpdf->placa}}</font></td>
							 		</tr>
							 		<tr>
							 			<td align="top" style="width:75%;"><font style="font-family:sans-serif;"><strong>Señor(es):</strong></font><font style="font-family:sans-serif;"> {{$cabpdf->ccanom}}</font></td>
							 			
							 			<td align="top" style="width:25%;"><font style="font-family:sans-serif;"><strong>Marca:</strong></font><font style="font-family:sans-serif;">@if(!empty($vehiculo)) {{$vehiculo->mar_nom}} @endif</font></td>

							 		</tr>
							 		<tr>
							 			
									  <td style="width:75%;"><font style="font-family:sans-serif;"><strong>RUC/DNI:</strong></font><font style="font-family:sans-serif;"> {{$cabpdf->ccandi}}  </font></td>
									  <td align="top" style="width:25%;"><font style="font-family:sans-serif;"><strong>Modelo:</strong></font><font style="font-family:sans-serif;">@if(!empty($vehiculo)){{$vehiculo->mod_nom}} @endif </font></td>
							 		</tr>
							 		<tr>
							 			<td style="width:75%;"><font style="font-family:sans-serif;"><strong>Dirección:</strong></font><font style="font-family:sans-serif;"> {{$cabpdf->clidirfac}}  </font></td>
							 			 <td align="top" style="width:25%;"><font style="font-family:sans-serif;"><strong>Kilómetros:</strong></font><font style="font-family:sans-serif;">@if(!empty($vehiculo)){{$vehiculo->kilometros}} @endif </font></td>
							 		
							 			
							 		</tr>
								
							 		
							 	</table>
								
							</div>
						</div>
					</div>
				</div>
				
			
			 	
			 	<div class="row">
					<div class="col-xs-12">
											<table border="2"  class="table table-bordered" style="border-color:black;width:100%;">
										<thead style="">
											<tr>
												<th style="border-color:black;"  style="width:7%;"><center><font color="black" size='1'>ITEM</font></center></th>
												<th style="border-color:black;" style="width:8%;"><center><font color="black" size='1'>CANT.</font></center></th>
												<th style="border-color:black;" style="width:52%;"><center><font color="black" size='1'>DESCRIPCIÓN</font></center></th>
												
												<th style="border-color:black;" style="width:8%;"><center><font color="black" size='1'>P.UNI</font></center></th>
												<th style="border-color:black;" style="width:8%;"><center><font color="black" size='1'>P.VENTA</font></center></th>
											</tr>
						
										</thead>
										<tbody>
											<?php $i=0; ?>
											@foreach($detpdf as $det)
											<?php $i=$i+1; ?>
											<tr>
												<td style="border-color:black;" style="width:7%;"><center><font size='1'><?php echo $i;?></font></center></td>
												<td style="border-color:black;" style="width:8%;"><center><font size='1'>{{$det->cdecan}}</font></center></td>
												<td style="border-color:black;" style="width:52%;"><font size='1'> {{$det->cdedes}}</font></td>
												
												<td style="border-color:black;" style="width:8%;" class=" text-right "><center><font size='1'>{{number_format($det->cdepuni,'2','.',',')}}</font></center></td>
												<td style="border-color:black;" style="width:8%;" class=" text-right "><center><font size='1'>{{number_format($det->cdevve,'2','.',',')}}</font></center></td>
											</tr>
											@endforeach
										
										</tbody>
									</table>
					</div>
				</div>

			  <div class="row margen-top-obs">
					<div class="col-xs-12">
						<div class="panel panel-default" style="border-color:black;width:728px;height:35px;font-size:8">
							<div class="panel-body" style="height:40px;">
								<div>
									<font>SON: {{$totalletras}} {{$cabpdf->monnom}}</font><br><br>
							</div>
							</div>
						</div>
					</div>
				</div>

			<div class="row margen-top-pago">
				
					<div class="col-xs-5">
						<div class="panel panel-default" style="border-color:black;width:400px;height:200px;font-size:8">
							<div class="panel-body">
								
								CUENTA CORRIENTE BBVA - SOLES 	N° 001101970100043486
								CUENTA CORRIENTE BBVA - D&Oacute;LARES N° 001103280100017680
								<br><br><center><img src="{{asset($imgqr)}}" width="80px" height="80px"></center><br><br>
								<strong>OBSERVACI&Oacute;N:</strong> {{$cabpdf->nota}}
									
									
							</div>
						</div>
					</div>
					<div class="col-xs-5">
						<div class="panel panel-default margen-top-total" style="border-color:black;width:300px;height:200px;font-size:8">
							<div class="panel-body">
								<table style="width:90%" >
								
									<tr>	
										<td class="width-table text-left" style="font-size:11"><strong>Subtotal</strong></td>
										<td class="width-table text-right">@if($cabpdf->moncod=='USD') $ @else S/ @endif{{number_format($cabpdf->ccatvg,'2','.',',')}}</td>
									</tr>
									<tr>
										<td class="width-table text-left" style="font-size:11"><strong>IGV 18%</strong></td>
										<td class="width-table text-right">@if($cabpdf->moncod=='USD') $ @else S/ @endif{{number_format($cabpdf->ccaigv,'2','.',',')}}</td>
									</tr>
									<tr>
										<td class="width-table text-left" style="font-size:11"><strong>Desc %</strong></td>
										<td class="width-table text-right">{{number_format($cabpdf->ccadespor,'2','.',',')}}%</td>
									</tr>
								
									<tr>
										<td class="width-table text-left" style="font-size:11"><strong>Total</strong></td>
										<td class="width-table text-right" style="font-size:11" ><strong>@if($cabpdf->moncod=='USD') $ @else S/ @endif{{number_format($cabpdf->ccaitv,'2','.',',')}}</strong></td>
									</tr>
									
								</table>
							</div>
						</div>
					</div>
		
				
			</div>


	
	
			</div>
		</div>
	</body>
</html>