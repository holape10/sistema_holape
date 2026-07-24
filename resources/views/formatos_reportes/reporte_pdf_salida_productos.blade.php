@if($tipo=='1')
				<table id="dtHorizontalExample" width="100%;" style="font-size:10px;">
					<thead>
						<tr>
							<th colspan="5" style="text-align:left;width:85%">{{$empresa->NomEmpresa}}</th>
						
							<th colspan="2" style="text-align:right;">Fecha: {{now()->format('d-m-Y')}}</th>
						</tr>
						<tr >
							<th colspan="5" style="text-align:left;width:85%">{{$data_sucursal->direccion}}</th>
							
							<th colspan="2" style="text-align:right;">Hora: {{now()->format('H:i:s')}}</th>
						</tr>
						<tr>
							<th colspan="5" style="text-align:left;width:85%">RUC: {{$empresa->IdEmpresa}}</th>
							<th colspan="2"></th>
							
						</tr>
						<tr>
							<th colspan="2" style="text-align:center;font-weight:bold;">REPORTE SALIDAS DE PRODUCTOS POR ÁREAS</th>
						</tr>
					</thead>

					<thead style="background:blue;color:white;">
						<tr>
							<th><center>Fec. Emision</center></th>
							<th><center>Colaborador</center></th>

							<th><center>Tipo</center></th>
							<th><center>Serie</center></th>
							<th><center>N°</center></th>
							<th><center>Producto</center></th>
							<th><center>Lote</center></th>
							<th><center>Vencimiento</center></th>
							<th><center>Cantidad</center></th>




						</tr>
					</thead>

					<tbody>
						@php
							$totalgeneral=0;
						@endphp
						@foreach($areas as $ar)
							<tr>
								<td colspan="9"><font><center><strong>{{$ar->are_emp_des}}</strong></center></font></td>
							</tr>
								@php
									$total = 0;
									$totalv = 0;
								@endphp
							@foreach($comprobantes as $comp)
								
								@if($comp->are_emp_id == $ar->are_emp_id)
									@php
										$total = $total+$comp->cdecan;
										$totalv = $totalv+($comp->cdecan*$comp->costo);
									@endphp
								<tr>
									<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->name}} {{$comp->apeusu}}</td>
									<td>{{$comp->tdodes}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									<td>{{$comp->cdedes}}</td>
									<td>{{$comp->cpe_det_lote}}</td>
									<td>{{$comp->cpe_det_venc}}</td>
										<td style="text-align:right;">{{number_format($comp->cdecan,2,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->costo,2,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->cdecan*$comp->costo,2,'.','')}}</td>
								</tr>
								@endif
								
							@endforeach
							<tr style="background:gray;color:white;">
									<td colspan="8" style="text-align:right;"><strong>TOTAL PRODUCTOS - {{$ar->are_emp_des}}</strong></td>
									<td style="text-align:right;"><strong>{{number_format($total,2,'.','')}}</strong></td>
									<td style="text-align:right;"><strong></strong></td>
									<td style="text-align:right;"><strong>{{number_format($totalv,2,'.','')}}</strong></td>
								</tr>
								@php
									$totalgeneral = $totalgeneral + $totalv;
								@endphp
						@endforeach
						
						<tr style="background:blue;color:white;">
							<td colspan="8" style="text-align:right;"><strong>TOTAL PRODUCTOS</strong></td>
								<td style="text-align:right;"><strong>{{number_format($cantidad,2,'.','')}}</strong></td>
									<td style="text-align:right;"><strong></strong></td>
							<td style="text-align:right;"><strong>{{number_format($totalgeneral,2,'.','')}}</strong></td>
						</tr>
					</tbody>
				</table><br>
				@elseif($tipo=='2')

				<table id="dtHorizontalExample" width="100%;" style="font-size:10px;">
					<thead>
						<tr>
							<th colspan="2" style="text-align:left;width:85%">{{$empresa->NomEmpresa}}</th>
						
							<th colspan="1" style="text-align:right;">Fecha: {{now()->format('d-m-Y')}}</th>
						</tr>
						<tr >
							<th colspan="2" style="text-align:left;width:85%">{{$data_sucursal->direccion}}</th>
							
							<th colspan="1" style="text-align:right;">Hora: {{now()->format('H:i:s')}}</th>
						</tr>
						<tr>
							<th colspan="2" style="text-align:left;width:85%">RUC: {{$empresa->IdEmpresa}}</th>
							<th colspan="1"></th>
							
						</tr>
						<tr>
							<th colspan="2" style="text-align:center;font-weight:bold;">REPORTE SALIDAS DE PRODUCTOS POR ÁREAS</th>
						</tr>
					</thead>
					<thead style="background:blue;color:white;">
						<tr>

							<th><center>Area</center></th>
							<th width="300px;"><center>Producto</center></th>
							<th width="100px;"><center>Cantidad</center></th>
							<th width="100px;"><center>Costo</center></th>
							<th width="100px;"><center>Total</center></th>




						</tr>
					</thead>

					@php
						$totalgeneral =0;
						@endphp
						@foreach($areas as $ar)
							<tr>
								<td colspan="7"><font><center><strong>{{$ar->are_emp_des}}</strong></center></font></td>
							</tr>
							@php
								$total = 0;
								$totalca=0;
								$totalcu = 0;
							@endphp

							@foreach($comprobantes as $comp)
								@if($comp->are_emp_id == $ar->are_emp_id)
									@php
										$total = $total+$comp->cantidad;
										$totalca = $totalca + ($comp->cantidad*($comp->costo_total/$comp->cantidad));
										$totalcu = $totalcu + ($comp->costo_total/$comp->cantidad);
									@endphp
								<tr>
									<td>{{$comp->are_emp_des}}</td>
									<td>{{$comp->cdedes}}</td>
									<td style="text-align:right;">{{number_format($comp->cantidad,2,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->costo_total/$comp->cantidad,4,'.','')}}</td>
									<td style="text-align:right;">{{number_format($comp->cantidad*($comp->costo_total/$comp->cantidad),3,'.','')}}</td>
									
								</tr>
								@endif
							@endforeach
							<tr style="background:gray;color:white;">
									<td colspan="2" style="text-align:right;"><strong>TOTAL PRODUCTOS - {{$ar->are_emp_des}}</strong></td>
									<td style="text-align:right;"><strong>{{number_format($total,2,'.','')}}</strong></td>
									<td style="text-align:right;"><strong></strong></td>
									<td style="text-align:right;"><strong>{{number_format($totalca,3,'.','')}}</strong></td>
							</tr>

							@php
							 $totalgeneral = $totalgeneral + $totalca;
							@endphp
							@endforeach
						<tr>
							<td colspan="2" style="text-align:right;"><strong>TOTAL PRODUCTOS</strong></td>
							<td style="text-align:right;"><strong>{{number_format($cantidad,2,'.','')}}</strong></td>
							<td style="text-align:right;"><strong></strong></td>
							<td style="text-align:right;"><strong>{{number_format($totalgeneral,2,'.','')}}</strong></td>
						</tr>
				</table><br>

				@endif