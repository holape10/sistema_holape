
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:8pt;">
						
							<thead>
								<tr>
									<th colspan="10" style="text-align:center;background-color:#337ab7;color:#fff;"><strong>REPORTE DE VENTAS DESDE {{$fec_ini}} HASTA {{$fec_fin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></th>
									
								</tr>
							
							
								<tr>
									<th style="width:15;text-align:center;">Item</th>
									<th style="width:20;text-align:center;">Fecha</th>
									<th style="width:30;text-align:center;">Comprobante</th>
									<th style="width:15;text-align:center;">Serie</th>
									<th style="width:15;text-align:center;">Número</th>
									<th style="width:30;text-align:center;">RUC/DNI</th>
									<th style="width:70;text-align:center;">Cliente</th>
									<th style="width:30;text-align:center;">Contado</th>
									<th style="width:30;text-align:center;">Crédito</th>
									<th style="width:30;text-align:center;">Total</th>
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($ventas_anuladas as $cab)
									 	@php
											$i=$i+1;
										@endphp
									 	<tr>
									 		<td style="width:15;text-align:center;">{{$i}}</td>
											<td style="width:20;text-align:center;">{{Carbon::parse($cab->fecha)->format('d-m-Y')}}</td>
											<td style="width:30;text-align:center;">{{$cab->comprobante}}</td> 
											<td style="width:15;text-align:center;">{{$cab->serie}}</td>
											<td style="width:15;text-align:right;">{{$cab->numero}}</td>
											<td style="width:30;text-align:right;">{{$cab->numerodocumento}}</td>
											<td style="width:70">{{$cab->cliente}}</td>
											<td style="width:30;text-align:right;">{{$cab->contado}}</td>
											<td style="width:30;text-align:right;">{{$cab->credito}}</td>
											<td style="width:30;text-align:right;">{{$cab->total}}</td>
										</tr>
								@endforeach
								<!--	<tr>
									<th colspan='10'><hr></th>
								</tr>
								<tr>
									<td colspan='7' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;width:30;text-align:right;">{{number_format($total_contado,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;width:30;text-align:right;">{{number_format($total_credito,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;width:30;text-align:right;">{{number_format($total_ventas,'2','.',',')}}</td>
								</tr>
								-->
							</tbody>
						</table><br>
						


