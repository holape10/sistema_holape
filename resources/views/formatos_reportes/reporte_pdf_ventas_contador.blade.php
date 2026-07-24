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
					<font><center>REPORTE DE VENTAS DESDE {{$fecin}} hasta {{$fecfin}}</center></font>
				</th>
			</tr>
		</thead>

	</table>
	
</div>

	
<br><br>
<div class="detalle" style="font-size:7pt;">
	
	<table style="width:90%;">
							
							<thead>

								<tr >
									<th rowspan="2" style="vertical-align:middle;width:55px;">N° CORREL. DEL REG. O COD. UNICO DE LA OPER.</th>
									<th rowspan="2" style="vertical-align:middle;width:55px;">FECHA DE EMIS. DEL COMP. DE PGO. O DOC</th>
									<th rowspan="2" style="vertical-align:middle;width:55px;">FECHA DE VENCIM.<br>Y/O PAGO</th>
									<th colspan="3" style="vertical-align:middle;width:250px;"></th>
									<th colspan="3" style="vertical-align:middle;width:150px;">INFORMACION <br>DEL CLIENTE</th>
									<th rowspan="2" style="vertical-align:middle;width:60px;">VALOR <br> FACTURADO <br>DE LA <br>EXPORTAC.</th>
									<th rowspan="2" style="vertical-align:middle;width:80px;">BASE IMP.<br> DE LA OPERAC.<br> GRAVADA</th>
									<th colspan="2" style="vertical-align:middle;width:50px;">IMPORTE TOTAL <br>DE OPER. EXONERADA<br> O INAFECTA</th>
									<th rowspan="2" style="vertical-align:middle;width:50px;">IGV Y/O I.P.M</th>
									<th rowspan="2" style="vertical-align:middle;width:50px;">ICBPER</th>
									<th rowspan="2" style="vertical-align:middle;width:80px;">IMPORTE TOTAL DEL COMP. DE PAGO</th>
									<th rowspan="2" style="vertical-align:middle;width:5px;">TIPO DE CAMB.</th>
									<th colspan="4" style="vertical-align:middle;width:150px;">REF. DE COMP. DE PAGO O DOC. ORIGINAL QUE SE MODIFICA.</th>
								</tr>
					
							
								<tr>
									
									<th style="vertical-align:middle;width:15px;">TIPO (10)</th>
									<th style="vertical-align:middle;width:15px;">SERIE</th>
									<th style="vertical-align:middle;width:15px;">N°</th>
									<th style="vertical-align:middle;width:10px;">TIPO TABLA (2)</th>
									<th style="vertical-align:middle;width:10px;">NUMERO</th>
									<th style="vertical-align:middle;width:50px;">DENOMINACION O RAZON SOCIAL</th>
									<th style="vertical-align:middle;">EXONERADA</th>
									<th style="vertical-align:middle;">INAFECTA</th>
									<th style="vertical-align:middle;">FECHA</th>
									<th style="vertical-align:middle;">TIPO TABLA (10)</th>
									<th style="vertical-align:middle;">SERIE</th>
									<th style="vertical-align:middle;">N° DEL COMP. O DOC.</th>
									
								</tr>
							</thead>
							
							<tbody>
									
										
								@if(count($facturas)>0)
									@foreach($facturas as $factura)
								<tr>
								
								 	<td>{{$factura->cod_mov}}</td>
								 	<td>{{Carbon::parse($factura->ccafem)->format('d-m-Y')}}</td>
									<td>{{Carbon::parse($factura->fechaven)->format('d-m-Y')}}</td>
									<td>{{$factura->tdocod}}</td>
									<td>{{$factura->serie}}</td>
									<td>{{$factura->numero}}</td>
									<td>{{$factura->tdicod}}</td>
									<td>{{$factura->numerodocumento}}</td>
									<td>{{$factura->cliente}}</td>
								 	<td></td>
									<td></td>
									<td style="text-align:right;">{{$factura->ccatexo}}</td>
									<td style="text-align:right;">{{$factura->ccatvi}}</td>
									<td style="text-align:right;">{{$factura->ccaigv}}</td>
									<td style="text-align:right;">{{$factura->icbper}}</td>
									<td style="text-align:right;">{{$factura->ccaitv}}</td>
									<td style="text-align:right;">{{$factura->tipcambio}}</td>
									<td>{{$factura->ccafem_ref}}</td>
									<td>{{$factura->tdocod_ref}}</td>
									<td>{{$factura->serie_ref}}</td>
									<td>{{$factura->num_ref}}</td>
									
								</tr>
								@endforeach
								<tr>
									<td colspan="11" style="font-weight:bold;">TOTAL FACTURAS</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasexo,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasinaf,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasigv,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasicbper,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturas,'2','.','')}}</td>
									<td colspan="5"></td>
								</tr>
								@endif


								@if(count($boletas)>0)
									@foreach($boletas as $boleta)
								<tr>
								
								 	<td>{{$boleta->cod_mov}}</td>
								 	<td>{{Carbon::parse($boleta->ccafem)->format('d-m-Y')}}</td>
									<td>{{Carbon::parse($boleta->fechaven)->format('d-m-Y')}}</td>
									<td>{{$boleta->tdocod}}</td>
									<td>{{$boleta->serie}}</td>
									<td>{{$boleta->numero}}</td>
									<td>{{$boleta->tdicod}}</td>
									<td>{{$boleta->numerodocumento}}</td>
									<td>{{$boleta->cliente}}</td>
								 	<td></td>
									<td></td>
									<td style="text-align:right;">{{$boleta->ccatexo}}</td>
									<td style="text-align:right;">{{$boleta->ccatvi}}</td>
									<td style="text-align:right;">{{$boleta->ccaigv}}</td>
									<td style="text-align:right;">{{$boleta->icbper}}</td>
									<td style="text-align:right;">{{$boleta->ccaitv}}</td>
									<td style="text-align:right;">{{$boleta->tipcambio}}</td>
									<td>{{$boleta->ccafem_ref}}</td>
									<td>{{$boleta->tdocod_ref}}</td>
									<td>{{$boleta->serie_ref}}</td>
									<td>{{$boleta->num_ref}}</td>
									
								</tr>
								@endforeach

								<tr>
									<td colspan="11" style="font-weight:bold;">TOTAL BOLETAS</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasexo,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasinaf,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasigv,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasicbper,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletas,'2','.','')}}</td>
									<td colspan="5"></td>
								</tr>
								@endif


								@if(count($notas)>0)
								@foreach($notas as $nota)
								<tr>
								
								 	<td>{{$nota->cod_mov}}</td>
								 	<td>{{Carbon::parse($nota->ccafem)->format('d-m-Y')}}</td>
									<td>{{Carbon::parse($nota->fechaven)->format('d-m-Y')}}</td>
									<td>{{$nota->tdocod}}</td>
									<td>{{$nota->serie}}</td>
									<td>{{$nota->numero}}</td>
									<td>{{$nota->tdicod}}</td>
									<td>{{$nota->numerodocumento}}</td>
									<td>{{$nota->cliente}}</td>
								 	<td></td>
									<td></td>
									<td style="text-align:right;">{{$nota->ccatexo}}</td>
									<td style="text-align:right;">{{$nota->ccatvi}}</td>
									<td style="text-align:right;">{{$nota->ccaigv}}</td>
									<td style="text-align:right;">{{$nota->icbper}}</td>
									<td style="text-align:right;">{{$nota->ccaitv}}</td>
									<td>{{$nota->tipcambio}}</td>
									<td>{{$nota->ccafem_ref}}</td>
									<td>{{$nota->tdocod_ref}}</td>
									<td>{{$nota->serie_ref}}</td>
									<td>{{$nota->num_ref}}</td>
									
								</tr>
								@endforeach
									<tr>
									<td colspan="11" style="font-weight:bold;">TOTAL NOTAS DE CRÉDITOS</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasexo,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasinaf,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasigv,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasicbper,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotas,'2','.','')}}</td>
									<td colspan="5"></td>
								</tr>
								@endif

							</tbody>
						</table><br>



					




	
</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>