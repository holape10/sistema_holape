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
				width:100%;
				top:130px;
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
 	
	<body style="font-family:sans-serif">

	

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
		

					<table style="width:100%">
						<thead>
							<tr>
								<td style="width:75%;"><strong>{{$empresa->NomEmpresa}}</strong></td>
								
								
							</tr>
							<tr>
								<td style="width:75%;"><strong>{{$sucursal->direccion}}</strong></td>
								
								<td style="width:25%;"></td>
							</tr>
							<tr>
								<td style="width:75%;"><strong>{{$empresa->IdEmpresa}}</strong></td>
								
								<td style="width:25%;"></td>
							</tr>
						</thead>

					</table>

					<br><font><center><strong>RESUMEN ARQUEO DIARIO DE CAJA</strong></center></font>
			
			</div>
			<div class="detalle-cliente">
							 	<table style="width:100%">
							 		<thead>
							 			<tr  >
							 				<th style="font-size:10pt">Usuario</th>
							 				<th style="font-size:10pt">Apertura</th>
							 				<th style="font-size:10pt">Cierre</th>
							 				
							 			</tr>
							 		</thead>

							 		<tbody>
							 			<tr style="text-align:center;">
							 		    <td style="font-size:8pt">{{$usuario->name}} {{$usuario->apeusu}}</td>
							 		   <td valign="top" style="width:25%;"><font style="font-size:8pt" style="font-family:sans-serif;"> {{$datos->apertura}}</font></td>
							 		   <td valign="top" style="width:25%;"><font style="font-size:8pt" style="font-family:sans-serif;"> {{$datos->cierre}}</font></td>
							 		
							 		</tr>
							 		</tbody>
							 		
							 	
							 		
							 	</table>

								
			
				</div>
				



				
				<div class="detalle">

							<table class="table-detalle" style="width:100%;padding:10px;">
								<thead>
									<tr>
										<th colspan="6"><hr></th>
									</tr>
									<tr>
										<th ><center><font style="font-size:7pt;width:10%">FECHA</font></center></th>
										<th ><center><font style="font-size:7pt;width:10%">COD. DOC.</font></center></th>
										<th ><center><font style="font-size:7pt;width:10%">SERIE</font></center></th>
										<th ><center><font style="font-size:7pt;width:10%">NUMERO</font></center></th>
										<th><center><font style="font-size:7pt;width:60%">DESCRIPCION</font></center></th>
										<th><center><font style="font-size:7pt;width:10%"">EFECTIVO</font></center></th>
									</tr>
									<tr>
										<th colspan="6"><hr></th>
									</tr>
								</thead>
							<tbody>
							
							<tr>
								<th colspan="6" style="text-align:left;font-size:9pt">CAJA CHICA</th>
								
							</tr>
							<tr>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{date('d/m/Y',strtotime($datos->apertura))}}</th>
								<th style="width:10%;"></th>
								<th style="width:10%;"></th>
								<th style="width:10%;"></th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:60%;">FONDO DE CAJA</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$datos->monto}}</th>
							</tr>
							<tr>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{date('d/m/Y',strtotime($datos->apertura))}}</th>
								<th style="width:10%;"></th>
								<th style="width:10%;"></th>
								<th style="width:10%;"></th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:60%;">INGRESOS EFECTIVO</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$totaling}}</th>
							</tr>
								<tr>
							  		<th colspan="6"><hr></th>	
							</tr>
							<tr>
								<th colspan="5" style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">TOTAL INGRESOS CAJA</th>
								<TH style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">{{$datos->monto+$totaling}}</TH>
							</tr>
								<tr>
							  		<th colspan="6"></th>	
							</tr>
								<tr>
							  		<th colspan="6"></th>	
							</tr>
							<tr>
								<th colspan="6" style="text-align:left;font-size:9pt">VENTAS</th>
								
							</tr>
							@foreach($comprobantes as $comp)
							<tr>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$comp->fecha_hora}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$comp->tdocod}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$comp->serdoc}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$comp->numdoc}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;"></th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$comp->ccaitv}}</th>
							</tr>
							@endforeach
								<tr>
							  		<th colspan="6"><hr></th>	
							</tr>
							<tr>
								<th colspan="5" style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">TOTAL VENTAS EFECTIVO</th>
								<TH style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">{{$total}}</TH>
							</tr>
							<tr>
							  		<th colspan="6"></th>	
							</tr>
								<tr>
							  		<th colspan="6"></th>	
							</tr>
							<tr>
								<th colspan="6" style="text-align:left;font-size:9pt">GASTOS</th>
								
							</tr>
							@foreach($gastos as $gast)
							<tr>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$gast->gast_fec}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$gast->tdocod}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$gast->gast_doc_ser}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$gast->gast_doc_num}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$gast->det_gasto}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$gast->total}}</th>
							</tr>
							@endforeach
								<tr>
							  		<th colspan="6"><hr></th>	
							</tr>
							<tr>
								<th colspan="5" style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">TOTAL GASTOS</th>
								<TH style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">{{number_format($totalgas,'2','.',',')}}</TH>
							</tr>

							<tr>
							  		<th colspan="6"></th>	
							</tr>
								<tr>
							  		<th colspan="6"></th>	
							</tr>
							<tr>
								<th colspan="6" style="text-align:left;font-size:9pt">COMPRAS</th>
								
							</tr>
							@foreach($compras as $compra)
							<tr>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$compra->com_fec}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$compra->tdocod}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$compra->com_doc_ser}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$compra->com_doc_num}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$compra->pronom}}</th>
								<th style="text-align:left;font-size:7pt;font-weight:normal;width:10%;">{{$compra->total}}</th>
							</tr>
							@endforeach
								<tr>
							  		<th colspan="6"><hr></th>	
							</tr>
							<tr>
								<th colspan="5" style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">TOTAL COMPRAS</th>
								<TH style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">{{number_format($totalcompras,'2','.',',')}}</TH>
							</tr>

							</tbody>
					
							
							</table>
							
				</div>
				
				<div class="totales">
							
							<hr>
							<hr>
							<table class="table-detalle" style="width:50%;" >
							<thead>
								
								<tr>
									<th style="font-weight:bold;text-align:left;">RESUMEN</th>
									<th></th>
									
								</tr>
							</thead>
							
							<tbody >
								<tr>
									<th style="width:25%;font-size:10pt;font-weight:normal;text-align:left;">TOTAL INGRESO</th>
									<th style="width:25%;font-size:10pt;font-weight:normal;text-align:left;">{{number_format($totaling+$total+$datos->monto,'2','.',',')}}</th>
								</tr>
								<tr>
									<th style="width:25%;font-size:10pt;font-weight:normal;text-align:left;">TOTAL EGRESO</th>
									<th style="width:25%;font-size:10pt;font-weight:normal;text-align:left;">{{number_format($totalgas+$totalcompras,'2','.',',')}}</th>
								</tr>
								<tr>
									<th style="width:25%;"></th>
									<th style="width:25%;font-weight:bold;text-align:left;"><hr></th>
								</tr>
								<tr>
									<th style="width:25%;font-weight:bold;text-align:left;">SALDO</th>
									<th style="width:25%;font-weight:bold;text-align:left;">{{number_format(($totaling+$total+$datos->monto)-($totalgas+$totalcompras),'2','.',',')}}</th>
								</tr>
							</tbody>
									</table>



				</div>
		
			<!--<div class="montoletras">
		
								
			
			</div>-->

		

	
	</body>
</html>