<table>
							<thead>
								<tr style="background:#337ab7;">
									<th  colspan="9" style="text-align:center;color:#ffff;font-weight:bold;"><font color="white"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
								</tr>
								<tr>
									<th  colspan="3">PERIODO: </th>
									<th colspan="6">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</th>
								</tr>
								<tr>
									<th  colspan="3">RUC: </th>
									<th colspan="6">{{$dat_suc->IdEmpresa}}</th>
								</tr>
								<tr>
									<th  colspan="3">NOMBRE Y/O RAZON SOCIAL: </th>
									<th colspan="6">{{$dat_emp->NomEmpresa}}</th>
								</tr>
								<tr>
									<th  colspan="3">ESTABLECIMIENTO: </th>
									<th colspan="6">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</th>
								</tr>
								<tr>
									<th  colspan="3">METODO DE VALUACION: </th>
									<th colspan="6"></th>
								</tr>
								<tr>
									<th colspan="9" style="text-align:center;color:#ffff;font-weight:bold;background:#337ab7;">DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</th>
									
								</tr>
								<tr>
									<th style="width:15;">FECHA</th>
									<TH style="width:15;">SERIE</TH>
									<TH style="width:15;">NUMERO</TH>
									<TH style="width:100;">DESCRIPCION</TH>
									<th style="width:15;">LOTE</th>
									<th style="width:15;">VENCIMIENTO</th>
									<th style="width:15;">ENTRADAS</th>
									<th style="width:15;">SALIDAS</th>
									<th  style="width:15;">SALDOS</th>
									
								</tr>
							</thead>
							<tbody>
						
							
	

							@foreach($productos->chunk(10) as $chunk)
								
								  @foreach ($chunk as $pro)
								<tr >

									<td ><strong>CODIGO </strong></td>
									<td colspan="8"  ><strong>{{$pro->procod}}</strong></td>
								</tr>
									<tr>
									<td><strong>DESCRIPCION</strong></td>
									<td colspan="8" ><strong>{{$pro->pronom}}</strong></td>
								</tr>
									<tr>
									<td><strong>U.M</strong></td>
									<td colspan="8" ><strong>{{$pro->umecod}}</strong></td>
								</tr>
						
									@php
    										$movimientos = DB::TABLE('movimientos_productos')
										      ->where(function ($query) use ($pro){
											        	
											             $query->where('IdProducto','=',$pro->IdProducto)
										     					->orwhere('IdProducto_rel','=',$pro->IdProducto);
											                
											     }) 
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
											
												<td >@if(!empty($mov->cliente)){{$mov->cliente}}@else{{$mov->descripcion}} @endif</td>
													<td>{{$mov->mov_lote}}</td>
												<td></td>
														@if($mov->mov_tip =='E')
												
														<td >0.000</td>
													
														<td >{{number_format($mov->cantidad,'3','.',',')}}</td>
													
													@elseif($mov->mov_tip =='I')

														<td >{{number_format($mov->cantidad,'3','.',',')}}</td>
														<td >0.000</td>
													
														
													@endif
														
													@if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR')
																<td >{{number_format($mov->cantidad,'2','.',',')}}</td>
																
														@else
																<td >{{number_format($mov->stock,'2','.',',')}}</td>
														

														@endif
													
														

											</tr>
									
										
											@endforeach

									@endforeach

									

									 @endforeach
								@endforeach
								

							</tbody>
						</table>