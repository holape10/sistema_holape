
						<table id="" style="font-size:9pt;" >
							<thead>
								<tr style="background:#337ab7;">
									<th  colspan="16"><font style="color:#ffff"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
								</tr>
								<tr>
									<th  colspan="3">PERIODO: </th>
									<th colspan="13">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</th>
								</tr>
								<tr>
									<th  colspan="3">RUC: </th>
									<th colspan="13">{{$dat_suc->IdEmpresa}}</th>
								</tr>
								<tr>
									<th  colspan="3">NOMBRE Y/O RAZON SOCIAL: </th>
									<th colspan="13">{{$dat_emp->NomEmpresa}}</th>
								</tr>
								<tr>
									<th  colspan="3">ESTABLECIMIENTO: </th>
									<th colspan="13">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</th>
								</tr>
								<tr>
									<th  colspan="3">METODO DE VALUACION: </th>
									<th colspan="13"></th>
								</tr>
								<tr style="background:#337ab7;">
									<th colspan='4'><center><font style="color:#ffff"><strong>DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</strong></font></center></th>
								
									<TH   ><font style="color:#ffff">TIPO OPERACION (12)</font></TH>
									<th colspan="2" ></th>
									<th  colspan="3" ><font style="color:#ffff">ENTRADAS</font></th>
									<th  colspan="3" ><font style="color:#ffff">SALIDAS</font></th>
									<th  colspan="3"  ><font style="color:#ffff">SALDOS</font></th>
									
								</tr>
							
							</thead>
							<thead>
									<tr>
									<th>FECHA</th>
									<th>TIPO</th>
									<TH>SERIE</TH>
									<TH>NUMERO</TH>
									<th ></th>
								    <th >LOTE</th>
									<th >VENCIMIENTO</th>
									<th >CANTIDAD</th>
									<th >COSTO UNITARIO</th>
									<th  >COSTO TOTAL</th>

									<th >CANTIDAD</th>
									<th >COSTO UNITARIO</th>
									<th  >COSTO TOTAL</th>

									<th >CANTIDAD</th>
									<th >COSTO UNITARIO</th>
									<th  >COSTO TOTAL</th>

									
								</tr>
							</thead>
								<tbody>
								

									@foreach($array_productos as $detalles)
								
								<tr >

									<td ><strong>CODIGO </strong></td>
									<td colspan="16" style="text-align:left;" ><strong>{{$detalles['codigo']}}</strong></td>
								</tr>
									<tr>
									<td><strong>DESCRIPCION</strong></td>
									<td colspan="16" style=""><strong>{{$detalles['producto']}}</strong></td>
								</tr>
									<tr>
									<td><strong>U.M</strong></td>
									<td colspan="16" style=""><strong>{{$detalles['unidad']}}</strong></td>
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
											<td >{{Carbon::parse($mov[$j]['fecha'])->format('d-m-Y')}}</td>
											<td >{{$mov[$j]['tdocod']}}</td>
											<td >{{$mov[$j]['serie']}}</td>
											<td >{{$mov[$j]['numero']}}</td>
												<td style="text-align:center;">
													{{$mov[$j]['cod_tip_ope']}}	
												</td>
												<td>{{$mov[$j]['mov_lote']}}</td>
											<td>{{$mov[$j]['mov_vencimiento']}}</td>
											@if($mov[$j]['mov_tip']=='E')
											
												<td style="text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
											
												<td style=";text-align:right;">{{number_format($mov[$j]['cantidad'],'3','.',',')}}</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{$mov[$j]['costo']}} @endif</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{number_format($mov[$j]['costo']*$mov[$j]['cantidad'],'2','.',',')}} @endif</td>

											@elseif($mov[$j]['mov_tip']=='I')

												<td style="text-align:right;">{{number_format($mov[$j]['cantidad'],'3','.',',')}}</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{$mov[$j]['costo']}} @endif</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{number_format($mov[$j]['costo']*$mov[$j]['cantidad'],'2','.',',')}} @endif</td>
												
												<td style="text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
											@endif
												
										 	   	<td style="text-align:right;">{{number_format($stock,'2','.',',')}}</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{$mov[$j]['costo']}} @endif</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{number_format($mov[$j]['costo']*$stock,'3','.',',')}} @endif</td>
											

										
										
										</tr>
										@php
										 	$saldo = $stock;
            							 	$i = $i+1;
										@endphp

									@endfor

								@endforeach
								

								@endforeach
								

							</tbody>
						</table>
	