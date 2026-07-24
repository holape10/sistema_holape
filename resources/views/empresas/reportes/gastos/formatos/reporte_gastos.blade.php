
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:8pt;">
						
							<thead>
								<tr>
									<th colspan="9" style="text-align:center;background-color:#337ab7;color:#fff;"><strong>REPORTE DE GASTOS DESDE {{Carbon::parse($fec_ini)->format('d-m-Y')}} HASTA {{Carbon::parse($fec_fin)->format('d-m-Y')}}</strong></th>
									
								</tr>
							
							
								<tr>
									<th style="width:15;text-align:center;">Item</th>
									<th style="width:20;text-align:center;">Fecha</th>
									<th style="width:30;text-align:center;">Comprobante</th>
									<th style="width:15;text-align:center;">Serie</th>
									<th style="width:15;text-align:center;">Número</th>
									<th style="width:30;text-align:center;">RUC<br>Proveedor</th>
									<th style="width:70;text-align:center;">Proveedor</th>									
									<th style="width:30;text-align:center;">Total</th>
									<th style="width:30;text-align:center;">Observación</th>
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
									$total = 0;
								@endphp
								@foreach($gastos as $gast)
									 	@php
											$i=$i+1;
											$total = $total + $gast->total_gast;
										@endphp
									 	<tr>
									 		<td style="width:15;text-align:center;">{{$i}}</td>
											<td style="width:20;text-align:center;">{{Carbon::parse($gast->gast_fec)->format('d-m-Y')}}</td>
											<td style="width:30;text-align:center;">{{$gast->tdodes}}</td> 
											<td style="width:15;text-align:center;">{{$gast->gast_doc_ser}}</td>
											<td style="width:15;text-align:right;">{{$gast->gast_doc_num}}</td>
											<td style="width:30;text-align:right;">{{$gast->prov_ruc}}</td>
											<td style="width:70">{{$gast->prov_raz}}</td>
											<td style="width:30;text-align:right;">{{number_format($gast->total_gast,'2','.','')}}</td>
											<td style="width:30;text-align:right;">{{$gast->gast_obs}}</td>
											
										</tr>
								@endforeach
									<tr>
									<th colspan='9'><hr></th>
								</tr>
								<tr>
									<td colspan='7' style="text-align:right;font-weight:bold;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;width:30;text-align:right;">{{number_format($total,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;width:30;text-align:right;"></td>
									
								</tr>
								
							</tbody>
						</table><br>
						


