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
				top: -40px;
				width:100%;
				
			}

			.logo{
				position:absolute;
				top:10px;
				left:-25px;
				width:75%;
				
			}

			.panel-logo{

				position:absolute;
				top:-5px;
				left:140px;
				width:45%;
				text-align:center;

			}

			.panel-numeracion{
				position:absolute;
				border: 2px 2px 2px 2px solid  black;
				padding: s10px;
				border-radius: 10px 10px 10px 10px;
				top:10px;
				right: 0px;
				width:30%;
				text-align:center;
			}

			.panel-cliente{
				position: relative;
				top:85px;
				border: 0px 0px 0px 0px solid  black;
				padding: 10px;
				border-radius: 0px 0px 0px 0px;
			}

			.detalle-cliente{
				position: relative;
				top:76px;
				border: 1px 1px 1px 1px solid  black;
				padding: 5px;
				border-radius: 10px 10px 10px 10px;
			}


			.detalle{
				position: relative;
				width:100%;
				top:81px;
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
				top:86px;
				border: 1px 1px 1px 1px solid  black;
				border-radius: 5px 5px 5px 5px;
				width:100%;
				height:105px;
			}

			.hash{
				position: relative;
				top:55px;
			}


			.div-observacion{
				position:absolute;
				padding: 10px;
				top:-5px;
				left:0px;
				width:30%;
				height:140px;

			}



			.div-aceptacion{
				position:absolute;
				text-align:center;
				align-content: center;
				padding: 10px;
				top:0px;
				left:153px;
				width:45%;
				height:140px;

			}

			.div-totales{
				position:absolute;
				padding: 10px;
				top:-5px;
				left: 465px;
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
				background-color:gray;
				width:100%;
				height:20px;

			}

			.comprobante{
				text-align:center;
				
				width:100%;
				height:20px;

			}


			.qr{
				position:absolute;
				top:15px;
				left:5px;
				width:50%;
			}


			.letras{
				position:absolute;
				top:10px;
				left:5px;
				width:100%;
			}

			.tipo-comprobante{
				position:absolute;
				text-align:center;
				align-content: center;
				padding: 10px;
				top:80px;
				left:90px;
				width:60%;
				height:20px;
			}


			.observacion{
				position:absolute;
				top:20px;
				left:120px;
				width:60%;
				text-align:justify;			}




	 	</style>
 	</head>
	<body style="font-family:sans-serif">

		<div class="cabecera">

			@if(!empty($sucursal->logosuc))
			<div class="logo">
				<center><img src="{{$sucursal->logosuc}}"  style="padding-left:0px;" width="450px" height="100px"></center><br>
			</div>
		@endif
			<div class="panel-logo">
			
			
							
			</div>
			<div class="panel-numeracion">
				<br><font style="font-family:sans-serif;" size='3'><strong>RUC {{$empresa->IdEmpresa}}</strong></font><br><br>
				<div class="comprobante">
					<font style="font-family:sans-serif;" size='2' color="white"><strong>{{$cabpdf->tdodes}}</strong></font><br>
				</div><br>
				<div class="numero">
				<font style="font-family:sans-serif;" size='3'><strong>{{$cabpdf->serdoc}}-{{str_pad($cabpdf->numdoc,8,"0",STR_PAD_LEFT)}}</strong></font><br>
				</div>
			</div>
		</div>
			
			<div class="panel-cliente">
				
			<font  style="font-size:9;font-family:sans-serif;"><strong>Señor(es):</strong></font>
			<font  style="font-size:9;font-family:sans-serif;"> {{$cabpdf->ccanom}}</font>@if(!empty($cabpdf->clicorcli))&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;	<font  style="font-size:9;font-family:sans-serif;"><strong>Correo:</strong></font><font  style="font-size:9;font-family:sans-serif;"> {{$cabpdf->clicorcli}}</font>@endif<bR>	 			
				
			<font  style="font-size:9;font-family:sans-serif;"><strong>{{$cabpdf->tdides}}:</strong></font><font  style="font-size:9;font-family:sans-serif;"> {{$cabpdf->ccandi}}  </font>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				
			<font  style="font-size:9;font-family:sans-serif;"><strong>Dirección:</strong></font><font  style="font-size:9;font-family:sans-serif;"> {{$cabpdf->direccion}}  </font>			
				
			</div>
			<div class="detalle-cliente">
							 	<table style="width:100%">
							 		<thead>
							 			<tr style="font-size:8pt;" >
							 				<th>Vendedor</th>
							 				<th>Fecha Emisión</th>
							 				<th>Fecha Vencimiento</th>
							 				<th>Condición Pago</th>
							 				<th>Número Guía</th>
							 			</tr>
							 		</thead>

							 		<tbody>
							 			<tr style="text-align:center;">
							 		    <td></td>
							 		   <td style="font-family:sans-serif;font-size:8pt;" valign="top" style="width:25%;"><font  style="font-family:sans-serif;"> {{date('d-m-Y',strtotime($cabpdf->ccafem))}}</font></td>
							 		   <td style="font-family:sans-serif;font-size:8pt;" valign="top" style="width:25%;"><font  style="font-family:sans-serif;"> 
							 		   	@if(!empty($cabpdf->ccafve)) {{date('d-m-Y',strtotime($cabpdf->ccafve))}} @endif</font></td>
							 		   @if(!empty($cabpdf->estadopago))
							 		   <td style="font-family:sans-serif;font-size:8pt;" valign="top" style="width:75%;"><font  style="font-family:sans-serif;"> {{$cabpdf->estadopago}}</font></td>
							 		   @endif
							 		   <td style="font-family:sans-serif;font-size:8pt;" valign="top" style="width:25%;"><font  style="font-family:sans-serif;"> </font></td>
							 		  
							 		</tr>
							 		</tbody>
							 		
							 	
							 		
							 	</table>
								
			
				</div>
				



				
				<div class="detalle">

							<table class="table-detalle" style="width:100%">
										<thead >
											<tr style="background:gray">
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>CANT.</font></center></th>
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>CODIGO.</font></center></th>
												<th style="border-color:black;max-width:60%;"><center><font color="white" size='1'>DESCRIPCIÓN</font></center></th>
												<th style="border-color:black;"><center><font color="white" size='1'>U.M</font></center></th>
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
												<td style="border-color:black;"><center><font size='1'>{{$det->procod}}</font></center></td>
												<td style="border-color:black;max-width:60%;"><font size='1'> {{$det->cdedes}}</font></td>
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'>{{$det->umecod}}</font></center></td>
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'>{{number_format($det->cdepuni,'2','.',',')}}</font></center></td>
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'>{{number_format($det->cdevve,'2','.',',')}}</font></center></td>
											</tr>
											@else

												<tr>
												<td style="border-color:black;width:10%" ><center><font size='1'></font></center></td>
												<td style="border-color:black;"><center><font size='1'></font></center></td>
												<td style="border-color:black;max-width:60%;"><font size='1'> {{$det->cdedes}}</font></td>
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'></font></center></td>
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
									<font style="font-size:7">SON: {{$totalletras}} {{$cabpdf->monnom}}</font>
								</div>
								@endif
								
								<div class="qr">
									<br><img src="{{$imgqr}}" width="100px" height="70px">	
								</div>
							
								
								
									
								
						
					</div>
					<div class="div-aceptacion">
								
								@if(!empty($totalletras))
								<div class="letras">
									<center><font style="font-size:7">CANCELADO</font></center><br>

									<center><font style="font-size:7">{{$sucursal->departamento}},____de_________________del 202___</font></center><br>
									<center><font style="font-size:7">________________________</font></center>

								</div>
								@endif
						
						
						
					</div>
							<div class="tipo-comprobante">
									<CENTER><FONT style="font-size:7;text-align:right;">REPRESENTACIÓN IMPRESA DE LA {{$cabpdf->tdodes}}</FONT></CENTER>
								</div>
								
			
					<div class="div-totales">
					
								<table  class="table-total" style="width:90%;">
									<tr>	
										<td class="width-table text-left" style="font-size:7"><strong>SUBTOTAL</strong></td>
										<td  style="font-size:7;text-align:right;width:10%;" >@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:7;text-align: right;width:70%;">{{number_format($cabpdf->ccatexo+$cabpdf->ccatvg,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td class="width-table text-left" style="font-size:7"><strong>OP. GRAVADA</strong></td>
										<td style="font-size:7;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table" style="font-size:7;text-align: right;">{{number_format($cabpdf->ccatvg,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td class="width-table text-left" style="font-size:7"><strong>IGV</strong></td>
										<td style="font-size:7;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:7;text-align: right;width:70%;">{{number_format($cabpdf->ccaigv,'2','.',',')}}</td>
									</tr>
								<!--	<tr>	
										<td class="width-table text-left" style="font-size:7"><strong>OP. INAFECTA</strong></td>
										<td style="font-size:7;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:7;text-align: right;width:70%;">{{number_format($cabpdf->ccatvi,'2','.',',')}}</td>
									</tr>-->
									<tr>
										<td class="width-table text-left" style="font-size:7"><strong>ICBPER</strong></td>
										<td style="font-size:7;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:7;text-align: right;width:70%;">{{number_format($cabpdf->icbper,'2','.',',')}}</td>
									</tr>
						
								
									<tr style="border-bottom:black 1px solid;" >
										<td class="width-table text-left" style="font-size:7"><strong>IMPORTE TOTAL</strong></td>
										<td style="font-size:7;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:7;text-align: right" ><strong>{{number_format($cabpdf->ccaitv,'2','.',',')}}</strong></td>
									</tr>
									
								</table>
					
					</div>
				
				</div>

			<!--	<div hidden="hidden" class="hash">
					
						<p><strong><FONT style="font-size:9px;">"BIENES TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA"</FONT></strong></p>
					
				</div>-->
			
	
		
	</body>
</html>