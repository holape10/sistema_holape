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
		



	<table style="width:100%;">
		<thead>
			<tr>
				<th colspan="3" style="text-align:left;">{{$empresa->NomEmpresa}}</th>
				<th colspan="3" style="text-align:right;">Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr >
				<th colspan="3" style="text-align:left;">{{$sucursal->direccion}}</th>
				<th colspan="3" style="text-align:right;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="3" style="text-align:left;">{{$empresa->IdEmpresa}}</th>
				<th></th>
			</tr>
			<tr>
				<th colspan="6">ARQUEO DIARIO DE CAJA - {{Carbon::parse($fecha)->format('d-m-Y')}}<br>
				</th>
			</tr>
		</thead>

	</table>
	
</div>

	

<div class="detalle">
	



	<table class="table-detalle" style="width:100%;padding:10px;">
		<thead>
			<tr>
				<td colspan="7"><hr></th>
			</tr>
			<tr>
				<td style="width:13%" ><center><font >FECHA</font></center></th>
					<td style="width:10%" ><center><font >COD. DOC.</font></center></th>
						<td style="width:10%"><center><font >SERIE</font></center></th>
						<td style="width:10%"><center><font >NUMERO</font></center></th>
						<td style="width:60%"><center><font >DESCRIPCION</font></center></th>
						<td style="width:10%"><center><font >EFECT.</font></center></th>
							<td style="width:10%"><center><font >DEPOS.</font></center></th>
			</tr>
			<tr>
				<td colspan="7"><hr></th>
			</tr>
		</thead>
		<tbody>

			<!--<tr>
				<td colspan="7" style="text-align:left">SALDO ANTERIOR</td>

			</tr>

			<tr>
				<td colspan="5" style="text-align:left;font-weight:normal;"></td>

				<td style="text-align:left;font-weight:normal;text-align:right;">{{$saldo}}</td>
				<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>-->

			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr>
				<td colspan="7" style="text-align:left">01. (+) VENTAS DEL DIA</td>

			</tr>
			@foreach($registroventascontado as $comp)
			<tr>
				<td style="text-align:left;font-weight:normal;">{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
				<td style="text-align:left;font-weight:normal;">{{$comp->tdocod}}</td>
					<td style="text-align:left;font-weight:normal;">{{$comp->serdoc}}</td>
						<td style="text-align:left;font-weight:normal;">{{$comp->numdoc}}</td>
							<td style="text-align:left;font-weight:normal;">{{$comp->ccanom}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;">{{$comp->ccaitv}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			@endforeach
			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL VENTAS DEL DÍA</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($ventascontado,'2','.',',')}}</td>
						<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			<tr>
				<td colspan="7"></td>	
			</tr>
			<tr>
				<td colspan="7"></td>	
			</tr>
			<tr>
				<td colspan="7" style="text-align:left">02. VENTAS AL CRÉDITO</td>

			</tr>
			@foreach($registroventascredito as $comp)
			<tr>
				<td style="text-align:left;font-weight:normal;">{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
				<td style="text-align:left;font-weight:normal;">{{$comp->tdocod}}</td>
					<td style="text-align:left;font-weight:normal;">{{$comp->serdoc}}</td>
						<td style="text-align:left;font-weight:normal;">{{$comp->numdoc}}</td>
							<td style="text-align:left;font-weight:normal;">{{$comp->ccanom}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;">{{$comp->ccaitv}}</td>
									<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			@endforeach
			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL VENTAS AL CREDITO</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($ventascredito,'2','.',',')}}</td>
						<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			<tr>
				<td colspan="7"><hr></td>	
			</tr>

			<tr>
				<td colspan="7"></td>	
			</tr>

			<tr>
				<td colspan="7" style="text-align:left">03. (+) COBRANZAS</td>

			</tr>
			@foreach($cobranzas as $comp)
			<tr>
				<td style="text-align:left;font-weight:normal;">{{Carbon::parse($comp->fec_reg)->format('d-m-Y')}}</td>
				<td style="text-align:left;font-weight:normal;">{{$comp->tdocod}}</td>
					<td style="text-align:left;font-weight:normal;">{{$comp->serdoc}}</td>
						<td style="text-align:left;font-weight:normal;">{{$comp->numdoc}}</td>
							<td style="text-align:left;font-weight:normal;">{{$comp->ccanom}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;">{{$comp->abono}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;">{{$comp->monto_deposito}}</td>
			</tr>
			@endforeach
			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL COBRANZAS</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($totalcobranzas,'2','.',',')}}</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($totalcobranzasdepositos,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="7"><hr></td>	
			</tr>

			<tr>
				<td colspan="7"></td>	
			</tr>
			<tr>
				<td colspan="7" style="text-align:left">04. (-) PAGOS</td>

			</tr>
			@foreach($pagos as $comp)
			<tr>
				<td style="text-align:left;font-weight:normal;">{{Carbon::parse($comp->fec_reg)->format('d-m-Y')}}</td>
				<td style="text-align:left;font-weight:normal;">{{$comp->tdocod}}</td>
					<td style="text-align:left;font-weight:normal;">{{$comp->com_doc_ser}}</td>
						<td style="text-align:left;font-weight:normal;">{{$comp->com_doc_num}}</td>
							<td style="text-align:left;font-weight:normal;">{{$comp->proveedor}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;">{{$comp->abono}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			@endforeach
			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL PAGOS</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($totalpagos,'2','.',',')}}</td>
						<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			<tr>
				<td colspan="7"><hr></td>	
			</tr>


			<tr>
				<td colspan="7" style="text-align:left">10. (-) GASTOS VARIOS DE GESTIÓN</td>

			</tr>
			@foreach($gastos as $gast)
			<tr>
				<td style="text-align:left;font-weight:normal;">{{Carbon::parse($gast->gast_fec)->format('d-m-Y')}}</td>
				<td style="text-align:left;font-weight:normal;">{{$gast->tdocod}}</td>
					<td style="text-align:left;font-weight:normal;">{{$gast->gast_doc_ser}}</td>
						<td style="text-align:left;font-weight:normal;">{{$gast->gast_doc_num}}</td>
							<td style="text-align:left;font-weight:normal;">{{$gast->det_gasto}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;">{{$gast->total}}</td>
								<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			@endforeach
			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL GASTOS</td>
					<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($totalgast,'2','.',',')}}</Td>
								<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>

			<tr>
				<td colspan="7"><hr></td>	
			</tr>


			<tr>
				<td colspan="7" style="text-align:left">15. (+) OTROS INGRESOS</td>

			</tr>

			@foreach($ingresos as $ing)
			<tr>
				<td style="text-align:left;font-weight:normal;">{{$ing->gast_fec}}</td>
					<td style="text-align:left;font-weight:normal;">{{$ing->tdocod}}</td>
						<td style="text-align:left;font-weight:normal;">{{$ing->gast_doc_ser}}</td>
						<td style="text-align:left;font-weight:normal;">{{$ing->gast_doc_num}}</td>
						<td style="text-align:left;font-weight:normal;">{{$ing->det_gasto}}</td>
						<td style="text-align:left;font-weight:normal;text-align:right;">{{$ing->total}}</td>
							<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>
			@endforeach
			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL OTROS INGRESOS</td>
					<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($totalingreso,'2','.',',')}}</td>
							<td style="text-align:left;font-weight:normal;text-align:right;"></td>
			</tr>


			<tr>
				<td colspan="7"></td>	
			</tr>
			<tr>
				<td colspan="7"></td>	
			</tr>

			

			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			

				<tr>
				<td colspan="7" style="text-align:center;background:#D1D1D1;font-weight:bold;">RESUMEN INGRESOS</td>

			</tr>

			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;">COBRANZAS DE DOCUMENTOS A CREDITO</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($totalcobranzas,'2','.',',')}}</td>
							<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>

			<tr >
					<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;">TOTAL FACTURAS EFECTIVO</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($ventasfactura,'2','.',',')}}</td>
					<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>

			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;">TOTAL BOLETAS EFECTIVO</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($ventasboleta,'2','.',',')}}</td>
							<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>

				<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;">TOTAL NOTAS EFECTIVO</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($notascontado,'2','.',',')}}</td>
							<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>

			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;">OTROS INGRESOS</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($totalingreso,'2','.',',')}}</td>
							<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>

				<tr style="text-align:center;background:#D1D1D1;font-weight:bold;padding:5px;">
					<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;;">TOTAL INGRESOS</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($notascontado+$totalcobranzas+$ventasfactura+$ventasboleta+$totalingreso,'2','.',',')}}</td>
					<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>


	

			<tr>
				<td colspan="7"></td>	
			</tr>

			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			

			<tr>
				<td colspan="7" style="text-align:center;background:#D1D1D1;font-weight:bold;">RESUMEN EGRESOS</td>

			</tr>


		


		
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;">TOTAL GASTOS - PAGOS</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($totalgast+$totalpagos,'2','.',',')}}</td>
							<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>

				<tr style="text-align:center;background:#D1D1D1;font-weight:bold;padding:5px;">
					<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;;">TOTAL EGRESOS</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($totalgast+$totalpagos,'2','.',',')}}</td>
					<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>


				<tr>
				<td colspan="7"></td>	
			</tr>

			<tr>
				<td colspan="7"><hr></td>	
			</tr>
			

			<tr style="text-align:center;background:#D1D1D1;font-weight:bold;padding:5px;">
					<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;;">TOTAL SALDO ( INGRESOS-EGRESOS )</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format(($notascontado+$totalcobranzas+$ventasfactura+$ventasboleta+$totalingreso)-($totalgast+$totalpagos),'2','.',',')}}</td>
					<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>
				<tr>
				<td colspan="7"><hr></td>	
			</tr>
			<tr style="text-align:center;background:#D1D1D1;font-weight:bold;padding:5px;">
					<td colspan="5" style="text-align:left;font-weight:bold;padding:5px;;">TOTAL VALES CONSUMO</td>
					<td style="text-align:left;font-weight:bold;text-align:right;padding:5px;">{{number_format($consumo,'2','.',',')}}</td>
					<td style="text-align:left;font-weight:normal;text-align:right;padding:5px;"></td>
			</tr>




		





		</tbody>


	</table>


</div>


			<!--<div class="montoletras">
		
								
			
			</div>-->




		</body>
		</html>