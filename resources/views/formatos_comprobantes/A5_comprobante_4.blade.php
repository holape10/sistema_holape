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
				top: 10px;
				left:50px;
				width:70%;
				
			}

			.panel-logo{
				position:absolute;
				top:10px;
				left:25px;
				width:70%;
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
				top:135px;
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


			.detalle-cliente{
				position: relative;
				top:140px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 10px 10px 10px 10px;
				margin-left:-15px;
				margin-right:-30px;
			}


			.detalle{
				position: relative;
				width:100%;
				top:140px;
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
				height:200px;
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
				top:0px;
				left:0px;
				width:100%;
				height:140px;

			}



			.div-aceptacion{
				position:absolute;
				text-align:center;
				align-content: center;
				padding: 10px;
				top:33px;
				left:173px;
				width:45%;
				height:140px;
				z-index: 99999;

			}

			.div-totales{
				position:absolute;
				padding: 10px;
				top:25px;
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
				top:15px;
				left:5px;
				width:50%;
			}


			.letras{
				position:absolute;
				top:5px;
				left:5px;
				width:100%;
			}

			.tipo-comprobante{
				position:absolute;
			
				align-content: center;
				padding: 10px;
				top:145px;
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
				<img src="{{$sucursal->logosuc}}"  style="padding-left:0px;" width="400px" height="80px"><br>
			</div>
		
			<div class="panel-logo">
				<br><font style="font-family:Helvética;font-size:15;"><strong>{{$empresa->NomEmpresa}}</strong></font>
				@if(!empty($sucursal->nombre_comercial))
				<br><font style="font-family:Helvética;font-size:15;"><strong>{{$sucursal->nombre_comercial}}</strong></font>@endif
			
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
					{{$sucursal->telefono}}<br>
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
						<td style="width:10%;">FECHA</td>
						<td style="width:60%;">: {{$cabpdf->ccafem}}</td>
						<td style="width:10%;">MONEDA</td>
						<td style="width:20%;">: {{$cabpdf->monnom}}</td>
					</tr>
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:10%;">CLIENTE</td>
						<td style="width:60%;">: {{$cabpdf->ccanom}}</td>
						<td style="width:10%;"></td>
						<td style="width:20%;"></td>
					</tr>
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:10%;">DIRECCION</td>
						<td style="width:60%;">: {{$cabpdf->direccion}}</td>
						<td style="width:10%;">CONDICION</td>
						<td  style="width:20%;"> @if(!empty($cabpdf->estadopago))
							: {{$cabpdf->estadopago}}
							@endif
						</td>
					</tr>
					<tr style="font-family:Helvética;font-size:8;">
						<td style="width:10%;">{{$cabpdf->tdides}}:</td>
						<td style="width:60%;">: {{$cabpdf->ccandi}}</td>
						<td style="width:10%;">VENDEDOR</td>
						<td style="width:20%;">: </td>
					</tr>
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
		
								
			
			</div>-->

			<div class="totales">
				<div class="div-observacion">
								
								@if(!empty($totalletras))
								<div class="letras">
									<font style="font-family:Helvética;font-size:8pt;">SON: {{$totalletras}} {{$cabpdf->monnom}}</font><br>
								</div>
								@endif
								
								<div class="qr">
									<br><img src="{{asset($imgqr)}}" width="120px" height="120px">	
								</div>
								
								<div class="tipo-comprobante">
									<FONT style="font-family:Helvética;font-size:8pt;font-style: oblique;">Representación Impresa de la {{ucwords(strtolower($cabpdf->tdodes))}} {{$cabpdf->serdoc}}-{{str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT)}}</FONT><br>
							
									<FONT style="font-family:Helvética;font-size:7pt;font-style: oblique;"><strong></strong></FONT>
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
										<td class="width-table text-right" style="font-size:8;text-align: right;width:70%;">{{number_format($cabpdf->ccatexo+$cabpdf->ccatvg,'2','.',',')}}</td>
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