<table id="dtHorizontalExample"  class="table table-responsive table-bordered table-sm" style="font-size:8pt;width:100%;">
							
								<tr >
									<td  colspan="15" style="text-align:center;color:#fff;font-weight:bold;background:#337ab7;">REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</td>
								</tr>
								
							
						
							
	

							@foreach($productos->chunk(10) as $chunk)
								@foreach ($chunk as $pro)
								@php
								            $movimientos = DB::TABLE('movimientos_productos')
											->where(function ($query) use ($pro){
												$query->where('IdProducto','=',$pro->IdProducto)
											     	  ->orwhere('IdProducto_rel','=',$pro->IdProducto);          
												}) 
										
											->where('id_almacen',$almacen)
											->where('fecha_mov','>=',$fecin)
											->where('fecha_mov','<=',$fecfin)
											->orderby('fecha_mov','asc')
											->orderby('mov_tip','desc')
											->orderby('tipo','asc')
											->get();
							   	 		@endphp

							   	 		@if(count($movimientos)>0)
								<tr>
									<td  colspan="5" style="font-weight:bold;">PERIODO: </td>
									<td colspan="14">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</td>
								</tr>
								<tr>
									<td  colspan="5" style="font-weight:bold;">RUC: </td>
									<td colspan="14">{{$dat_suc->IdEmpresa}}</td>
								</tr>
								<tr>
									<td  colspan="5" style="font-weight:bold;">NOMBRE Y/O RAZON SOCIAL: </td>
									<td colspan="14">{{$dat_emp->NomEmpresa}}</td>
								</tr>
								<tr>
									<td  colspan="5" style="font-weight:bold;">ESTABLECIMIENTO: </td>
									<td colspan="14">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</td>
								</tr>
								<tr>
									<td colspan="5" style="font-weight:bold;">METODO DE VALUACION: </td>
									<td colspan="14"></td>
								</tr>
								<tr>
									<td colspan="5" style="font-weight:bold;"><strong>CODIGO </strong></td>
									<td colspan="14" style="text-align:left;" ><strong>{{$pro->procod}}</strong></td>
								</tr>
								<tr>
									<td colspan="5" style="font-weight:bold;"><strong>DESCRIPCION</strong></td>
									<td colspan="14" ><strong>{{$pro->pronom}}</strong></td>
								</tr>
								<tr>
									<td colspan="5" style="font-weight:bold;"><strong>U.M</strong></td>
									<td colspan="14" ><strong>{{$pro->umecod}}</strong></td>
								</tr>
								<tr >
									<td colspan="15" style="text-align:center;color:#fff;font-weight:bold;background:#337ab7;">DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</td>	
								</tr>
										
								<tr style="color:#fff;">
									<td rowspan="2" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;">FECHA DE EMISIÓN</td>
									<Td rowspan="2" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;">TIPO</Td>
									<Td rowspan="2" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;">SERIE</Td>
									<Td rowspan="2" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;">NUMERO</Td>
									<Td rowspan="2" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;">CLIENTE</Td>
									<td rowspan="2" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;">TIPO DE OPERACIÓN</td>
									<td colspan="3"  style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;" >ENTRADAS</td>
									<td colspan="3" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;" >SALIDAS</td>
									<td colspan="3" style="background-color:#9B9B9B;color:#fff;font-weight:bold;text-align:center;" >SALDOS</td>
									
								</tr>
								<tr style="color:#fff;">
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">CANTIDAD</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">COSTO UNITARIO</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">COSTO TOTAL</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">CANTIDAD</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">COSTO UNITARIO</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">COSTO TOTAL</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">CANTIDAD</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">COSTO UNITARIO</th>
									<th style="background-color:#9B9B9B;color:#fff;font-weight:bold;width:20;">COSTO TOTAL</th>
								</tr>

										
										@foreach($movimientos->chunk(10) as $chunk1)
											@foreach ($chunk1 as $mov)
												<tr>
													<td style="text-align:center;width:20;">{{Carbon::parse($mov->fecha_mov)->format('d-m-Y')}}</td>
													<td style="text-align:center;">{{$mov->tdocod}}</td>
													<td>{{$mov->serie}}</td>
													<td>{{$mov->numero}}</td>
													<td >@if(!empty($mov->cliente)){{$mov->cliente}}@else{{$mov->descripcion}} @endif</td>
													<td style="text-align:center;">{{$mov->cod_tip_ope}}</td>
													@if($mov->mov_tip =='E')
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
													@elseif($mov->mov_tip =='I')
													<td style="text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													@endif
														
													@if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR')
													<td style="text-align:right;">{{number_format($mov->cantidad,'2','.',',')}}</td>	
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
										
													@else
													<td style="text-align:right;">{{number_format($mov->stock,'2','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
													
													@endif
												</tr>
											@endforeach
										@endforeach
												<tr>
									<td colspan="15"><hr></td>
								</tr>	
								@endif
								@endforeach
							@endforeach
							
						</table>