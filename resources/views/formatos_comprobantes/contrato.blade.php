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
				width:15%;
				
			}

			.panel-logo{
				position:absolute;
				top:0px;
				left:80px;
				width:55%;
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
				border: 0px 0px 0px 0px solid  black;
				
			}


			.detalle{
				position: relative;
				width:100%;
				top:120px;
			}

			.detalle-condicion-pago{
				position: relative;
				width:100%;
				top:110px;
			}

			.detalle-cuotas{
				position: relative;
				width:100%;
				top:100px;
			}


			.montoletras{
				position: absolute;
				top:730px;
				border: 1px 1px 1px 1px solid  black;
				padding: 10px;
				border-radius: 5px 5px 5px 5px;
				width: 100%;
			}

			.firmas{
				position: relative;
				top:100px;
				
				border-radius: 5px 5px 5px 5px;
				width:100%;
			}

			.clausulas{
				position: relative;
				top:20px;
				width:100%;
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
					<br><STRONG><font style="font-family:sans-serif;font-size:12;">{{$empresa->NomEmpresa}} </font> <br>
						@if(!empty($sucursal->nombre_comercial))<font style="font-family:sans-serif;font-size:12;">{{$sucursal->nombre_comercial}}</font></STRONG><br>@endif
			
				<font style="font-family:sans-serif;font-size:8">{{$sucursal->direccion}} </font><br>
				
				@if(!empty($sucursal->telefono))
					<font style="font-family:sans-serif;font-size:8">TEL&Eacute;FONO: {{$sucursal->telefono}}</font><br>
				@endif
			
				@if(!empty($sucursal->correo))
					<font style="font-family:sans-serif;font-size:8">E-MAIL: {{$sucursal->correo}}</font>
				@endif
		
							
			</div>
			<div class="panel-numeracion">
				<br><font style="font-family:sans-serif;" size='3'><strong>RUC {{$empresa->IdEmpresa}}</strong></font><br><br>
				<div class="comprobante">
					<font style="font-family:sans-serif;" size='2' color="white"><strong>{{$cabecera->tdodes}}</strong></font><br>
				</div><br>
				
				<font style="font-family:sans-serif;" size='3'><strong>{{$cabecera->serdoc}}-{{str_pad($cabecera->numdoc,8,"0",STR_PAD_LEFT)}}</strong></font><br>
			</div>
		</div>
			<br>
			<div class="detalle-cliente">
				<table  style="border-collapse: collapse;border: 1px solid black;width:100%;">
						<tr style="background:gray">
												<td style="border-color:black;padding-top:5px;padding-bottom: 10px;font-weight:bold;padding-left: 10px;" colspan="5"><font color="white" size='2'>1) Datos del Cliente</font></td>
											
									</tr>
					<tr  style="font-size:9pt;">
						<td colspan="2" style="width:50px;padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong>Garante</strong></font></td>
						<td  style="width:325px;padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;">: {{$cabecera->garante}}</font></td>

					

						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong>UGEL</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;">: {{$cabecera->ugel}}</font></td>
					</tr>

					<tr  style="font-size:9pt;">
						<td colspan="2" style="padding-left:10px;padding-top:5px;" ><font style="font-family:sans-serif;font-size:9pt;"><strong>Nombres y Apellidos</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;">: {{$cabecera->clinom}}</font></td>

					

						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong>Código Modular</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"></font>: {{$cabecera->cod_mod}}</td>
					</tr>

					<tr  style="font-size:9pt;">
						<td colspan="2" style="padding-left:10px;padding-top:5px;" ><font style="font-family:sans-serif;font-size:9pt;"><strong>Cargo</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;">: {{$cabecera->cargo}}</font></td>

					

						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong>Condición</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"></font>: {{$cabecera->condicion}}</td>
					</tr>

					<tr  style="font-size:9pt;">
						
						<td colspan="2" style="padding-left:10px;padding-top:5px;" ><font style="font-family:sans-serif;font-size:9pt;"><strong>Centro Trabajo</strong></font></td>
						<td><font style="font-family:sans-serif;font-size:9pt;padding-left:10px;padding-top:5px;">: {{$cabecera->clinom}}</font></td>

				

						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong>Lugar</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"> </font>: {{$cabecera->lugar}}</td>
					</tr>

					<tr  style="font-size:9pt;">
						
						<td style="padding-left:10px;padding-top:5px;" colspan="2" ><font style="font-family:sans-serif;font-size:9pt;"><strong>Domicilio:</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;">: {{$cabecera->clidir}}</font></td>

					
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong></strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"></font></td>
					</tr>

					<tr  style="font-size:9pt;">
				

						<td colspan="2" style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong>Ciudad</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;">: {{$cabecera->ciudad}}</font></td>

						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong></strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"></font></td>
					</tr>

						<tr  style="font-size:10;">
						
						<td colspan="2" style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong>Teléfono</strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;">: {{$cabecera->telefono}}</font></td>

						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"><strong></strong></font></td>
						<td style="padding-left:10px;padding-top:5px;"><font style="font-family:sans-serif;font-size:9pt;"></font></td>
					</tr>


				</table>
			
				
			
			</div>



		



				
				<div class="detalle">

							<table class="table-detalle" style="width:100%">
										<thead >
											<tr style="background:gray">
												<td style="border-color:black;padding-top:5px;padding-bottom: 10px;font-weight:bold;padding-left: 10px;" colspan="4"><font color="white" size='2'>2) Por lo siguiente: </font></td>
											
									</tr>
											<tr style="background:gray">
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>ITEM</font></center></th>
												<th style="border-color:black;width:10%"><center><font color="white" size='1'>CANT.</font></center></th>
												<th style="border-color:black;max-width:60%;"><center><font color="white" size='1'>DESCRIPCIÓN</font></center></th>
												<th style="border-color:black;width:10%" ><center><font color="white" size='1'>IMPORTE</font></center></th>
											</tr>
						
										</thead>
										<tbody>
											<?php $i=0; ?>
											@foreach($detalle as $det)
											<?php $i=$i+1; ?>
											<tr>
												<td style="border-color:black;width:10%" ><center><font size='1'><?php echo $i;?></font></center></td>
												<td style="border-color:black;"><center><font size='1'>{{$det->cdecan}}</font></center></td>
												<td style="border-color:black;max-width:60%;"><font size='1'> {{$det->cdedes}}</font></td>
												<td style="border-color:black;width:10%;text-align:right;"><font size='1'>{{number_format($det->cdevve,'2','.',',')}}</font></td>
											</tr>
											@endforeach
										
										</tbody>
									</table>

									
								<br>
							
							
								
								

				</div>
				

				
				<div class="detalle-condicion-pago">

						
							<table style="border-collapse: collapse;border: 1px solid black;width:100%;">
									<tr style="background:gray">
										<td style="border-color:black;padding-top:5px;padding-bottom: 10px;font-weight:bold;padding-left: 10px;" colspan="6"><font color="white" size='2'>3) Condiciones de Pago</font></td>
											
									</tr>
								@if($cabecera->mod_pag=='1')

									<tr>
										<td style="width:20px;padding-left:10px;padding-top:10px;"><font size='2'><strong>Planilla</strong></font></td>
										<td style="width:20px;"><font size='2'>: X</font></td>

										<td style="padding-top:10px;"><font size='2'><strong>Importe Total</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{$cabecera->imp_tot}}</font></td>

										<td style="padding-top:10px;"><font size='2'><strong>Venc. Primera Cuota</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{$cabecera->ven_prim_cuot}}</font></td>
									</tr>

									<tr>
										<td style="width:20px;padding-left:10px;padding-top:10px;"><font size='2'><strong>Pago Oficina</strong></font></td>
										<td style="width:20px;"><font size='2'>: </font></td>

										<td style="padding-top:10px;"><font size='2'><strong>Inicial</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{$cabecera->inicial}}</font></td>

										<td style="padding-top:10px;"><font size='2'></font></td>
										<td style="padding-top:10px;"><font size='2'></font></td>
									</tr>

							
									<tr>
										<td style="padding-left:10px;padding-top:10px;"><font size='2'></font></td>
										<td style="padding-top:10px;"><font size='2'></font></td>
										
										<td style="padding-top:10px;"><font size='2'><strong>Saldo</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{$cabecera->saldo}}</font></td>

										<td style="padding-top:10px;"><font size='2'></font></td>
										<td style="padding-top:10px;"><font size='2'></font></td>
									</tr>

								@elseif($cabecera->mod_pag=='2')
									<tr>
										<td style="width:20px;padding-left:10px;padding-top:10px;"><font size='2'><strong>Planilla</strong></font></td>
										<td style="width:20px;"><font size='2'>: </font></td>

										<td style="padding-top:10px;"><font size='2'><strong>Importe Total</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{$cabecera->imp_tot}}</font></td>

										<td style="padding-top:10px;"><font size='2'><strong>Venc. 1° Cuota</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{Carbon::parse($cabecera->ven_prim_cuot)->format('d-m-Y')}}</font></td>

									</tr>
									
									<tr>
										<td style="width:20px;padding-left:10px;padding-top:10px;"><font size='2'><strong>Pago Oficina</strong></font></td>
										<td style="width:20px;"><font size='2'>: X</font></td>


										<td style="padding-top:10px;"><font size='2'><strong>Inicial</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{$cabecera->inicial}}</font></td>

										<td style="padding-top:10px;"><font size='2'></font></td>
										<td style="padding-top:10px;"><font size='2'></font></td>

									</tr>

							

									<tr>
										<td style="padding-left:10px;"><font size='2'></font></td>
										<td style="padding-top:10px;"><font size='2'></font></td>
										
										<td style="padding-top:10px;"><font size='2'><strong>Saldo</strong></font></td>
										<td style="padding-top:10px;"><font size='2'>: {{$cabecera->saldo}}</font></td>

										<td style="padding-top:10px;"><font size='2'></font></td>
										<td style="padding-top:10px;"><font size='2'></font></td>
									</tr>

								@endif
						

							</table>
								



								<br>
							
							
								
								

				</div>


				<div class="detalle-cuotas">

							<table class="table-detalle" style="border-collapse: collapse;border: 1px solid black;width:100%;">
										<thead >
											<tr style="background:gray">
												<td style="border-color:black;padding-top:5px;padding-bottom: 10px;font-weight:bold;padding-left: 10px;" colspan="3"><CENTER><font color="white" size='2'>VENCIMIENTO DE CUOTAS</font></CENTER></td>
											
											</tr>
											<tr style="background:gray">
												<th style="border-color:black;"><center><font color="white" size='1'>FECHA <BR> VENCIMIENTO</font></center></th>
												<th style="border-color:black;"><center><font color="white" size='1'>MONTO DE LA <BR> CUOTA</font></center></th>
												<th style="border-color:black;"><center><font color="white" size='1'>ESTADO</font></center></th>
										
											</tr>
						
										</thead>
										<tbody>
											@foreach($pago as $pag)
															<tr>
														    	<td style="padding-top:8;padding-left:8px;padding-bottom:8px;font-size:9pt;" width='1300px'>{{Carbon::parse($pag->cont_fec_ven)->format('d-m-Y')}}</td>
														    	<td style="padding-top:8;padding-left:8px;padding-bottom:8px;font-size:9pt;" width='1300px'>{{$pag->cont_mont}}</td>
														    	<td style="padding-top:8;padding-left:8px;padding-bottom:8px;font-size:9pt;"  width='1300px'>{{$pag->cat_est_cont_des}} </td>
														    	
														    </tr>
													
											@endforeach
										
										</tbody>
									</table>

									
								<br>
							
							
								
								

				</div>
			
		
			<!--<div class="montoletras">
		
								
			
			</div>-->

			<div class="firmas">
			
					
								
									<table  style="border-collapse: collapse;width:100%;">
										<tr style="background:gray">
												<td style="border-color:black;padding-top:5px;padding-bottom: 10px;font-weight:bold;padding-left: 10px;"><font color="white" size='2'>4) AUTORIZACION DE DESCUENTO</font></td>
											
											</tr>
										<tr> 
											<td><font style="font-size:9">El presente contrato es en FIRME por lo tanto autorizo se me descuente de mis haberes deacuerdo a las condiciones del presente contrato,en los meses indicados,reprogramado los descuentos en caso de no ser descontados en los meses correspondientes, asi mismo autorizo los descuentos en los meses de Julio y diciembre.</font>
											</td>

										</tr>
									</table>
								

									<table  style="border-collapse: collapse;width:100%;">
										<tr>
											<td><center><font style="font-size:9"><br><br>________________________</font></center><BR>
												<center><font style="font-size:9">GARANTE</font><br><br>
												<font style="font-size:9">DNI N°:________________</font></center>
											</td>

											<td><center><font style="font-size:9"><br><br>_________________________</font></center><BR>
												<center><font style="font-size:9">CLIENTE</font><br><br>
												<font style="font-size:9">DNI N°:________________</font></center></td>

											<td><center><font style="font-size:9"><br><br>_________________________</font></center><BR>
												<center><font style="font-size:9">RECIBÍ CONFORME</font><br><br>
												<font style="font-size:9">DNI N°:________________</font></center></td>

											<td><center><font style="font-size:9"><br><br>_________________________</font></center><BR>
												<center><font style="font-size:9">VENDEDOR</font><br><br>
												<font style="font-size:9">DNI N°:________________</font></center></td>
										</tr>
									</table>

					</div>

					<div class="clausulas">

									<table  style="border-collapse: collapse;width:100%;">
										
										<tr> 
											<td style="border-color:black;padding-top:5px;padding-bottom: 10px;font-weight:bold;padding-left: 10px;"><font color="white" size='2'>5) CLAÚSULAS ESPECIALES</font></td>
										</tr>
										<tr>
											<td>
												<font style="font-size:9">

												<strong>a)</strong> El cliente para dar su conformidad o reparos a la mercadería tendrá un plazo de 24 horas apartir de la fecha de recibida la misma,vencido este periodo la Cooperativa dará por conforme y no se aceptará devolución o reclamos posteriores.<br>

												<strong>b)</strong> Este pedido queda entendido que es FIRME y por lo tanto en caso de rescisión del presente contrato, el cliente se compromete a pagar el 15% del valor total por concepto de gastos administrativos y perjuicios.<br>

												<strong>c)</strong> De producirse la devolución dé la mercadería quedará a favor de la EMPRESA, losimportes que haya pagado el cliente.<br>

												<strong>d)</strong> De no realizar el pago en oficina,cobranza personal o depósito a la cuenta corriente del Banco de la Nación, pasado os 30 días pasará automáticamente al descuento por PLANILLA.<br>

												<strong>e)</strong> El cliente se compromete a dar aviso inmediato por escrito  a la EMPRESA, cada vez que cambie el domicilio o centro de trabajo.<br>
												<strong>f)</strong> El cliente si tiene un garante, pasado los 30 días de no regularizar su pago del mes se le descontará automáticamente al GARANTE en el Sistema Único de Planillas.<br>

												<strong>g)</strong> En caso de cese de sus labores, en sus diversas variantes: despido, renuncia, muerte, suspensión, fenecimiento del convenio con la institución en la que labora, etc. elCOMPRADOR (o sus sucesoresquedará nobligados a pagar el monto convenido en las cuotas pactadas, así como en la frecuencia periodo de tiempo. De no realizarse el pago se procederá a reportar a la central de riesgo(INFOCORP)y posteriormente a demandar judicialmente al COMPRADOR,en mérito a este contrato y/o títulos valores que aceptan,incluidos los intereses,<br>

												<strong>h)</strong> De no realizarse el pago en ninguna de sus formas (descuento por planilla y pago anticipado), se generará intereses moratorios equivalentes al 15% mensual del total del monto señalado en la letra de cambio. Estos intereses computan con anticipación una vez transcurridos los ocho primeros días de cada mes.<br>


											</font>
											</td>

										</tr>
									</table>
								
								
				
								
			
				
				</div>

			
			
	
		
	</body>
</html>