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
			width:100%;

		}

		.panel-logo{
			position:relative;
			top:0px;
			left:170px;
			width:100%;


		}

		
		.panel-cliente{
			position: relative;
			top:90px;
			border: 0px 0px 0px 0px solid  black;
			padding: 10px;
			border-radius: 0px 0px 0px 0px;
		}

		.detalle-cliente{
			position: relative;
			top:120px;
			border: 1px 1px 1px 1px solid  black;
			padding: 10px;
			border-radius: 10px 10px 10px 10px;
		}


		.detalle{
			position: relative;
			margin-left:-35px;
			margin-right:-20px;
			width:100%;
			top:20px;
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
			top:170px;
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
			position:relative;
			padding: 10px;
			top:190px;
			left: 465px;
			width:15%;
			height:140px;

		}


		.table-detalle{
			position: relative;
			border:0px 0px 0px 0px solid  black;
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
			text-align:justify;			
		}




	</style>
</head>

<body  style="font-family:Helvética,sans-serif;font-size:8pt;">

	

	<div class="cabecera">
		<!--<script type="text/php">
			if ( isset($pdf) ) {
			$pdf->page_script('
			$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
			$pdf->text(270, 10, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 10);
			$pdf->text(480, 10, "{{now()->format('d/m/Y H:i:s')}}", $font, 10);
			');
		}
	</script>-->



	<table style="width:100%;">
		<thead>
			<tr>
				<th colspan="3" style="text-align:left;width:85%">{{$empresa->NomEmpresa}}</th>
				
				<th colspan="3" style="text-align:left;">Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr >
				<th colspan="3" style="text-align:left;width:85%">{{$data_sucursal->direccion}}</th>
				
				<th colspan="3" style="text-align:left;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="3" style="text-align:left;width:85%">RUC: {{$empresa->IdEmpresa}}</th>
				<th colspan="3"></th>
				
			</tr>
			
		</thead>

	</table>
	
</div>

	

<div class="detalle">
	<br>
<table style="width:100%;">
							<thead>
								<tr>
									<th colspan="6"><center><strong>RESUMEN DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}}  @endif</strong></center></th>
									
								</tr>
							
								<tr>
									<td colspan='6'><hr></td>
								</tr>
								<tr>
									<th>CODIGO</th>
									<th width="70%">PRODUCTO</th>
									<th>UNIDAD</th>
									<th>CANTIDAD</th>
									<th>PRECIO</th>
									<th>TOTAL</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan='6'><hr></td>
								</tr>
								@foreach($cabecera as $cab)
									 
									 	<tr>
											<td><strong>{{$cab->ccafem}}</strong></td>
											<td colspan="4"><strong>{{$cab->tdocod}} {{$cab->serdoc}} {{$cab->numdoc}} {{$cab->ccanom}} </strong></td>
										
											@if($cab->tdocod =='07')
												<td  style="text-align:right;"><strong>{{number_format((-1)*$cab->ccaitv,'2','.',',')}}</strong></td>
											@else
												<td style="text-align:right;"><strong>{{number_format($cab->ccaitv,'2','.',',')}}</strong></td>
											@endif
											

										</tr>
									 	<tr>
									<td colspan='6'><hr></td>
								</tr>
									@foreach($comprobantes as $comprobante)
										@if($comprobante->IdCpe_cabecera == $cab->IdCpe_cabecera)
										<tr>
											<td>{{$comprobante->procod}}</td>
											<td style="width:70%">{{$comprobante->cdedes}}</td>
											<td>{{$comprobante->umecod}}</td>
											<td  style="text-align:right;">{{number_format($comprobante->cdecan,'2','.',',')}}</td>
											<td  style="text-align:right;">{{number_format($comprobante->cdepuni,'2','.',',')}}</td>
											@if($comprobante->tdocod =='07')
												<td  style="text-align:right;">{{number_format((-1)*$comprobante->cdevve,'2','.',',')}}</td>
											@else
												<td  style="text-align:right;">{{number_format($comprobante->cdevve,'2','.',',')}}</td>
											@endif
											
										

										</tr>
										@endif
									@endforeach
									<tr>
									<td colspan='6'><hr></td>
								</tr>
								@endforeach
								
								<tr>
									<td colspan='5' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table><br>
						


						<table style="width:60%;">
							<thead>
								<tr>
							
									<th colspan="2" style="width:35%;text-align:left;">RESUMEN VENTAS</th>
									<th style="width:5%">CANTIDAD</th>
									<th style="width:5%">TOTAL</th>
								</tr>
								<tr>
									<td colspan='4'><hr></td>
								</tr>
							</thead>
							<tbody>
								
								@foreach($productos as $producto)
								<tr>
								
									<td colspan="2" style="width:35%">{{$producto->procod}} {{$producto->cdedes}}</td>
									<td  style="text-align:right;width:10%">{{number_format($producto->cantidad,'2','.',',')}}</td>
									<td  style="text-align:right;width:10%">{{number_format($producto->precio,'2','.',',')}}</td>
								</tr>
								@endforeach
								<tr>
									<td colspan='2' style="text-align:left;">TOTAL NOTAS DE CRÉDITO</td>
										<td  style="text-align:right;width:10%">{{number_format((-1)*$productosnotas,'2','.',',')}}</td>
									<td  style="text-align:right;width:10%;">{{number_format((-1)*$totalnotas,'2','.',',')}}</td>
								</tr>
								<tr>
									<td colspan='4'><hr></td>
								</tr>
								<tr>
									<td colspan='2' style="text-align:left;"><strong>TOTAL VENTAS</strong></td>
									<th style="width:10%;"></th>
									<td  style="text-align:right;width:10%;"><strong>{{number_format($totalmontoproductos,'2','.',',')}}</strong></td>
								</tr>
								
								<tr>
									<td colspan='4'><hr></td>
								</tr>
								
							</tbody>
						</table><br>




	
</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>