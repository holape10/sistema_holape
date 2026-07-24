<!doctype html>
<html lang="es">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<style>


			.page-break {
				page-break-after: always;
			}



		


		.cabecera {
			position:absolute;
			left: -20px;
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
			margin-left: -30px;
			margin-right:-30px;
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

		table, thead, tr, th {
		  border: 1px solid black;
		  border-collapse: collapse;
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



	<table style="width:100%;border:0px;" >
		<thead style="border:0px;">
			<tr style="border:0px;">
				<th colspan="6" style="text-align:left;width:85%;border:0px;">{{$empresa->NomEmpresa}}</th>
				
				<th colspan="1" style="text-align:right;border:0px;">Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr >
				<th colspan="6" style="text-align:left;width:85%;border:0px;">{{$data_sucursal->direccion}}</th>
				
				<th colspan="1" style="text-align:right;border:0px;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="6" style="text-align:left;width:85%;border:0px;">RUC: {{$empresa->IdEmpresa}}</th>
				<th colspan="1" style="text-align:right;border:0px;"></th>
				
			</tr>
			
		</thead>

	</table>
	
</div>

	
<br><br>
<div class="detalle" style="font-size:7pt;">
	
	<table style="width:100%;">
							
							<thead>
								<tr>
									<th colspan="10"><center><strong>REPORTE DE COMPRAS DESDE: {{$fecin}} HASTA {{$fecfin}}</strong></center>
									<th>TOTAL COMPRAS</th>
								</tr>
								<tr>
									<th colspan="10"></th>
									<th style="padding:5px 5px 5px 5px;">{{number_format($total,'2','.',',')}}</th>
									
								</tr>

								<tr>
							
									<th style="width:70px;">FECHA</th>
									<th style="width: 50px;">Tip. Doc</th>
									<th>SERIE</th>
									<th>N°</th>
									<th style="width:210px;">PROVEEDOR</th>
									<th>FECHA<br>DOC. REF</th>
									<th>TIPO<br>DOC. REF</th>
									<th>SERIE<br>DOC. REF</th>
									<th>NUMERO<br>DOC. REF</th>
									<th>MONEDA</th>
									<th>TOTAL</th>
												
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
								<tr>
						
								 	<td style="padding:5px 5px 5px 5px;">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->des_doc}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->com_doc_ser}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;">{{$comprobante->com_doc_num}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->prov_raz}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->fec_ref}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->tdocod_ref}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->serie_ref}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->num_ref}}</td>
								
									<td style="padding:5px 5px 5px 5px;text-align:center;">{{$comprobante->mon_id}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;">{{number_format($comprobante->total_com,'2','.',',')}}</td>
								
								</tr>
								@endforeach
							</tbody>
						</table><br>



					




	
</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>