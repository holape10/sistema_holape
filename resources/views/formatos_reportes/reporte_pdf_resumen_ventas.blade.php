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
			top: -30px;
			width:90%;

		}

		.logo{
			position:absolute;
			top:0px;
			left:0px;
			width:90%;

		}

		.panel-logo{
			position:relative;
			top:0px;
			left:170px;
			width:90%;


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
			width:90%;
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


		.border-table {
  border: 1px solid black;
    padding:5px;
}

table {
 
  border-collapse: collapse;
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


		
		.comprobante{
			text-align:center;
			background-color:gray;
			width:90%;
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
			width:90%;
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
	<!--	<script type="text/php">
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
				<th colspan="5" style="text-align:left;">{{$empresa->NomEmpresa}}</th>
				<th colspan="3" style="text-align:right;">Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr >
				<th colspan="5" style="text-align:left;">{{$sucursal->direccion}}</th>
				<th colspan="3" style="text-align:right;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="5" style="text-align:left;">{{$empresa->IdEmpresa}}</th>
				<th></th>
			</tr>
			
		</thead>

	</table>
	
</div>

	

<div class="detalle">
	
<table style="width:100%;" class="table-comprobantes">
							<thead >
								<tr>
									<th colspan="8"><center><strong>RESUMEN DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} </strong></center></th>
									
								</tr>
							
								<tr>
									<td colspan='8'><hr></td>
								</tr>
								<tr  >
									<th class="border-table" rowspan="2" >CODIGO DESRIPCION</th>
									<th class="border-table" rowspan="2" >VALOR FACTURADO DE EXPORTAC.</th>
									<th class="border-table" rowspan="2" >BASE IMPONIBLE DE LA OPERAC. GRAVADA</th>
									<th class="border-table"  colspan="2">IMPORTE TOTAL DE OPER. EXONERAD O INAFECTA</th>
									<th class="border-table" rowspan="2" >IGV Y/O I.P.M</th>
									<th class="border-table" rowspan="2" >OTR. TRIB. QUE NO FORMAN PARTE DEL LAB .I</th>
									<th class="border-table" rowspan="2" >IMPORTE TOTAL DEL COMP. DE PAGO</th>
								</tr>
								<tr>
								
									<th  class="border-table" >EXONERADA</th>
									<th  class="border-table" >INAFECTA</th>
									
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan='8'><br><hr></td>
								</tr>
								
								<tr>
									<td>01 F/.</td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;">{{number_format($totalgravadasfacturas,'2','.','')}}</td>
									<td style="text-align:right;">{{number_format($totalexoneradasfacturas,'2','.','')}}</td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;">{{number_format($totalgravadasfacturas+$totalexoneradasfacturas,'2','.','')}}</td>

								</tr>
								
								<tr>
									<td>03 B/V</td>
									<td></td>
									<td style="text-align:right;">{{number_format($totalgravadasboletas,'2','.','')}}</td>
									<td style="text-align:right;">{{number_format($totalexoneradasboletas,'2','.','')}}</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="text-align:right;">{{number_format($totalgravadasboletas+$totalexoneradasboletas,'2','.','')}}</td>

								</tr>
								<tr>
									<td>07 N/C</td>
									<td></td>
									<td style="text-align:right;">{{number_format((-1)*$totalgravadasnotas,'2','.','')}}</td>
									<td style="text-align:right;">{{number_format((-1)*$totalexoneradasnotas,'2','.','')}}</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="text-align:right;">{{number_format((-1)*($totalgravadasnotas+$totalexoneradasnotas),'2','.','')}}</td>

								</tr>
							

								<tr>
									<td colspan='8'><hr></td>
								</tr>
								<tr>
									<td  style="text-align:right;"><strong>TOTAL VENTA S/. </strong></td>
									<td></td>
									<td style="text-align:right;">{{number_format(($totalgravadasfacturas+$totalgravadasboletas)-($totalgravadasnotas),'2','.','')}}</td>
									<td style="text-align:right;">{{number_format(($totalexoneradasfacturas+$totalexoneradasboletas)-($totalexoneradasnotas),'2','.','')}}</td>
									<td></td>
									<td></td>
									<td></td>
									<td  style="text-align:right;"><strong>{{number_format(($totalgravadasfacturas+$totalgravadasboletas+$totalexoneradasfacturas+$totalexoneradasboletas)-($totalgravadasnotas+$totalexoneradasnotas),'2','.',',')}}</strong></td>
								</tr>
							</tbody>
						</table><br>
				



	
</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>