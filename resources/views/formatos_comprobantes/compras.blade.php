

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
				<th colspan="6" style="text-align:left;width:85%;border:0px;">{{$sucursal->direccion}}</th>
				
				<th colspan="1" style="text-align:right;border:0px;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="6" style="text-align:left;width:85%;border:0px;">RUC: {{$empresa->IdEmpresa}}</th>
				<th colspan="1" style="text-align:right;border:0px;"></th>
				
			</tr>
			
		</thead>

	</table>
	
</div>

	
<br>
<div class="detalle" style="font-size:7pt;">
	
	<table style="width:100%;border: 0px 0px 0px 0px;">
							
							<thead>
								<tr >
									<th colspan="4"><center><strong>LISTA DE INGRESO DE MERCADERIA</strong></center></th>
								</tr>
								<tr>
									<th style="text-align:left;">Proveedor:</th>
									<th >{{$cabpdf->prov_raz}}</th>
									<th style="text-align:left;">RUC:</th>
									<th >{{$cabpdf->prov_ruc}}</th>
								</tr>
								
								<tr>
									<th style="text-align:left;">Dirección:</th>
									<th >{{$cabpdf->prov_dir}}</th>
									<th style="text-align:left;" >Comprobante:</th>
									<th >{{$cabpdf->com_doc_ser}}-{{$cabpdf->com_doc_num}}</th>
								</tr>
								<tr>
									<th style="text-align:left;">Ag Transporte:</th>
									<th ></th>
									<th style="text-align:left;">N° Guía:</th>
									<th ></th>
								</tr>
								<tr>
									<th style="text-align:left;">N° Bultos:</th>
									<th ></th>
								
									<th style="text-align:left;">P. Neto:</th>
									<th ></th>
								</tr>
								<tr>
									<th style="text-align:left;">F. Emisión:</th>
									<th ></th>
									<th style="text-align:left;">F. Ingreso:</th>
									<th ></th>
									
								</tr>
								
							</thead>
	</table>
	<br>
	<table style="width:100%;">
		<thead>
			<tr style="background:gray">
									<th style="border-color:black;width:80px;"><center><font color="white" size='1'>ITEM</font></center></th>
									<th style="border-color:black;width:80px;"><center><font color="white" size='1'>CODIGO.</font></center></th>
									<th style="border-color:black;width:80px;"><center><font color="white" size='1'>CANTIDAD</font></center></th>
									<th style="border-color:black;width:80px;"><center><font color="white" size='1'>U.M</font></center></th>
									<th style="border-color:black;width:150px;"><center><font color="white" size='1'>DESCRIPCIÓN</font></center></th>
									<th style="border-color:black;width:80px;" ><center><font color="white" size='1'>P.U</font></center></th>
									<th style="border-color:black;width:80px;" ><center><font color="white" size='1'>P.VENTA</font></center></th>
								</tr>
		</thead>
							<tbody>
								<?php $i=0; ?>
									@foreach($detpdf as $det)
								<?php $i=$i+1; ?>
									<tr>
										<td style="border-color:black;width:80px;" ><center><font size='1'>{{$i}}</font></center></td>
										<td style="border-color:black;width:80px;"><center><font size='1'>{{$det->procod}}</font></center></td>
										<td style="border-color:black;width:80px;" ><center><font size='1'>{{$det->cantidad}}</font></center></td>
										<td style="border-color:black;width:80px;" class=" text-right "><center><font size='1'>{{$det->umecod}}</font></center></td>
										<td style="border-color:black;width:150px;"><font size='1'> {{$det->pronom}}</font></td>
										<td style="border-color:black;width:80px;" class=" text-right "><center><font size='1'>{{number_format($det->pre_uni,'2','.',',')}}</font></center></td>
										<td style="border-color:black;width:80px;" class=" text-right "><center><font size='1'>{{number_format($det->total,'2','.',',')}}</font></center></td>
									</tr>
											
									@endforeach
								

								<tr >	
										<td colspan="6" class="width-table text-left" style="font-size:7;border-top:black 1px solid;"><strong>SUBTOTAL</strong></td>
								
										<td class="width-table text-right" style="font-size:7;text-align: right;width:70%;border-top:black 1px solid;">@if($cabpdf->moncod=='USD') $ @else S/ @endif {{number_format($cabpdf->subtot_com,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td  colspan="6" class="width-table text-left" style="font-size:7"><strong>OP. GRAVADA</strong></td>
			
										<td class="width-table" style="font-size:7;text-align: right;">@if($cabpdf->moncod=='USD') $ @else S/ @endif {{number_format($cabpdf->com_grav,'2','.',',')}} </td>
									</tr>
									<tr>	
										<td colspan="6" class="width-table text-left" style="font-size:7"><strong>OP. EXONERADA</strong></td>
									
										<td class="width-table" style="font-size:7;text-align: right;">@if($cabpdf->moncod=='USD') $ @else S/ @endif {{number_format($cabpdf->com_exo,'2','.',',')}} </td>
									</tr>
									<tr>	
										<td colspan="6" class="width-table text-left" style="font-size:7"><strong>OP. INAFECTA</strong></td>
									
										<td class="width-table" style="font-size:7;text-align: right;">@if($cabpdf->moncod=='USD') $ @else S/ @endif {{number_format($cabpdf->com_inaf,'2','.',',')}} </td>
									</tr>
									<tr>	
										<td colspan="6" class="width-table text-left" style="font-size:7"><strong>IGV</strong></td>
									
										<td class="width-table text-right" style="font-size:7;text-align: right;width:70%;">@if($cabpdf->moncod=='USD') $ @else S/ @endif{{number_format($cabpdf->com_cab_igv,'2','.',',')}}</td>
									</tr>
							
									
								
									<tr style="border-bottom:black 1px solid;" >
										<td colspan="6" class="width-table text-left" style="font-size:7"><strong>IMPORTE TOTAL</strong></td>
						
										<td class="width-table text-right" style="font-size:7;text-align: right" ><strong>@if($cabpdf->moncod=='USD') $ @else S/ @endif {{number_format($cabpdf->total_com,'2','.',',')}}</strong></td>
									</tr>
									


							</tbody>
						</table><br>



					




	
</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>