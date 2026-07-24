
						<table>
						
							<thead>
								<tr>
									<th colspan="10"><center><strong>RESUMEN DE VENTAS ANULADAS DESDE {{$fecin}} HASTA {{$fecfin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></center></th>
									
								</tr>
							
							
									<tr>
									<th colspan='10'><hr></th>
								</tr>
								<tr>
									<th style="text-align:center;">Item</th>
									<th style="text-align:center;">Fecha</th>
									<th style="text-align:center;">Cod. Doc.</th>
									<th style="text-align:center;">Serie</th>
									<th style="text-align:center;">Número</th>
									<th style="text-align:center;">RUC/DNI</th>
									<th style="text-align:center;">Cliente</th>
									<th style="text-align:center;">Efectivo</th>
									<th style="wtext-align:center;">Crédito</th>
									<th style="text-align:center;">Total</th>
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($cabecera as $cab)
									 	@php
											$i=$i+1;
										@endphp
									 	<tr>
									 		<td >{{$i}}</td>
											<td style="text-align:center;">{{$cab->ccafem}}</td>
											<td style="">{{$cab->tdocod}}</td> 
											<td style="text-align:right;">{{$cab->serdoc}}</td>
											<td style="text-align:right;">{{$cab->numdoc}}</td>
											<td style="text-align:right;">{{$cab->ccandi}}</td>
											<td >{{$cab->ccanom}}</td>
											<td style="text-align:right;">{{$cab->totalcontado}}</td>
											<td style="text-align:right;">{{$cab->totalcredito}}</td>
											<td style="text-align:right;">{{number_format($cab->totalcontado+$cab->totalcredito,'2','.',',')}}</td>
										</tr>
								@endforeach
									<tr>
									<th colspan='10'><hr></th>
								</tr>
								<tr>
									<td colspan='7' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;text-align:right;">{{number_format($totalefectivo,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;text-align:right;">{{number_format($totalcredito,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;text-align:right;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table><br>
						


