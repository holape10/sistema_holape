
						<table  style="font-size:9pt;">
							<thead>
								<tr>
									<th colspan="6"><center><strong>RESUMEN DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}}  @endif</strong></center></th>
									
								</tr>
							
								<tr>
									<td colspan='6'><hr></td>
								</tr>
								<tr>
									<th>CODIGO</th>
									<th >PRODUCTO</th>
									<th>UNIDAD</th>
									<th>CANTIDAD</th>
									<th>PRECIO</th>
									<th>TOTAL</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td colspan='6'><hr></td>
								</tr>
								@foreach($cabecera as $cab)
									 
									 	<tr>
											<td><strong>{{$cab->ccafem}}</strong></td>
											<td colspan="4"><strong>{{$cab->tdocod}} {{$cab->serdoc}} {{$cab->numdoc}} {{$cab->ccanom}} </strong></td>
										
											@if($cab->tdocod =='07')
												<td  style="text-align:right;"><strong>{{number_format((-1)*$cab->ccaitv,'2','.',',')}}</strong></td>
											@else
												<td style="text-align:right;"><strong>{{number_format($cab->ccaitv,'2','.',',')}}</strong></td>
											@endif
											

										</tr>
									 	<tr>
									<td colspan='6'><hr></td>
								</tr>
									@foreach($comprobantes as $comprobante)
										@if($comprobante->IdCpe_cabecera == $cab->IdCpe_cabecera)
										<tr>
											<td>{{$comprobante->procod}}</td>
											<td >{{$comprobante->cdedes}}</td>
											<td>{{$comprobante->umecod}}</td>
											<td  style="text-align:right;">{{number_format($comprobante->cdecan,'2','.',',')}}</td>
											<td  style="text-align:right;">{{number_format($comprobante->cdepuni,'2','.',',')}}</td>
											@if($comprobante->tdocod =='07')
												<td  style="text-align:right;">{{number_format((-1)*$comprobante->cdevve,'2','.',',')}}</td>
											@else
												<td  style="text-align:right;">{{number_format($comprobante->cdevve,'2','.',',')}}</td>
											@endif
											
										

										</tr>
										@endif
									@endforeach
									<tr>
									<td colspan='6'><hr></td>
								</tr>
								@endforeach
								
								<tr>
									<td colspan='5' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table><br>
						


						<table >
							<thead>
								<tr>
							
									<th colspan="2" style="text-align:left;">RESUMEN VENTAS</th>
									<th >CANTIDAD</th>
									<th >TOTAL</th>
								</tr>
								<tr>
									<td colspan='4'><hr></td>
								</tr>
							</thead>
							<tbody>
								
								@foreach($productos as $producto)
								<tr>
								
									<td  colspan="2"> {{$producto->procod}} {{$producto->cdedes}}</td>
									<td  style="text-align:right">{{number_format($producto->cantidad,'2','.',',')}}</td>
									<td  style="text-align:right">{{number_format($producto->precio,'2','.',',')}}</td>
								</tr>
								@endforeach
								<tr>
									<td colspan='2' style="text-align:left;">TOTAL NOTAS DE CRÉDITO</td>
									<td  style="text-align:right;">{{number_format((-1)*$productosnotas,'2','.',',')}}</td>
									<td  style="text-align:right;">{{number_format((-1)*$totalnotas,'2','.',',')}}</td>
								</tr>
								<tr>
									<td colspan='4'><hr></td>
								</tr>
								<tr>
									<td colspan='2' style="text-align:left;"><strong>TOTAL VENTAS</strong></td>
									<th ></th>
									<td  style="text-align:right;"><strong>{{number_format($totalmontoproductos,'2','.',',')}}</strong></td>
								</tr>
								
								<tr>
									<td colspan='4'><hr></td>
								</tr>
								
							</tbody>
						</table><br>
	