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
				margin-left:0px;
			margin-right:0px;
			width:95%;
			top:0px;
			width:90%;

		}

	

	


		.detalle{
			position: relative;
			margin-left:0px;
			margin-right:0px;
			width:95%;
			top:20px;
		}

		
		th, td {
			width: 100px;
			word-wrap: break-word;
		}
		




	</style>
</head>

<body  style="font-family:Helvética,sans-serif;font-size:8pt;">

	

	<div class="cabecera">
	

	<table style="width:100%;" >
		<thead>
			<tr>
				<th colspan="7" style="text-align:left;width:85%">{{$dat_emp->NomEmpresa}}</th>
				
				<th colspan="3" style="text-align:right;">Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr >
				<th colspan="7" style="text-align:left;width:85%">{{$dat_suc->direccion}}</th>
				
				<th colspan="3" style="text-align:right;">Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			<tr>
				<th colspan="7" style="text-align:left;width:85%">RUC: {{$dat_emp->IdEmpresa}}</th>
				<th colspan="3"></th>
				
			</tr>
			
		</thead>

	</table>
	
</div>

	
<br>
<div class="detalle">

					<table width="100%" >
							<thead>
								<tr style="background:#337ab7;">
									<th  colspan="10"><font color="white"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">PERIODO: </th>
									<th colspan="8">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">RUC: </th>
									<th colspan="8">{{$dat_suc->IdEmpresa}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">NOMBRE Y/O RAZON SOCIAL: </th>
									<th colspan="8">{{$dat_emp->NomEmpresa}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">ESTABLECIMIENTO: </th>
									<th colspan="8">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">METODO DE VALUACION: </th>
									<th colspan="8"></th>
								</tr>
								<tr style="background:#337ab7;">
									<th colspan="10"><center><font color="white"><strong>DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</strong></font></center></th>
									
								</tr>
								<tr>
									<th>FECHA</th>
									<TH>SERIE</TH>
									<TH>NUMERO</TH>
									<TH style="width:10%;">DESCRIPCION</TH>
									<th style="width:5%;">LOTE</th>
									<th style="width:5%;">VENCIMIENTO</th>
									<th style="width:10%;">ENTRADAS</th>
									<th style="width:10%;">SALIDAS</th>
									<th style="width:10%;" >SALDOS</th>
									
								</tr>
							</thead>
							<tbody>
						
							
	

							@foreach($productos->chunk(10) as $chunk)
								
								  @foreach ($chunk as $pro)
								<tr >

									<td ><strong>CODIGO </strong></td>
									<td colspan="10" style="width:10%;text-align:left;" ><strong>{{$pro->procod}}</strong></td>
								</tr>
									<tr>
									<td><strong>DESCRIPCION</strong></td>
									<td colspan="10" style="width:10%;"><strong>{{$pro->pronom}}</strong></td>
								</tr>
									<tr>
									<td><strong>U.M</strong></td>
									<td colspan="10" style="width:10%;"><strong>{{$pro->umecod}}</strong></td>
								</tr>
						
									@php

							            $movimientos = DB::TABLE('movimientos_productos')
							    ->where('IdProducto','=',$pro->IdProducto)
							     ->where('id_empresa_negocio',$sucursal)
							     ->where('id_almacen',$almacen)
							     ->where('fecha_mov','>=',$fecin)
							      ->where('fecha_mov','<=',$fecfin)
							    ->orderby('fecha_mov','asc')
							    ->orderby('mov_tip','desc')
							    ->orderby('tipo','asc')
							    ->get();
							    @endphp

												@foreach($movimientos->chunk(10) as $chunk1)

												 @foreach ($chunk1 as $mov)
									
									     
											<tr>
												<td>{{Carbon::parse($mov->fecha_mov)->format('d-m-Y')}}</td>
												<td>{{$mov->serie}}</td>
												<td>{{$mov->numero}}</td>
											
												<td style="width:5%;">@if(!empty($mov->cliente)){{$mov->cliente}}@else{{$mov->descripcion}} @endif</td>
													<td>{{$mov->mov_lote}}</td>
												<td></td>
														@if($mov->mov_tip =='E')
												
														<td style="width:10%;text-align:right;">0.000</td>
													
														<td style="width:10%;text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
													
													@elseif($mov->mov_tip =='I')

														<td style="width:10%;text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
														<td style="width:10%;text-align:right;">0.000</td>
													
														
													@endif
														
														@if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR')
																<td style="width:10%;text-align:right;">{{number_format($mov->cantidad,'2','.',',')}}</td>
																
														@else
																<td style="width:10%;text-align:right;">{{number_format($mov->stock,'2','.',',')}}</td>
														

														@endif
													
														

											</tr>
									
										
											@endforeach

									@endforeach

									

									 @endforeach
								@endforeach
								

							</tbody>
						</table>
	



	
</div>





		</body>
		</html>