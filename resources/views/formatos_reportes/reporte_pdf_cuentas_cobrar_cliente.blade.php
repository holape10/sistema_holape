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
 					<th colspan="10" style="text-align:left;">{{$data_emp->NomEmpresa}}</th>
 					<th style="text-align:left;">Fecha: {{now()->format('Y-m-d')}}</th>
 				</tr>
 				<tr >
 					<th colspan="10" style="text-align:left;">{{$data_neg->direccion}}</th>
 					<th style="text-align:left;">Hora: {{now()->format('H-i-s')}}</th>
 				</tr>
 				<tr>
 					<th colspan="10" style="text-align:left;">{{$data_emp->IdEmpresa}}</th>
 					<th></th>
 				</tr>
 				<tr>
 					<th colspan="11">REPORTE DE CUENTAS POR COBRAR (CLIENTES) AL: {{now()->format('Y-m-d')}}<br>
 						@if(!empty($data_cliente)) CLIENTE: {{$data_cliente->clinom}}   @endif</th>
 				</tr>
 			</thead>
 			
 		</table>

 		<table style="width: 100%;">
 			
 			<thead style="border-top:1px solid;border-bottom:1px solid;">
 				<tr>
 				<th width="30%">Doc.</th>
 				<th width="30%">Serie</th>
 				<th width="30%">Número</th>
 				<TH width="30%">Fecha</TH>
 				<th width="30%">Días</th>
 				<th width="30%">Vcmto.</th>
 				<th width="5%">D. Vencidos</th>
 				<th width="30%">Total</th>
 				<th width="30%">Amortiz.</th>
 				<th width="30%">Saldo</th>
 				<th width="30%">Obser.</th>
 			</tr>

 			</thead>
 				<tbody>


 		

 					@php
 						$total=0;
 						$totalamort=0;
 						$totalsaldo=0;
 					@endphp

		 			@foreach($cuentas as $cuenta)
		 			

				 		
				 			@if($cuenta->saldo>'0')
				 				@php

				 				$fecha1 = date_create($cuenta->ccafem);
				 				$fecha2 = date_create($cuenta->fec_ven);
				 				$dias = date_diff($fecha1, $fecha2)->format('%a');

				 				$total = $total+$cuenta->ccaitv;
		 					$totalamort = $totalamort+$cuenta->abono;
		 					$totalsaldo = $totalsaldo+$cuenta->saldo;

				 				if($cuenta->fec_ven < now()->format('Y-m-d')){
				 					$fecha3 = date_create($cuenta->fec_ven);
				 					$fecha4 = date_create(now()->format('Y-m-d'));
				 					$diasven = date_diff($fecha3, $fecha4)->format('%a');
				 				}else{
				 					$diasven = 0;
				 				}

				 			@endphp
				 			<tr>
				 				<th colspan="11"></th>
				 			</tr>
						 	<tr>
						 		<td width="30%">{{$cuenta->tdocod}}</td>
						 		<td width="30%">{{$cuenta->serdoc}}</td>
						 		<td width="30%">{{$cuenta->numdoc}}</td>
						 		<td width="30%">{{Carbon::parse($cuenta->ccafem)->format('d-m-Y')}}</td>
						 		<td width="30%">{{$dias}}</td>
						 		<td width="30%">{{Carbon::parse($cuenta->fec_ven)->format('d-m-Y')}}</td>
						 		<td width="30%">{{$diasven}}</td>
						 		<td width="30%">{{$cuenta->ccaitv}}</td>
						 		<td width="30%">{{$cuenta->abono}}</td>
						 		<td width="30%">{{$cuenta->saldo}}</td>
						 		<td width="30%"></td>
						 	</tr>
						 	
		 					@endif
		 			@endforeach
		 				<tr>
						 		<td colspan="7"></td>
						 		<td style="border-top:1px solid;">{{number_format($total,2,'.',',')}}</td>
						 		<td style="border-top:1px solid;">{{number_format($totalamort,2,'.',',')}}</td>
						 		<td style="border-top:1px solid;">{{number_format($totalsaldo,2,'.',',')}}</td>
						 		<td></td>
						 	</tr> 
						 	<tr>
						 		<td colspan="11"></td>
						 	</tr>
		 
		 	
 			</tbody>
 		

 </table>
 	</body>

 </html>
