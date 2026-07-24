
						<table id="dtHorizontalExample" style="width:50%;"  class="table table-responsive table-striped table-bordered table-sm">
						
							<thead>
								<tr>
									<th colspan="3"><center><strong>RESUMEN DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></center></th>
									
								</tr>
							
							
									<tr>
									<th colspan='3'><hr></th>
								</tr>
								<tr>
									<th style="width:25px;text-align:center;">Item</th>
									<th style="width:100px;text-align:center;">Vendedor</th>
									<th style="width:40px;text-align:center;">Total</th>
								
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($comprobantes as $cab)
									 	@php
											$i=$i+1;
										@endphp
									 	<tr>
									 		<td style="width:25px;">{{$i}}</td>
											<td style="width:70px;text-align:right;">{{$cab->name}} {{$cab->apeusu}}</td>
					
											<td style="width:60px;text-align:right;">{{$cab->total}}</td>
										</tr>
								@endforeach
									<tr>
									<th colspan='3'><hr></th>
								</tr>
								<tr>
									<td colspan='2' style="text-align:right;font-weight:bold;">TOTAL</td>
									
									<td style="text-align:right;font-weight:bold;width:60px;text-align:right;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table><br>
						


