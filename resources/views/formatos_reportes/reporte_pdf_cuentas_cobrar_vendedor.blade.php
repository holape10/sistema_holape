 <!DOCTYPE html> 
 <html> 
 	<head>
 		
 		<style type="text/css">
 			body{
 				margin-bottom:40px;
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
 						@if(!empty($data_vendedor))VENDEDOR: {{$data_vendedor->name}} {{$data_vendedor->apeusu}}  @endif</th>
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
 						$totalgeneral=0;
 						$totalamortgeneral=0;
 						$totalsaldogeneral=0;
 					@endphp

 			@foreach($clientes as $cliente)

 			@php

 				$sum_deuda = DB::TABLE('cuentas_cobrar')
 				->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
 				->where('cuentas_cobrar.clicod',$cliente->clicod)
 				->where('IdUsuario_ven',$vendedor)
 				->sum('saldo');

 			@endphp

 			@if($sum_deuda>0)
 						<tr>
						 		<td colspan="11" style="height:7px;"></td>
						 	</tr>
 					<tr style="font-weight:bold;">
 						<td colspan="2">{{$cliente->clinum}}  </td>
 						<td colspan="5"> {{$cliente->clinom}}</td>
 						<td colspan="4">  {{$cliente->clidir}}</td>
 					</tr>

 					@php
 						$total=0;
 						$totalamort=0;
 						$totalsaldo=0;
 					@endphp

		 			@foreach($cuentas as $cuenta)
		 				@if($cliente->clicod == $cuenta->clicod)

		 					

				 		

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
		 				@endif

		 			@endforeach


		 			@if($totalsaldo>0)
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

					@endif
				 			@php

				 			

				 				$totalgeneral = $totalgeneral+$total;
		 					$totalamortgeneral = $totalamortgeneral+$totalamort;
		 					$totalsaldogeneral = $totalsaldogeneral+$totalsaldo;

				 			@endphp

				 	@endif	
		 		@endforeach
		 			
		 				 	<tr>
						 		<td colspan="11"></td>
						 	</tr>

 	<tr>
						 		<td colspan="11"></td>
						 	</tr>

		 				<tr>
						 		<td colspan="7" style="text-align:right;"><strong>TOTAL CUENTAS POR COBRAR</strong></td>
						 		<td style="border-top:1px solid;">{{number_format($totalgeneral,2,'.',',')}}</td>
						 		<td style="border-top:1px solid;">{{number_format($totalamortgeneral,2,'.',',')}}</td>
						 		<td style="border-top:1px solid;">{{number_format($totalsaldogeneral,2,'.',',')}}</td>
						 		<td></td>
						 	</tr> 
 			</tbody>
 		

 </table>
 	</body>

 </html>
