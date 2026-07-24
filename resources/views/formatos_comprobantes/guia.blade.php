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

			.margen-top-total {
				position:fixed;
				left:35.5em;
				top: 69em;
			}

			.margen-top-obs {
				position:fixed;
				
				top: 77 em;
			}

			.margen-top-pago {
				position:fixed;
				
				top: 69em;
			}

			.margen-top-totlet {
				position:fixed;
				top: 80.5em;
			}

			.margen-top-hash {
				position:fixed;
				top: 84.5em;
			}



	 	</style>
 	</head>
	<body style="font-family:sans-serif;font-size:9";>
		<div class="margen-div">
			<div class="row">
		     	<div class="row">
					<div class="col-xs-6 text-center" style="font-family:sans-serif;font-size:9";>
						@if(!empty($empresa->LogEmpresa))
						<img src="{{asset('imagenes/logos/logo_elvia.PNG')}}"  style="padding-left:0px;" width="70%"><br><br>
						@endif

						<font style="font-family:sans-serif;font-size:12";><STRONG>{{$empresa->NomEmpresa}}<STRONG></font><br>
						<font style="font-family:sans-serif;">{{$empresa->DirEmpresa}} </font><br>
					
					
						
					</div>
					<div class="col-xs-4 col-xs-offset-1 text-center">
						<div class="panel panel-default" style="height:140px;">
							<div class="panel-body">
								<font style="font-family:sans-serif;" size='15'><strong>RUC {{$empresa->IdEmpresa}}</strong></font><br>
								 <font style="font-family:sans-serif;" size='16'><strong>Gu&iacute;a Remisi&oacute;n</strong></font><br>
								<font style="font-family:sans-serif;" size='15'><strong>{{$cabpdf->serieguia}}-{{str_pad($cabpdf->numeroguia,8,"0",STR_PAD_LEFT)}}</strong></font><br>
							</div>
						</div>
 					</div>
 				</div><br>
		     	
				<div class="row margen-top-det">
					<div class="col-xs-12">
						<div class="panel panel-default" style="width:728px;">
							<div class="panel-body">
							 	<table style="width:100%">
							 		<tr>
							 			<td valign="top" style="width:100%;"><font style="font-size:8;font-family:sans-serif;"><strong>Fecha de Inicio de Traslado:</strong></font><font style="font-size:8;font-family:sans-serif;"> {{$cabpdf->fechatraslado}}</font></td>
							 		</tr>
							 		<tr>
							 			<td valign="top" style="width:25%;"><font style="font-size:8;font-family:sans-serif;"><strong>Destinatario:</strong></font><font style="font-size:8;font-family:sans-serif;"> {{$cabpdf->nomcliente}}</font></td>
							 			<td valign="top" style="font-size:8;width:100%;"><font style="font-size:8;font-family:sans-serif;"><strong>Punto Partida:</strong></font><font style="font-size:8;font-family:sans-serif;"> {{$cabpdf->direccionpartida}} {{$cabpdf->ubipartida}}</font></td>
										
							 		</tr>
							 		<tr>
							 			<td style="font-size:8;width:25%;"><font style="font-size:8;font-family:sans-serif;"><strong>RUC:</strong></font><font style="font-size:8;font-family:sans-serif;"> {{$cabpdf->ruccliente}}  </font></td>
							 			<td valign="top" style="font-size:8;width:100%;"><font style="font-size:8;font-family:sans-serif;"><strong>Punto Llegada:</strong></font><font style="font-size:8;font-family:sans-serif;"> {{$cabpdf->direccionllegada}} {{$cabpdf->ubillegada}}</font></td>
							 			
							 		</tr>
							 		<tr>
							 			<td style="font-size:8;width:100%;"><font style="font-size:8;font-family:sans-serif;"><strong>Motivo:</strong></font><font style="font-size:8;font-family:sans-serif;"> {{$cabpdf->motivo}}  </font></td>
							 			
							 			
							 		</tr>
									
							 		
							 	</table>
								
							</div>
						</div>
					</div>
				</div>
			
				<div class="row">
					<div class="col-xs-12">
									<table border="1" class="table table-bordered" style="width:100%;">
									<thead style="">
										<tr>
											<th style="width:5%;font-size:8"><center><font color="black">N°</font></center></th>
											<th style="width:5%;font-size:8"><center><font color="black">CODIGO</font></center></th>	
											<th style="width:43%;font-size:8"><center><font color="black">DESCRIPCIÓN</font></center></th>
											<th style="width:8%;font-size:8"><center><font color="black">CANTIDAD</font></center></th>
											<th style="width:8%;font-size:8"><center><font color="black">UNIDAD DE<BR>DESPACHO</font></center></th>
										
										</tr>
					
									</thead>
									<tbody>
										<?php $i=0; ?>
										@foreach($detpdf as $det)
										<?php $i=$i+1; ?>
										<tr>
											<td style="width:5%;font-size:7"><center>{{$i}}</center></td>
											<td style="width:5%;font-size:7"><center>{{$det->procod}}</center></td>
											<td style="width:43%;font-size:7">{{$det->pronom}}</td>
											<td style="width:8%;font-size:7" class=" text-right "><center>{{$det->cantidad}}</center></td>
											<td style="width:8%;font-size:7" class=" text-right "><center>{{$det->umenom}}</center></td>
										
										</tr>
										@endforeach
									</tbody>
								</table>
								
					</div>
				</div>
				
			
				<div class="row margen-top-pago">
					<div class="col-xs-5">
						<div class="panel panel-default" style="width:420px;height:80px">
							<div class="panel-body">
								<strong>Unidad de Transporte Conductor</strong>	<br>
								<strong>Placa del Vehiculo:</strong> {{$cabpdf->placa}}<br>
								<strong>DNI del Conductor:</strong> {{$cabpdf->rucconductor}}<BR><BR>

								
									
							</div>
								
						</div>
					</div>
				</div>
				
				<div class="row margen-top-total">
					<div class="col-xs-5">
						<div class="panel panel-default" style="width:300px;height:80px">
							<div class="panel-body">
								<STRONG>Modalidad de Transporte:</STRONG> {{$cabpdf->modalidad}}<br>
								<strong>Peso Total Aprox. (KGM):</strong> {{$cabpdf->pesobruto}}<br>
							</div>
						</div>
					</div>
				</div>
					<div class="row margen-top-obs">
					<div class="col-xs-12">
						<div class="panel panel-default" style="width:728px;height:80px;">
							<div class="panel-body" style="height:80px;">
								
								
								<STRONG>Nombre:</STRONG>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<STRONG>Conformidad del Cliente: ________________________________</strong><br>
								<strong>DNI:</strong> <br>
							</div>
						</div>
					</div>
				</div>
			 
				<div class="row margen-top-hash">
					<div class="col-xs-12">
						<div class="panel panel-default" style="width:728px;height:40px;">
							<div class="panel-body" style="height:40px;">
								<table>
									<tr>
										<td>Representación impresa de la Gu&iacute;a Electr&oacute;nica | {{$cabpdf->codhash}}</td><br><br>
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