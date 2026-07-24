
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
						
							<thead>
								<tr>
									<th colspan="10"><center><strong>RESUMEN DE VENTAS ANULADAS DESDE {{$fecin}} HASTA {{$fecfin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></center></th>
									
								</tr>
							
							
									<tr>
									<th colspan='10'><hr></th>
								</tr>
								<tr>
									<th style="width:25px;text-align:center;">Item</th>
									<th style="width:100px;text-align:center;">Fecha</th>
									<th style="width:40px;text-align:center;">Cod. Doc.</th>
									<th style="width:40px;text-align:center;">Serie</th>
									<th style="width:70px;text-align:center;">Número</th>
									<th style="width:70px;text-align:center;">RUC/DNI</th>
									<th style="width:100px;text-align:center;">Cliente</th>
									<th style="width:70px;text-align:center;">Efectivo</th>
									<th style="width:70px;text-align:center;">Crédito</th>
									<th style="width:60px;text-align:center;">Total</th>
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
									 		<td style="width:25px;">{{$i}}</td>
											<td style="width:100px;text-align:center;">{{$cab->ccafem}}</td>
											<td style="width:40px;">{{$cab->tdocod}}</td> 
											<td style="width:40px;text-align:right;">{{$cab->serdoc}}</td>
											<td style="width:70px;text-align:right;">{{$cab->numdoc}}</td>
											<td style="width:70px;text-align:right;">{{$cab->ccandi}}</td>
											<td style="width:100px">{{$cab->ccanom}}</td>
											<td style="width:70px;text-align:right;">{{$cab->totalcontado}}</td>
											<td style="width:70px;text-align:right;">{{$cab->totalcredito}}</td>
											<td style="width:60px;text-align:right;">{{number_format($cab->totalcontado+$cab->totalcredito,'2','.',',')}}</td>
										</tr>
								@endforeach
									<tr>
									<th colspan='10'><hr></th>
								</tr>
								<tr>
									<td colspan='7' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;width:70px;text-align:right;">{{number_format($totalefectivo,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;width:70px;text-align:right;">{{number_format($totalcredito,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;width:60px;text-align:right;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table><br>
						


