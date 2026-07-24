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
				top: -35px;
				width:100%;
				margin-left:-15px;
				margin-right:-30px;
				
			}

			.logo{
				position:absolute;
				top: 0px;
				left:0px;
				width:25%;
				

			}



			.panel-logo{
				position:absolute;
				top:-10px;
				left:175px;
				width:45%;
				text-align:center;

			}

			.panel-numeracion{
				position:absolute;
				border: 1px 1px 1px 1px solid  black;
				padding: s10px;
				border-radius: 0px 0px 0px 0px;
				top:6px;
				right: 0px;
				width:30%;
				text-align:center;
			}

			.panel-cliente{
				position: relative;
				top:120px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 0px 0px 0px 0px;
				margin-left:-15px;
				margin-right:-30px;
			}

			.div-cuentas{

				position: relative;
				top:165px;
				border: 1px 1px 1px 1px solid  black;
				border-radius: 5px 5px 5px 5px;
				width:100%;
				padding: 10px;
				height:150px;


			
			}

				.observaciones-cuentas{
				position: absolute;
				bottom:160px;
				border: 1px 1px 1px 1px solid  black;
				border-radius: 5px 5px 5px 5px;
				width:100%;
				height:130px;
				margin-left:-15px;
				margin-right:-30px;
				margin-bottom:-30px;


				
			}

			.cuentas{
				position:absolute;
				margin-top:10px;
				margin-left:10px;
				width:50%;
			}

			.observaciones{

				position:absolute;
				margin-top:10px;
				margin-left:400px;
				width:50%;
			}



			.detalle-cliente{
				position: relative;
				top:130px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 10px 10px 10px 10px;
				margin-left:-15px;
				margin-right:-30px;
			}


			.detalle{
				position: relative;
				width:100%;
				top:130px;
				margin-left:-15px;
				margin-right:-30px;
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
				position: absolute;
				bottom:0px;
				border: 1px 1px 1px 1px solid  black;
				border-radius: 5px 5px 5px 5px;
				width:100%;
				height:150px;
				margin-left:-15px;
				margin-right:-30px;
				margin-bottom:-30px;
			}

			.hash{
				position: relative;
				top:150px;
				margin-left:-15px;
				margin-right:-30px;
			}


			.div-observacion{
				position:absolute;
				padding: 10px;
				top:-15px;
				left:0px;
				width:100%;
				height:140px;

			}



			.div-aceptacion{
				position:absolute;
				text-align:center;
				align-content: center;
				padding: 10px;
				top:0px;
				left:173px;
				width:45%;
				height:140px;
				z-index: 99999;

			}


		

			.div-totales{
				position:absolute;
				padding: 10px;
				top:0px;
				left: 485px;
				width:15%;
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
				/*background-color:gray;*/
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
				top:25px;
				left:5px;
				width:100%;
			}

			.tipo-comprobante{
				position:absolute;
			
				align-content: center;
				padding: 10px;
				top:120px;
				left:-5px;
				width:100%;
				height:20px;
			}


			.observacion{
				position:absolute;
				top:25px;
				left:120px;
				width:60%;
				text-align:justify;			}




	 	</style>
 	</head>
	<body style="width:100%;">

		<div class="cabecera">

		<div class="logo">
				<img src="{{$sucursal->logosuc}}"  style="padding-left:0px;" width="150px" height="150px"><br>
			</div>
		
			<div class="panel-logo">
					@if(!empty($sucursal->nombre_comercial))
				<br><font style="font-family:Helvética;font-size:15;"><strong>{{$sucursal->nombre_comercial}}</strong></font>
				<br><font style="font-family:Helvética;font-size:8;">DE: {{$empresa->NomEmpresa}}</font><br>
				@else
				 <br><font style="font-family:Helvética;font-size:10;"> {{$empresa->NomEmpresa}}</font><br>
				@endif
				
				@if(!empty($sucursal->descripcion1))
					<p style="font-family:Helvética;font-size:8;font-style: oblique;">{{$sucursal->descripcion1}}</p>
				@endif
				@if(!empty($sucursal->descripcion2))
					<p style="font-family:Helvética;font-size:8;font-style: oblique;">{{$sucursal->descripcion2}}</p>
				@endif

				
				<p style="font-family:Helvética;font-size:9;">
				{{$sucursal->direccion}}<BR> 
				<BR>
				@if(!empty($sucursal->telefono))
					{{$sucursal->telefono}}<br><BR>
				@endif
				@if(!empty($sucursal->celular))
					{{$sucursal->celular}}<br><BR>
				@endif
				@if(!empty($sucursal->correo))
					{{$sucursal->correo}}<br>
				@endif</p>
				
				
				<!--<font style="font-family:sans-serif;font-size:8">{{$sucursal->direccion}} </font><br>
				
				@if(!empty($sucursal->telefono))
					<font style="font-family:sans-serif;font-size:8">TEL&Eacute;FONO: {{$sucursal->telefono}}</font><br>
				@endif
			
				@if(!empty($sucursal->correo))
					<font style="font-family:sans-serif;font-size:8">E-MAIL: {{$sucursal->correo}}</font>
				@endif
			-->
							
			</div>
			<div class="panel-numeracion">
				<br><font style="font-family:sans-serif;" size='3'><strong>RUC {{$empresa->IdEmpresa}}</strong></font><br><br>
				<div class="comprobante">
					<font style="font-family:sans-serif;" size='2' ><strong>{{$cabpdf->tdodes}}</strong></font><br>
				</div>
				
				<font style="font-family:sans-serif;" size='3'><strong>{{$cabpdf->serdoc}}-{{str_pad($cabpdf->numdoc,8,"0",STR_PAD_LEFT)}}</strong></font><br><br>
			</div>
		</div>
			
			<div class="panel-cliente">
				<table style="width:100%;">
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:15%;">F. EMISION</td>
						<td style="width:70%;">: {{$cabpdf->ccafem}}</td>
						<td style="width:15%;">F. VENC.</td>
						<td style="width:20%;">: {{$cabpdf->ccafve}}</td>
						
					</tr>
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:15%;">CLIENTE</td>
						<td style="width:70%;">: {{$cabpdf->ccanom}}</td>
						<td style="width:15%;">MONEDA</td>
						<td style="width:20%;">: {{$cabpdf->monnom}}</td>
					</tr>
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:15%;">DIRECCION</td>
						<td style="width:70%;">: {{$cabpdf->direccion}}</td>
						<td style="width:15%;">CONDICION</td>
						<td  style="width:20%;"> @if(!empty($cabpdf->estadopago))
							: {{$cabpdf->estadopago}}
							@endif
						</td>
					</tr>
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:15%;">{{$cabpdf->tdides}}:</td>
						<td style="width:70%;">: {{$cabpdf->ccandi}}</td>
						<td style="width:15%;">CUOTAS</td>
						<td style="width:20%;">:@if($cant_cuotas>'0') {{$cant_cuotas}} @else - @endif </td>
					</tr>
						<tr style="font-family:Helvética;font-size:8;">
						<td style="width:15%;">O.C.</td>
						<td style="width:70%;">: {{$cabpdf->placa}}</td>
						<td style="width:15%;">MTO. A PAGAR</td>
						<td style="width:20%;">:@if($cabpdf->moncod=='USD') $ @else S/ @endif {{$cabpdf->ccaitv-$cabpdf->montodetraccion}}</td>
					</tr>
					</tr>
						<tr style="font-family:Helvética;font-size:8;">
				
						<td style="width:15%;">G. REMISION</td>
						<td style="width:20%;">: {{$cabpdf->guia_remision}}</td>
					</tr>
					@if($cabpdf->tdocod=='07' or $cabpdf->tdocod=='08')
				
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:15%;">TIPO N.C</td>
						<td style="width:70%;">: {{$cabpdf->ncdes}}</td>
						<td style="width:15%;">DOC. REF:</td>
						<td style="width:20%;">: {{$cabpdf->serie_ref}}-{{$cabpdf->num_ref}}</td>

					</tr>
					@endif
				</table>
			
				
			</div>
		

				<div class="detalle">

							<table class="table-detalle" style="width:100%">
										<thead >
											<tr style="background:gray">
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>CANT.</font></center></th>
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>UNIDAD </font></center></th>
												<th style="border-color:black;max-width:60%;"><center><font color="white" size='1'>DESCRIPCIÓN</font></center></th>
												
												<th style="border-color:black;width:10%" ><center><font color="white" size='1'>P.U</font></center></th>
												<th style="border-color:black;width:10%" ><center><font color="white" size='1'>P.VENTA</font></center></th>
											</tr>
						
										</thead>
										<tbody>
											<?php $i=0; ?>
											@foreach($detpdf as $det)
											<?php $i=$i+1; ?>
											     @if($det->cdevve > '0' || $det->cdevve > '0.00'){
											<tr>
												<td style="border-color:black;width:10%" ><center><font size='1'>{{$det->cdecan}}</font></center></td>
												<td style="border-color:black;width:10%" ><center><font size='1'>{{$det->umecod}}</font></center></td>
												<td style="border-color:black;max-width:60%;"><font size='1'> {{$det->cdedes}}</font></td>
												
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'>{{number_format($det->cdepuni,'2','.',',')}}</font></center></td>
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'>{{number_format($det->cdevve,'2','.',',')}}</font></center></td>
											</tr>
											@else

												<tr>
												<td style="border-color:black;width:10%" ><center><font size='1'></font></center></td>
												<td></td>
												
												<td style="border-color:black;max-width:60%;"><font size='1'> {{$det->cdedes}}</font></td>
												
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'></font></center></td>
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'></font></center></td>
											</tr>
											@endif
											@endforeach
										
										</tbody>
									</table>
								

				</div>
				
			
		
			<!--<div class="montoletras">
		
								
			
			</div>

			
			<div class="observaciones-cuentas">
				<div class="cuentas">
					<font style="font-size:9">BBVA CUENTA CORRIENTE S/. 0011-0187-0100043635<br>
					BBVA CCI S/. 011-187-000100043635-20<br><br>

					INTERBANK CUENTA CORRIENTE S/. 200-3002566255<br>
					INTERBANK CCI S/. 003-200-003002566255-39<br><br>

					INTERBANK CUENTA CORRIENTE US$ 200-3002566262<br>
					
				</font>
	
				</div>
				<div class="observaciones">

					<font style="font-size:9"><STRONG>CUENTA DETRACCION BN: 00-008-048304</STRONG><BR><br><strong>Observaciones:</strong><br>{{$cabpdf->ccaobs}}</font>
				</div>
			</div>-->
			
			<div class="totales">
				<div class="div-observacion">
								
								@if(!empty($totalletras))
								<div class="letras">
									<font style="font-family:Helvética;font-size:8pt;">SON: {{$totalletras}} {{$cabpdf->monnom}}</strong></font>
								</div>
								@endif
								
								<div class="qr">
									<br><img src="{{$imgqr}}" width="100px" height="90px">
								
							
								</div>
								
								<div class="tipo-comprobante">
									<FONT style="font-family:Helvética;font-size:8pt;font-style: oblique;">Representación Impresa de la {{ucwords(strtolower($cabpdf->tdodes))}} {{$cabpdf->serdoc}}-{{str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT)}}</FONT><br>
							
									<FONT style="font-family:Helvética;font-size:7pt;font-style: oblique;"><strong>@if($sucursal->serv_selv=='1')"BIENES Y/O SERVICIOS TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA" @endif</strong></FONT>
								</div>
								
								
									
								
						
					</div>
					<div class="div-aceptacion">
								
								@if(!empty($totalletras))
								<div class="letras">
									<center><font style="font-size:9"><strong>RECIBIDO CONFORME</strong></font></center><br>

									<center><font style="font-size:9">__ /__ /__ </font></center><br>
									<center><font style="font-size:9">________________________<br>FIRMA</font></center>

								</div>
								@endif
						
						
						
					</div>
							
								
			
					<div class="div-totales">
					
								<table  class="table-total" style="width:90%;">
									<tr>	
										<td class="width-table text-left" style="font-size:8"><strong>SUBTOTAL</strong></td>
										<td  style="font-size:8;text-align:right;width:10%;" >@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:8;text-align: right;width:70%;">{{number_format($cabpdf->ccatexo+$cabpdf->ccatvg+$cabpdf->ccatinaf,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td class="width-table text-left" style="font-size:8"><strong>OP. GRAVADA</strong></td>
										<td style="font-size:8;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table" style="font-size:8;text-align: right;">{{number_format($cabpdf->ccatvg,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td class="width-table text-left" style="font-size:8"><strong>OP. EXONERADA</strong></td>
										<td style="font-size:8;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:8;text-align: right;width:70%;">{{number_format($cabpdf->ccatexo,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td class="width-table text-left" style="font-size:8"><strong>OP. INAFECTA</strong></td>
										<td style="font-size:8;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:8;text-align: right;width:70%;">{{number_format($cabpdf->ccatinaf,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td class="width-table text-left" style="font-size:8"><strong>IGV</strong></td>
										<td style="font-size:8;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:8;text-align: right;width:70%;">{{number_format($cabpdf->ccaigv,'2','.',',')}}</td>
									</tr>
									<tr>
										<td class="width-table text-left" style="font-size:8"><strong>ICBPER</strong></td>
										<td style="font-size:8;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:8;text-align: right;width:70%;">{{number_format($cabpdf->icbper,'2','.',',')}}</td>
									</tr>
						
								
									<tr style="border-bottom:black 1px solid;" >
										<td class="width-table text-left" style="font-size:8"><strong>IMPORTE TOTAL</strong></td>
										<td style="font-size:8;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:8;text-align: right" ><strong>{{number_format($cabpdf->ccaitv,'2','.',',')}}</strong></td>
									</tr>
									
								</table>
					
					</div>
				
				</div>

		

				
		
	</body>
</html>