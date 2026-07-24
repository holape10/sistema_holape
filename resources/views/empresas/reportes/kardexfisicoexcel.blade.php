<table   >
							<thead>
								<tr style="background:#337ab7;">
									<th  colspan="10"><font color="white"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
								</tr>
								<tr>
									<th  colspan="3">PERIODO: </th>
									<th colspan="8">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</th>
								</tr>
								<tr>
									<th  colspan="3">RUC: </th>
									<th colspan="8">{{$dat_suc->IdEmpresa}}</th>
								</tr>
								<tr>
									<th  colspan="3">NOMBRE Y/O RAZON SOCIAL: </th>
									<th colspan="8">{{$dat_emp->NomEmpresa}}</th>
								</tr>
								<tr>
									<th  colspan="3">ESTABLECIMIENTO: </th>
									<th colspan="8">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</th>
								</tr>
								<tr>
									<th  colspan="3">METODO DE VALUACION: </th>
									<th colspan="8"></th>
								</tr>
								<tr style="background:#337ab7;">
									<th colspan="10"><center><font color="white"><strong>DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</strong></font></center></th>
									
								</tr>
								<tr>
									<th>FECHA</th>
									<TH>SERIE</TH>
									<TH>NUMERO</TH>
									<TH >DESCRIPCION</TH>
								  <th >LOTE</th>
									<th >VENCIMIENTO</th>
									<th >ENTRADAS</th>
									<th >SALIDAS</th>
									<th  >SALDOS</th>
									
								</tr>
							</thead>
							<tbody>
						
							
							
							@foreach($array_productos as $detalles)
								
								<tr >

									<td ><strong>CODIGO </strong></td>
									<td colspan="8" style=text-align:left;" ><strong>{{$detalles['codigo']}}</strong></td>
								</tr>
									<tr>
									<td><strong>DESCRIPCION</strong></td>
									<td colspan="8" ><strong>{{$detalles['producto']}}</strong></td>
								</tr>
									<tr>
									<td><strong>U.M</strong></td>
									<td colspan="8" ><strong>{{$detalles['unidad']}}</strong></td>
								</tr>
						
								

								@foreach($detalles['movimientos'] as $i => $mov)

									@php
									$i=0;
									$stock=0;
									$saldo=0;
									$contar = count($mov);
									@endphp

    								@for($j=0;$j<$contar;$j++)	

										    @if($mov[$j]['mov_tip']=='I')
										    @php
										            $stock = $saldo+$mov[$j]['cantidad'];
										    @endphp
										   @endif

										    @if($mov[$j]['mov_tip']=='E')
										    @php
										        $stock = $saldo-$mov[$j]['cantidad'];
										      	@endphp
										    @endif
									        	
								     
										<tr>
											<td>{{Carbon::parse($mov[$j]['fecha'])->format('d-m-Y')}}</td>
											<td>{{$mov[$j]['serie']}}</td>
											<td>{{$mov[$j]['numero']}}</td>
											<td>@if(!empty($mov[$j]['cliente'])){{$mov[$j]['cliente']}}@else{{$mov[$j]['descripcion']}}@endif</td>
												<td>{{$mov[$j]['mov_lote']}}</td>
											<td>{{$mov[$j]['mov_vencimiento']}}</td>
												@if($mov[$j]['mov_tip']=='E')
											
													<td style="text-align:right;">0.000</td>
												
													<td style="text-align:right;">{{number_format($mov[$j]['cantidad'],'3','.',',')}}</td>
												
												@elseif($mov[$j]['mov_tip']=='I')

													<td style="text-align:right;">{{number_format($mov[$j]['cantidad'],'3','.',',')}}</td>
												
												
													
													<td style="text-align:right;">0.000</td>
												@endif
											
											 	   	<td style="text-align:right;">{{number_format($stock,'2','.',',')}}</td>
												
													

										</tr>

										@php
										 	$saldo = $stock;
            							 	$i = $i+1;
										@endphp

									@endfor

								@endforeach
								

								@endforeach
								

							</tbody>
						</table><br>