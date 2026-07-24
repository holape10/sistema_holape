		
						@php
							$medios = DB::TABLE('medios_pagos')->select('id_med_pag','nom_med_pag')->orderby('id_med_pag','asc')->get();
							$contar = 10+count($medios);
							$contar1 = count($medios);
						@endphp
							<thead>
								<tr>
									<th colspan="{{$contar}}"><center><strong>RESUMEN DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></center></th>
									
								</tr>
							
							
								<tr>
									<th style="text-align:center;">Item</th>
									<th style="text-align:center;">Fecha</th>
									<th style="text-align:center;">Cod. Doc.</th>
									<th style="text-align:center;">Serie</th>
									<th style="text-align:center;">Número</th>
									<th style="text-align:center;">RUC/DNI</th>
									<th style="wtext-align:center;">Cliente</th>
									<th style="text-align:center;">Contado</th>
									<th style="text-align:center;">Crédito</th>
										@foreach($medios as $med)
										
											<th style="text-align:center;">{{$med->nom_med_pag}}</th>
									
									@endforeach
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
									 		<td style="text-align:center;">{{$i}}</td>
											<td style="wtext-align:center;">{{Carbon::parse($cab->ccafem)->format('d-m-Y')}}</td>
											<td style="text-align:center;">{{$cab->tdocod}}</td> 
											<td style="text-align:center;">{{$cab->serdoc}}</td>
											<td style="text-align:right;">{{$cab->numdoc}}</td>
											<td style="text-align:right;">{{$cab->ccandi}}</td>
											<td >{{$cab->ccanom}}</td>
											<td style="text-align:right;">{{$cab->totalcontado}}</td>
											<td style="text-align:right;">{{$cab->totalcredito}}</td>
													@foreach($medios as $med)
												@php
													$mon_mp = DB::TABLE('venta_medio_pago')->where('IdCpe_cabecera',$cab->IdCpe_cabecera)->where('id_med_pag',$med->id_med_pag)->first();
												@endphp
												
												@if(!empty($mon_mp))
													<th style="text-align:right;">{{$mon_mp->monto}}</th>
												@else
													<th style="text-align:right;">0.00</th>
												@endif
												
									
											@endforeach
											<td style="text-align:right;">{{$cab->ccaitv}}</td>asdasdasd
										</tr>
								@endforeach
									<tr>
									<th colspan='{{$contar}}'><hr></th>
								</tr>
								<tr>
									<td colspan='7' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;text-align:right;">{{number_format($totalefectivo,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;text-align:right;">{{number_format($totalcredito,'2','.',',')}}</td>
									@foreach($medios as $med)
												@php
													$mon_mp = DB::TABLE('venta_medio_pago')
													->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','venta_medio_pago.IdCpe_cabecera')
													->whereNull('ccabaj')
													  ->where('cpe_cabecera.ccafem','>=',$fecin)
                									->where('cpe_cabecera.ccafem','<=',$fecfin)
													->where('id_med_pag',$med->id_med_pag)
													   ->where(function ($query) {
												          $query->Where('cpe_cabecera.tdocod','03')
												                ->orWhere('cpe_cabecera.tdocod','01')
												                ->orWhere('cpe_cabecera.tdocod','13');
												              
												          })
													->sum('monto');
												@endphp
										
													<td style="text-align:right;">{{$mon_mp}}</td>
												
												
									
									@endforeach
									<td style="text-align:right;font-weight:bold;text-align:right;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table>
	


						


