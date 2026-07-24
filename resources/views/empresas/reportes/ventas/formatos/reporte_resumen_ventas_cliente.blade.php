
						<table id="dtHorizontalExample" style="width:100%;font-size:9pt;"  class="table table-responsive table-striped table-bordered table-sm">
						
							<thead>
								<tr>
									<th colspan="3" style="text-align:center;background-color:#337ab7;color:#fff;font-weight:bold;">RESUMEN DE VENTAS DESDE {{$fec_ini}} HASTA {{$fec_fin}} @if(!empty($dat_vend)) <br> {{$dat_vend->name}}  {{$dat_vend->apeusu}} @endif @if(!empty($dat_cli)) <br> {{$dat_cli->clinom}} @endif</th>
									
								</tr>
							
							
								
								<tr>
									<th style="width:25;text-align:center;">Item</th>
									<th style="width:100;text-align:center;">Cliente</th>
									<th style="width:40;text-align:center;">Total</th>
								
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($ventas_res_cli as $cab)
									 	@php
											$i=$i+1;
										@endphp
									 	<tr>
									 		<td style="width:25;text-align:center;font-weight:bold;">{{$i}}</td>
											<td style="width:70;text-align:left;">{{$cab->name}} {{$cab->apeusu}}</td>
					
											<td style="width:60;text-align:right;">{{$cab->total}}</td>
										</tr>
								@endforeach
									
								<tr>
									<td colspan='2' style="text-align:right;font-weight:bold;">TOTAL</td>
									
									<td style="text-align:right;font-weight:bold;width:60;text-align:right;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table>
						


