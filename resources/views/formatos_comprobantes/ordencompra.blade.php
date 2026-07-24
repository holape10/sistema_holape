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
				top: -30px;
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
				padding: s10px;
				border-radius: 10px 10px 10px 10px;
				top:0px;
				right: 0px;
				width:30%;
				text-align:center;
			}

			.panel-cliente{
				position: relative;
				top:110px;
				border: 0px 0px 0px 0px solid  black;
				padding: 10px;
				border-radius: 0px 0px 0px 0px;
			}

			.detalle-cliente{
				position: relative;
				top:110px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 10px 10px 10px 10px;
			}


			.detalle{
				position: relative;
				width:100%;
				top:120px;
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
				top:130px;
				border: 1px 1px 1px 1px solid  black;
				border-radius: 5px 5px 5px 5px;
				width:100%;
				height:150px;
			}

			.hash{
				position: relative;
				top:120px;
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
				text-align:center;
				align-content: center;
				padding: 10px;
				top:10px;
				left:163px;
				width:45%;
				height:140px;

			}

			.div-totales{
				position:absolute;
				padding: 10px;
				top:0px;
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

			.tipo-comprobante{
				position:absolute;
				text-align:center;
				align-content: center;
				padding: 10px;
				top:120px;
				left:140px;
				width:60%;
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
	<body style="font-family:sans-serif">

		<div class="cabecera">

			<div class="logo">
				<img src="{{$sucursal->logosuc}}"  style="padding-left:0px;" width="150px" height="150px"><br>
			</div>
		
			<div class="panel-logo">
					<br><font style="font-family:sans-serif;font-size:16;"><STRONG>{{$empresa->NomEmpresa}}</STRONG></font><br>
				<p style="font-family:sans-serif;font-size:6;font-style: oblique;">PRINCIPAL:CALLE LAS AZUCENAS N° 203- SAN JUAN BAUTISTA</p>
				<p style="font-family:sans-serif;font-size:6;font-style: oblique;">SUCURSAL: CALLE AMAZONAS MZA C LOTE 32 - PUNCHANA </p>

				<p style="font-family:sans-serif;font-size:6;font-style: oblique;">Telefono: 985715940 / Persona de Contacto: Almendra Melita Sanchez Cabrera</p>
				<p style="font-family:sans-serif;font-size:6;font-style: oblique;">Correo: logisticaclinicaemanuel@gmail.com</p>

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
					<font style="font-family:sans-serif;" size='2' color="white"><strong>{{$cabpdf->tdodes}}</strong></font><br>
				</div><br>
				
				<font style="font-family:sans-serif;" size='3'><strong>{{$cabpdf->com_doc_ser}}-{{str_pad($cabpdf->com_doc_num,8,"0",STR_PAD_LEFT)}}</strong></font><br>
			</div>
		</div>
			
			<div class="panel-cliente">
				
			<font size="2" style="font-family:sans-serif;"><strong>Señor(es):</strong></font><font size="2" style="font-family:sans-serif;"> {{$cabpdf->prov_raz}}</font><bR>	 			
				
			<font size="2" style="font-family:sans-serif;"><strong>RUC:</strong></font><font size="2" style="font-family:sans-serif;"> {{$cabpdf->prov_ruc}}  </font><br>
				
			<font size="2" style="font-family:sans-serif;"><strong>Dirección:</strong></font><font size="2" style="font-family:sans-serif;"> {{$cabpdf->prov_dir}}  </font>			
				
			</div>
			<div class="detalle-cliente">
							 	<table style="width:100%">
							 		<thead>
							 			<tr style="font-size:10;" >
							 				<th >Nro Interno</th>
							 				<th>Fecha Emisión</th>
							 				<th>Fecha Vencimiento</th>
							 				<th>Condición Pago</th>
							 				<th>Número Guía</th>
							 			</tr>
							 		</thead>

							 		<tbody>
							 			<tr style="text-align:center;">
							 		   <td></td>
							 		   <td valign="top" style="width:25%;"><font size="2" style="font-family:sans-serif;"> {{date('d-m-Y',strtotime($cabpdf->com_fec))}}</font></td>
							 		   <td valign="top" style="width:25%;"><font size="2" style="font-family:sans-serif;"></font></td>
							 		  
							 		   <td valign="top" style="width:75%;"></td>
							 		  
							 		   <td></td>
							 		  
							 		</tr>
							 		</tbody>
							 		
							 	
							 		
							 	</table>
								
			
				</div>
				



				
				<div class="detalle">

							<table class="table-detalle" style="width:100%">
										<thead >
											<tr style="background:gray">
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>ITEM</font></center></th>
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>CANT.</font></center></th>
												<th style="border-color:black;max-width:60%;"><center><font color="white" size='1'>DESCRIPCIÓN</font></center></th>
												<th style="border-color:black;"><center><font color="white" size='1'>P.U</font></center></th>
												
												<th style="border-color:black;width:10%" ><center><font color="white" size='1'>P.VENTA</font></center></th>
											</tr>
						
										</thead>
										<tbody>
											<?php $i=0; ?>
											@foreach($detpdf as $det)
											<?php $i=$i+1; ?>
											<tr>
												<td style="border-color:black;width:10%" ><center><font size='1'><?php echo $i;?></font></center></td>
												<td style="border-color:black;"><center><font size='1'>{{$det->cantidad}}</font></center></td>
												<td style="border-color:black;max-width:60%;"><font size='1'> {{$det->pronom}}</font></td>
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'>{{number_format($det->pre_uni,'2','.',',')}}</font></center></td>
												
												<td style="border-color:black;width:10%" class=" text-right "><center><font size='1'>{{number_format($det->total,'2','.',',')}}</font></center></td>
											</tr>
											@endforeach
										
										</tbody>
									</table>
								

				</div>
				
			
		
			<!--<div class="montoletras">
		
								
			
			</div>-->

			<div class="totales">
				<div class="div-observacion">
								
							
								
						
					</div>
					<div class="div-aceptacion">
								
								
								<div class="letras">
									<center><font style="font-size:9"><strong>AUTORIZADO<BR>LOGISTICA</BR></strong></strong></font></center><br><br>

									<center><font style="font-size:9">________________________<BR>Lic. Almendra Sanchez Cabrera
</font></center>

								</div>
							
						
						
						
					</div>
							
								
			
					<div class="div-totales">
					
								<table  class="table-total" style="width:90%;">
								
								
								
						
								
									<tr style="border-bottom:black 1px solid;" >
										<td class="width-table text-left" style="font-size:8"><strong>TOTAL</strong></td>
										<td style="font-size:8;text-align:right;width:10%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif</td>
										<td class="width-table text-right" style="font-size:8;text-align: right" ><strong>{{number_format($cabpdf->total_com,'2','.',',')}}</strong></td>
									</tr>
									
								</table>
					
					</div>
				
				</div>

				<div class="hash">
					
					
				</div>
			
	
		
	</body>
</html>