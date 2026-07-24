
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:8pt;">
						
							<thead>
								<tr>
									<th colspan="{{$cont_mp}}" style="text-align:center;background-color:#337ab7;color:#fff;"><strong>REPORTE DE VENTAS POR MEDIOS DE PAGOS DESDE {{Carbon::parse($fec_ini)->format('d-m-Y')}}  HASTA  {{Carbon::parse($fec_fin)->format('d-m-Y')}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></th>
									
								</tr>
							
							
								<tr>
									<th style="width:15;text-align:center;">Item</th>
									<th style="width:20;text-align:center;">Fecha</th>
									<th style="width:30;text-align:center;">Comprobante</th>
									<th style="width:15;text-align:center;">Serie</th>
									<th style="width:15;text-align:center;">Número</th>
									<th style="width:30;text-align:center;">RUC/DNI</th>
									<th style="width:40;text-align:center;">Cliente</th>
									
									@foreach($med_pag as $mp)
									<th style="width:70;text-align:center;">{{$mp->nom_med_pag}}</th>
									@endforeach
									<th style="width:30;text-align:center;">Total</th>
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($vent_med_pag as $cab)

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
											<td style="width:40">{{$cab->cliente}}</td>
											@foreach($med_pag as $mp)

												@php
													$bus_mont = DB::TABLE('venta_medio_pago')->where('IdCpe_cabecera',$cab->IdCpe_cabecera)->where('id_med_pag',$mp->id_med_pag)->first();
												@endphp

												@if(!empty($bus_mont))
													<td style="width:30;text-align:right;">{{$bus_mont->monto}}</td>
												@else
													<td style="width:30;text-align:right;"></td>
												@endif

												
											@endforeach
											
											<td style="width:30;text-align:right;">{{$cab->total}}</td>
										</tr>
								@endforeach
									<tr>
										<td colspan='{{$cont_mp}}'></td>
									</tr>
								<tr>
									<td colspan='{{$cont_mp-2}}' style="text-align:right;font-weight:bold;">TOTAL</td>
									
									<td style="text-align:right;font-weight:bold;width:30;text-align:right;">{{number_format($total,'2','.','')}}</td>
								</tr>
								
							</tbody>
						</table><br>
						

							<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:45%;font-size:8pt;">
						
							<thead>
								<tr>
									<th colspan="3" style="text-align:center;background-color:#337ab7;color:#fff;"><strong>RESUMEN VENTAS POR MEDIOS DE PAGOS DESDE {{Carbon::parse($fec_ini)->format('d-m-Y')}}  HASTA  {{Carbon::parse($fec_fin)->format('d-m-Y')}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></th>
									
								</tr>
							
							
								<tr>
									<th style="width:15;text-align:center;">Item</th>
									<th style="width:20;text-align:center;">Medio Pago</th>
									<th style="width:30;text-align:center;">Total</th>
									
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($res_vent_med_pag as $cab)
									 	@php
											$i=$i+1;
										@endphp
									 	<tr>
									 		<td style="width:15;text-align:center;font-weight:bold;">{{$i}}</td>
											<td style="width:30;text-align:center;">{{$cab->nom_med_pag}}</td> 
											<td style="width:15;text-align:right;">{{$cab->tot_med_pag}}</td>
										</tr>
								@endforeach
									<tr>
									<td colspan='2' style="text-align:right;font-weight:bold;">TOTAL</td>
									
									<td style="text-align:right;font-weight:bold;width:30;text-align:right;">{{number_format($total,'2','.','')}}</td>
								</tr>
							
								
							</tbody>
						</table><br>



