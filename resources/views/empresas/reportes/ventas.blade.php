
						@php
							$medios = DB::TABLE('medios_pagos')->select('id_med_pag','nom_med_pag')->orderby('id_med_pag','asc')->get();
							$contar = 10+count($medios);
							$contar1 = count($medios);
						@endphp
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
						
							<thead>
								<tr>
									<th colspan="{{$contar}}"><center><strong>RESUMEN DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} @if(!empty($dato_vendedor)) <br> {{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}} @endif</strong></center></th>
									
								</tr>
							
							
								<tr>
									<th style="width:25px;text-align:center;">Item</th>
									<th style="width:100px;text-align:center;">Fecha</th>
									<th style="width:40px;text-align:center;">Cod. Doc.</th>
									<th style="width:40px;text-align:center;">Serie</th>
									<th style="width:70px;text-align:center;">Número</th>
									<th style="width:70px;text-align:center;">RUC/DNI</th>
									<th style="width:100px;text-align:center;">Cliente</th>
									<th style="width:70px;text-align:center;">Contado</th>
									<th style="width:70px;text-align:center;">Crédito</th>
									@foreach($medios as $med)
										
											<th style="width:70px;text-align:center;">{{$med->nom_med_pag}}</th>
									
									@endforeach
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
									 		<td style="width:25px;text-align:center;">{{$i}}</td>
											<td style="width:100px;text-align:center;">{{Carbon::parse($cab->ccafem)->format('d-m-Y')}}</td>
											<td style="width:40px;text-align:center;">{{$cab->tdocod}}</td> 
											<td style="width:40px;text-align:center;">{{$cab->serdoc}}</td>
											<td style="width:70px;text-align:right;">{{$cab->numdoc}}</td>
											<td style="width:70px;text-align:right;">{{$cab->ccandi}}</td>
											<td style="width:100px">{{$cab->ccanom}}</td>
											<td style="width:70px;text-align:right;">{{$cab->totalcontado}}</td>
											<td style="width:70px;text-align:right;">{{$cab->totalcredito}}</td>
											@foreach($medios as $med)
												@php
													$mon_mp = DB::TABLE('venta_medio_pago')->where('IdCpe_cabecera',$cab->IdCpe_cabecera)->where('id_med_pag',$med->id_med_pag)->first();
												@endphp
												
												@if(!empty($mon_mp))
													<th style="width:70px;text-align:right;">{{$mon_mp->monto}}</th>
												@else
													<th style="width:70px;text-align:right;">0.00</th>
												@endif
												
									
											@endforeach
											<td style="width:60px;text-align:right;">{{$cab->ccaitv}}</td>
										</tr>
								@endforeach
									<tr>
									<th colspan='{{$contar}}'><hr></th>
								</tr>
								<tr>
									<td colspan='7' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;width:70px;text-align:right;">{{number_format($totalefectivo,'2','.',',')}}</td>
									<td style="text-align:right;font-weight:bold;width:70px;text-align:right;">{{number_format($totalcredito,'2','.',',')}}</td>
												@foreach($medios as $med)
												@php
													$mon_mp = DB::TABLE('venta_medio_pago')
													->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','venta_medio_pago.IdCpe_cabecera')
													->whereNull('ccabaj')
													  ->where('cpe_cabecera.ccafem','>=',$fecin)
                									->where('cpe_cabecera.ccafem','<=',$fecfin)
                									   ->where(function ($query) {
												          $query->Where('cpe_cabecera.tdocod','03')
												                ->orWhere('cpe_cabecera.tdocod','01')
												                ->orWhere('cpe_cabecera.tdocod','13');
												              
												          })
													->where('id_med_pag',$med->id_med_pag)->sum('monto');
												@endphp
										
													<td style="text-align:right;">{{$mon_mp}}</td>
												
												
									
									@endforeach
									<td style="text-align:right;font-weight:bold;width:60px;text-align:right;">{{number_format($total,'2','.',',')}}</td>
								</tr>
								
							</tbody>
						</table><br>
						


