<!doctype html>
<html lang="es">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
	       
	    <style>
			.page-break {
				page-break-after: always;
			}

	
			th, td {
			    padding: 1px;
			}

			
		


			.cabecera {
				position:absolute;
				top: -10px;
				width:100%;
				
			}

			.logo{
				position:absolute;
				top:0px;
				left:0px;
				width:30%;
				
			}

			.panel-logo{
				position:absolute;
				top:0px;
				left:170px;
				width:40%;
				text-align:center;

			}

			.panel-numeracion{
				position:absolute;
				border: 2px 2px 2px 2px solid  black;
				padding: s20px;
				border-radius: 10px 10px 10px 10px;
				top:0px;
				right: 0px;
				width:30%;
				text-align:center;
			}

			.panel-cliente{
				position: relative;
				top:160px;
				border: 0px 0px 0px 0px solid  black;
				padding: 10px;
				border-radius: 0px 0px 0px 0px;
			}

			.detalle-cliente{
				position: relative;
				top:160px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 10px 10px 10px 10px;
			}


			.detalle{
				position: relative;
				width:100%;
				top:170px;
			}


			.montoletras{
				position: absolute;
				top:730px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 5px 5px 5px 5px;
				width: 100%;
			}

			.totales{
				position: relative;
				top:190px;
				border: 1px 1px 1px 1px solid  black;
				border-radius: 5px 5px 5px 5px;
				width:100%;
				height:200px;
			}

			.hash{
				position: relative;
				top:180px;
			}


			.div-observacion{
				position:absolute;
				padding: 10px;
				top:0px;
				left:0px;
				width:30%;
				height:140px;

			}

			.div-aceptacion{
				position:absolute;
			
				padding: 10px;
				top:0px;
				left:230px;
				width:45%;
				height:140px;

			}

			.div-totales{
				position:absolute;
				padding: 10px;
				top:0px;
				right: 0px;
				width:25%;
				height:140px;
				
			}


			.table-detalle{
				border:1px 1px 1px 1px solid  black;
				border-radius:0px;
				width: 100%;
				table-layout:fixed;
				min-height:900px;
			}


			th, td {
    			width: 100px;
   				 word-wrap: break-word;
			}
		
			.comprobante{
				text-align:center;
				background-color:gray;
				width:100%;
				height:40px;

			}

			.qr{
				position:absolute;
				top:25px;
				left:5px;
				width:50%;
			}


			.letras{
				position:absolute;
				top:10px;
				left:5px;
				width:100%;
			}


			.observacion{
				position:absolute;
				top:25px;
				left:120px;
				width:60%;
				text-align:justify;			}




	 	</style>
 	</head>
	<body style="font-family:sans-serif">

		<div class="cabecera">

			<div class="logo">
				<img src="{{$sucursal->logosuc}}"  style="padding-left:0px;" width="150px" height="150px"><br>
			</div>
		
			<div class="panel-logo">
				<label style="font-family:sans-serif;font-size:13pt;font-style: oblique;">{{$sucursal->nombre_comercial}}</label><br>
					<label style="font-family:sans-serif;font-size:5pt;font-style: oblique;">De: {{$empresa->NomEmpresa}}</label><br>
				@if(!empty($sucursal->descripcion1))
				<label style="font-family:sans-serif;font-size:7;font-style: oblique;">{{$sucursal->descripcion1}}</label><br>
				@endif
				<label style="font-family:sans-serif;font-size:7;font-style: oblique;">{{$sucursal->direccion}} <br>
					{{$sucursal->distrito}} - {{$sucursal->provincia}} - {{$sucursal->departamento}}<BR>@if(!empty($sucursal->celular))  {{$sucursal->celular}}@endif @if(!empty($sucursal->correo)) Email: {{$sucursal->correo}} @endif
				 
				</label>
			
							
			</div>
			<div class="panel-numeracion">
				<br><font style="font-family:sans-serif;" size='3'><strong>RUC {{$empresa->IdEmpresa}}</strong></font><br><br>
				<div class="comprobante">
					<font style="font-family:sans-serif;" size='2' color="white"><strong>{{$cabpdf->tdodes}}</strong></font><br><br>
				</div><br>
				
				<font style="font-family:sans-serif;" size='3'><strong>{{$cabpdf->serieguia}}-{{str_pad($cabpdf->numeroguia,8,"0",STR_PAD_LEFT)}}</strong></font><br><br>
			</div>
		</div>
			
			<div class="panel-cliente">
				
			<font size="2" style="font-family:sans-serif;"><strong>Remitente:</strong></font><font size="2" style="font-family:sans-serif;"> {{$cabpdf->nomclienterem}}</font> - <font size="2" style="font-family:sans-serif;"><strong>{{$cabpdf->tdidesrem}}:</strong></font><font size="2" style="font-family:sans-serif;"> {{$cabpdf->rucclienterem}}  </font>	<bR>	 			
				
		

			<font size="2" style="font-family:sans-serif;"><strong>Destinatario:</strong></font><font size="2" style="font-family:sans-serif;"> {{$cabpdf->nomcliente}}</font> - <font size="2" style="font-family:sans-serif;"><strong>{{$cabpdf->tdides}}:</strong></font><font size="2" style="font-family:sans-serif;"> {{$cabpdf->ruccliente}}  </font>	<bR>	 			

		    
				
			</div>
			<div class="detalle-cliente">
							 	<table style="width:100%">
							 		<thead>
							 			<tr style="font-size:10;" >
							 				<th >Fecha Emisión</th>
							 				<th >Fecha Traslado</th>
							 				<th>Punto Partida</th>
							 				<th>Punto Llegada</th>
							 				
							 				<!--<th>Número Guía</th>-->
							 			</tr>
							 		</thead>

							 		<tbody>
							 			<tr style="text-align:center;">
							 		 <!--  <td></td>-->
							 		  <td valign="top" style="width:25%;"><font size="2" style="font-family:sans-serif;"> {{date('d-m-Y',strtotime($cabpdf->fechaemision))}}</font></td>
							 		   <td valign="top" style="width:25%;"><font size="2" style="font-family:sans-serif;"> {{date('d-m-Y',strtotime($cabpdf->fechatraslado))}}</font></td>
							 		   <td valign="top" style="width:25%;"><font size="2" style="font-family:sans-serif;"> {{$cabpdf->direccionpartida}} 
							 		   <td valign="top" style="width:75%;"><font size="2" style="font-family:sans-serif;"> {{$cabpdf->direccionllegada}} </font></td>
							 		 
							 		  <!-- <td></td>-->
							 		  
							 		</tr>
							 		</tbody>
							 		
							 	
							 		
							 	</table>
								
			
				</div>
				



				
				<div class="detalle">

						<table class="table-detalle" style="width:100%">

										<thead >
											<tr style="background:gray">
											<th style="border-color:black;width:10%;font-family:sans-serif;" colspan="3"><center><font size='1' color="white">DOCUMENTOS RELACIONADOS</font></center></th>
											
										</tr>
										<thead >
											<tr style="background:gray">
											<th style="border-color:black;width:10%;font-family:sans-serif;"><center><font size='1' color="white">RUC EMISOR</font></center></th>
											<th style="width:5%;font-size:8;font-family:sans-serif;"><center><font size='1' color="white">TIPO DOCUMENTO</font></center></th>	
											<th style="width:5%;font-size:8;font-family:sans-serif;"><center><font size='1' color="white">N° DOCUMENTO</font></center></th>	
											
										</tr>
						
										</thead>
										<tbody>
										
											@foreach($relacionados as $rel)
											
											<tr>
												<td style="border-color:black;width:10%;font-family:sans-serif;font-size:8;" ><font size='1'><center>{{$rel->ruc_emi_doc_rel}}</center></font></td>
													<td style="border-color:black;width:10%;font-family:sans-serif;font-size:8;" ><font size='1'><center>{{$rel->tdodes}}</center></font></td>
												<td style="border-color:black;width:10%;font-family:sans-serif;font-size:8;" ><font size='1'><center>{{$rel->ser_num_doc_rel}}</center></font></td>
											
											
											</tr>
											@endforeach
										
										</tbody>
									</table>
									<br>
							<table class="table-detalle" style="width:100%">
										<thead >
											<tr style="background:gray">
											<th style="border-color:black;width:10%;font-family:sans-serif;"><center><font size='1' color="white">N°</font></center></th>
											<th style="width:5%;font-size:8;font-family:sans-serif;"><center><font size='1' color="white">CODIGO</font></center></th>	
											<th style="width:43%;font-size:8;font-family:sans-serif;"><center><font size='1' color="white">DESCRIPCIÓN</font></center></th>
											<th style="width:8%;font-size:8;font-family:sans-serif;"><center><font size='1' color="white">CANTIDAD</font></center></th>
											<th style="width:8%;font-size:8;font-family:sans-serif;"><center><font size='1' color="white">UNIDAD DE<BR>DESPACHO</font></center></th>
										
										</tr>
						
										</thead>
										<tbody>
											<?php $i=0; ?>
											@foreach($detpdf as $det)
											<?php $i=$i+1; ?>
											<tr>
												<td style="border-color:black;width:10%;font-family:sans-serif;font-size:8;" ><font size='1'><center>{{$i}}</center></font></td>
												<td style="border-color:black;width:10%;font-family:sans-serif;font-size:8;" ><font size='1'><center>{{$det->procod}}</center></font></td>
												<td style="border-color:black;max-width:60%;font-family:sans-serif;font-size:8;">{{$det->pronom}}</td>
												<td style="border-color:black;width:10%;font-family:sans-serif;font-size:8;" ><font size='1'><center>{{$det->cantidad}}</center></font></td>
												<td style="border-color:black;width:10%;font-family:sans-serif;font-size:8;" ><font size='1'><center>{{$det->umenom}}</center></font></td>
											
											</tr>
											@endforeach
										
										</tbody>
									</table>
								

				</div>
				
			
		
			<!--<div class="montoletras">
		
								
			
			</div>-->

			<div class="totales">
				<div class="div-observacion" >			
						<div class="letras">
							<font style="font-size:9"><strong>Conductor:</strong> {{$cabpdf->nomconductor}} {{$cabpdf->apeconductor}}</font><BR>
							<font style="font-size:9"><strong>DNI del Conductor:</strong> {{$cabpdf->rucconductor}}</font><BR>
							<font style="font-size:9"><strong>Placa del Vehiculo:</strong> {{$cabpdf->placa}}</font><br>
							<font style="font-size:9"><strong>Licencia:</strong> {{$cabpdf->licencia}}</font><br></BR>
							<img src="{{$imgqr}}" width="100px" height="90px">
						</div>
					</div>
					<div class="div-aceptacion">
								
								
								<div class="letras">
									<font style="font-size:9"><STRONG>MTC:</STRONG> {{$cabpdf->mtc}}</font><br>
								    <font style="font-size:9"><strong>Peso Total Aprox. (KGM):</strong> {{$cabpdf->pesobruto}}</font><br>
									

								</div>
						
						
								<!--<br><br><br><br><br><br>
								<CENTER><FONT size="1">REPRESENTACIÓN IMPRESA DE LA {{$cabpdf->tdodes}}</FONT></CENTER>-->
						
					</div>
			
					<div class="div-totales">
							
							
								<div class="letras">
									<font style="font-size:9"><strong>Nombre:</strong></font><br>
									<font style="font-size:9"><strong>DNI:</strong></font><br><BR>

									<center><font style="font-size:9"><strong>CONFORMIDAD</strong><br>
									_______________________</font><br></center>
									
								</div>
								
						
								<!--<br><br><br><br><br><br>
								<CENTER><FONT size="1">REPRESENTACIÓN IMPRESA DE LA {{$cabpdf->tdodes}}</FONT></CENTER>-->
						
					
					</div>
				
				</div>

				<div class="hash">
					
						<p><strong><FONT style="font-size:9px;">"BIENES TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA."<BR>"SERVICIOS PRESTADOS EN LA AMAZONIA"</FONT></strong></p>
					
				</div>
			
	
		
	</body>
</html>