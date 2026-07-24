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

					<br><font><center><strong>REPORTE DE PAGOS DE PERSONAL</strong></center></font>
			
			</div>
			<div class="detalle-cliente">
							 	<table style="width:100%">
							 		<thead>
							 			<tr  >
							 				<th style="font-size:10pt">Personal</th>
							 				<th style="font-size:10pt">Desde</th>
							 				<th style="font-size:10pt">Hasta</th>
							 				
							 			</tr>
							 		</thead>

							 		<tbody>
							 			<tr style="text-align:center;">
							 		    <td style="font-size:8pt">{{$datos_colaborador->name}} {{$datos_colaborador->apeusu}}</td>
							 		   <td valign="top" style="width:25%;"><font style="font-size:8pt" style="font-family:sans-serif;"> {{$fecin}}</font></td>
							 		   <td valign="top" style="width:25%;"><font style="font-size:8pt" style="font-family:sans-serif;"> {{$fecfin}}</font></td>
							 		
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
										<th><center><font style="font-size:7pt;width:10%">EFECTIVO</font></center></th>
									</tr>
									<tr>
										<th colspan="6"><hr></th>
									</tr>
								</thead>
							<tbody>
							
						
							<tr>
								<th colspan="6" style="text-align:left;font-size:9pt">PAGOS</th>
								
							</tr>
							@foreach($pagos as $gast)
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
								<th colspan="5" style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">TOTAL PAGOS</th>
								<TH style="text-align:left;font-size:9pt;font-weight:bold;width:10%;">{{number_format($totalgas,'2','.',',')}}</TH>
							</tr>

						

							</tbody>
					
							
							</table>
							
				</div>
				
			
	</body>
</html>