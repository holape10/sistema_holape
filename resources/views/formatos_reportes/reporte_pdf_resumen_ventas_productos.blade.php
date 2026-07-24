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
			margin-left:-25px;
			margin-right:-15px;
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

<body  style="font-family:Helvética,sans-serif;font-size:7pt;">

	

	<div class="cabecera">
		<script type="text/php">
			if ( isset($pdf) ) {
			$pdf->page_script('
			$font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
			$pdf->text(270, 10, "Pagina $PAGE_NUM de $PAGE_COUNT", $font, 10);
			$pdf->text(480, 10, "{{now()->format('d/m/Y H:i:s')}}", $font, 10);
			');
		}
	</script>



	<table style="width:100%;">
		<thead>
			<tr>
				<th colspan="3" style="text-align:left;">{{$empresa->NomEmpresa}}</th>
				<th colspan="3" style="text-align:right;">Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr >
				<th colspan="3" style="text-align:left;">{{$data_sucursal->direccion}}</th>
				<th colspan="3" style="text-align:right;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="3" style="text-align:left;">{{$empresa->IdEmpresa}}</th>
				<th></th>
			</tr>
			
		</thead>

	</table>
	
</div>

	

<div class="detalle">
	
<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;" >
							<thead>
								<tr>
									<th colspan="6"><center><strong>RESUMEN DE VENTAS POR PRODUCTO DESDE {{$fecin}} HASTA {{$fecfin}} </strong></center></th>
									
								</tr>
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								
								<tr>
									<th style="width:30px;">CODIGO</th>
									<th style="width:190px;">DESCRIPCION</th>
									<th style="width:30px;">UM</th>
									<th style="width:30px;">CANTIDAD</th>
									<th style="width:30px;">PRECIO</th>
									<th style="width:30px;">TOTAL</th>
								</tr>
							</thead>
							<tbody>
								@foreach($productos as $producto)
								<tr>
									<td style="width:30px;">{{$producto->procod}}</td>
									<td style="width:190px;">{{$producto->cdedes}}</td>
									<td  style="width:30px;">{{$producto->umecod}}</td>
									<td  style="text-align:right;width:30px;">{{number_format($producto->cantidad,'2','.',',')}}</td>
									<td  style="text-align:right;width:30px;">{{number_format($producto->precio/$producto->cantidad,'2','.',',')}}</td>
									<td  style="text-align:right;width:30px;">{{number_format($producto->precio,'2','.',',')}}</td>

								</tr>
								@endforeach
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								
								<tr>
									<td colspan='5' style="text-align:right;width:85px;"><strong>TOTAL</strong></td>
									<td  style="text-align:right;width:30px;"><strong>{{number_format($total,'2','.',',')}}</strong></td>
								</tr>
							</tbody>
						</table><br>
				


	
</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>