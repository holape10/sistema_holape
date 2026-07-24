 <!DOCTYPE html> 
 <html> 
 	<head>
 		
 		<style type="text/css">
 			body{
 			
 				margin-left:-25px;
 				margin-right: -25px;
 			}	
 		</style>

 		<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">
	       

 	<head>
 	<body style="font-family:Helvética;font-size:8;">
 		<script type="text/php">
		    if ( isset($pdf) ) {
		        $pdf->page_script('
		            $font = $fontMetrics->get_font("Arial, Helvetica, sans-serif", "normal");
		            $pdf->text(270, 10, "Página $PAGE_NUM", $font, 10);
		           
		        ');
		    }
		</script>

		 		

 
 		<table style="width:100%;">
 			<thead>
 				<tr>
 					<th colspan="3" style="text-align:left;">{{$data_emp->NomEmpresa}}</th>
 					<th style="text-align:left;">Fecha: {{now()->format('Y-m-d')}}</th>
 				</tr>
 				<tr >
 					<th colspan="3" style="text-align:left;">{{$data_neg->direccion}}</th>
 					<th style="text-align:left;">Hora: {{now()->format('H-i-s')}}</th>
 				</tr>
 				<tr>
 					<th colspan="3" style="text-align:left;">{{$data_emp->IdEmpresa}}</th>
 					<th></th>
 				</tr>
 				<tr>
 					<th colspan="4">REPORTE DE COBRANZAS (CLIENTES) AL: {{now()->format('Y-m-d')}}<br>
 						@if(!empty($data_cliente))CLIENTE: {{$data_cliente->clinom}}   @endif</th>
 				</tr>
 			</thead>
 			
 		</table>

 		<table style="width: 100%;">
 			
 			<thead style="border-top:1px solid;border-bottom:1px solid;">
 				<tr>
 				<th width="30%">RUC</th>
 				<th width="30%">Documento</th>
 				<th width="30%">Recibo</th>
 				<th width="30%">Nombre y/o Razón Social</th>
 				<th width="15%">Cuenta</th>
 				<th width="15%">A CTA</th>
 				<th width="15%">Saldo</th>
 				
 				
 			</tr>

 			</thead>
 				<tbody>

 		    	@php
 						$totalgeneral=0;
 						$totalamortgeneral=0;
 						$totalsaldogeneral=0;
 					@endphp

 		
 					

 					@php
 						$total=0;
 						$totalamort=0;
 						$totalsaldo=0;
 					@endphp

		 			@foreach($cuentas as $cuenta)


		 				@php

				 				

				 				$total = $total+$cuenta->abono;
		 					

				 			@endphp
						 	<tr>
						 		<td width="30%">{{$cuenta->ruc}}</td>
						 		<td width="30%">{{$cuenta->serdoc}}-{{$cuenta->numdoc}}</td>
						 		<td width="30%">{{$cuenta->numero_recibo}}</td>
						 		<td width="60%">{{$cuenta->cliente}}</td>
						 		<td width="30%" style="text-align:right;">{{$cuenta->total_detalle}}</td>
						 		<td width="30%" style="text-align:right;">{{$cuenta->abono}}</td>
						 		<td width="30%" style="text-align:right;">{{$cuenta->saldo_detalle}}</td>

						 		
						 	
						 	</tr>
						 	
		 				
		 			@endforeach



				 			@php

				 			

				 				$totalgeneral = $totalgeneral+$total;
		 					
				 			@endphp


		 	
		 			
		 				 	<tr>
						 		<td colspan="6"></td>
						 	</tr>

 	<tr>
						 		<td colspan="6"></td>
						 	</tr>

		 				<tr>
						 		<td colspan="6"  style="text-align:right;"><strong>TOTAL COBRANZA</strong></td>
						 		<td style="border-top:1px solid;">{{number_format($totalgeneral,2,'.',',')}}</td>
						 		
						 	</tr> 
 			</tbody>
 		

 </table>
 	</body>

 </html>
