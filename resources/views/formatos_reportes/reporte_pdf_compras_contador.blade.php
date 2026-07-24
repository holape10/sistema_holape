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
			margin-right:30px;
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
				<th colspan="19" style="text-align:left;width:85%;border:0px;">{{$empresa->NomEmpresa}}</th>
				
				<th colspan="3" style="text-align:right;border:0px;">Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr >
				<th colspan="19" style="text-align:left;width:85%;border:0px;">{{$data_sucursal->direccion}}</th>
				
				<th colspan="3" style="text-align:right;border:0px;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="19" style="text-align:left;width:85%;border:0px;">RUC: {{$empresa->IdEmpresa}}</th>
				<th colspan="3" style="text-align:right;border:0px;"></th>
				
			</tr>
			
			<tr>
				<th  colspan="21" style="text-align:center;border:0px;">
					<font><center>REPORTE DE COMPRAS DESDE {{$fecin}} hasta {{$fecfin}}</center></font>
				</th>
			</tr>
		</thead>

	</table>
	
</div>

	
<br><br>
<div class="detalle" style="font-size:7pt;">
	
	<table style="width:85%;">
							
							<thead>
							
								<tr>
									<th rowspan="2" style="vertical-align:middle;">N° CORREL. DEL REG. O COD. UNICO DE LA OPER.</th>
									<th rowspan="2" style="vertical-align:middle;">FECHA DE EMIS. DEL COMP. DE PGO. O DOC</th>
									<th rowspan="2" style="vertical-align:middle;">FECHA DE VENCIM. Y/O PAGO</th>
									<th colspan="3" style="vertical-align:middle;"></th>
									<th colspan="3" style="vertical-align:middle;">INFORMACION DEL CLIENTE</th>
									<th rowspan="2" style="vertical-align:middle;">VALOR FACTURADO DE LA EXPORTAC.</th>
									<th rowspan="2" style="vertical-align:middle;">BASE IMP. DE LA OPERAC. GRAVADA</th>
									<th colspan="2" style="vertical-align:middle;">IMPORTE TOTAL DE OPER. EXONERADA O INAFECTA</th>
									<th rowspan="2" style="vertical-align:middle;">IGV Y/O I.P.M</th>
									<th rowspan="2" style="vertical-align:middle;">ICBPER</th>
									<th rowspan="2" style="vertical-align:middle;">IMPORTE TOTAL DEL COMP. DE PAGO</th>
									<th rowspan="2" style="vertical-align:middle;">TIPO DE CAMB.</th>
									<th colspan="4" style="vertical-align:middle;">REF. DE COMP. DE PAGO O DOC. ORIGINAL QUE SE MODIFICA.</th>
								</tr>
					
							
								<tr>
									
									<th style="vertical-align:middle;">TIPO (10)</th>
									<th style="vertical-align:middle;">SERIE</th>
									<th style="vertical-align:middle;">N°</th>
									<th style="vertical-align:middle;">TIPO TABLA (2)</th>
									<th style="vertical-align:middle;">NUMERO</th>
									<th style="vertical-align:middle;">DENOMINACION O RAZON SOCIAL</th>
									<th style="vertical-align:middle;">EXONERADA</th>
									<th style="vertical-align:middle;">INAFECTA</th>
									<th style="vertical-align:middle;">FECHA</th>
									<th style="vertical-align:middle;">TIPO TABLA (10)</th>
									<th style="vertical-align:middle;">SERIE</th>
									<th style="vertical-align:middle;">N° DEL COMP. O DOC.</th>
									
								</tr>
							</thead>
							
							<tbody>
									
									@foreach($comprobantes as $comprobante)
								<tr>
								
								 	<td>{{$comprobante->cod_mov}}</td>
								 	<td style="width:90px;text-align:center;">{{Carbon::parse($comprobante->ccafem)->format('d-m-Y')}}</td>
									<td style="width:90px;text-align:center;">{{Carbon::parse($comprobante->fechaven)->format('d-m-Y')}}</td>
									<td>{{$comprobante->tdocod}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<td>{{$comprobante->tdicod}}</td>
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
								 	<td></td>
									<td></td>
									<td>{{$comprobante->ccatexo}}</td>
									<td>{{$comprobante->ccatvi}}</td>
									<td>{{$comprobante->ccaigv}}</td>
									<td>{{$comprobante->icbper}}</td>
									<td>{{$comprobante->total_com}}</td>
									<td>{{$comprobante->tipcambio}}</td>
									<td>{{$comprobante->fecha_ref}}</td>
									<td>{{$comprobante->tdocod_ref}}</td>
									<td>{{$comprobante->serie_ref}}</td>
									<td>{{$comprobante->num_ref}}</td>
									
								</tr>
								@endforeach
							</tbody>>
						</table><br>



					




	
</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>