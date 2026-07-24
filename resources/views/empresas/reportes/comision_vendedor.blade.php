
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="4"><center><strong>REPORTE DE COMISION DESDE: {{$fecin}} HASTA {{$fecfin}} @if(!empty($dat_ven)) - Vendedor: {{$dat_ven->name}} {{$dat_ven->apeusu}} @endif @if(!empty($dat_cli)) - Cliente: {{$dat_cli->clinom}} @endif  </strong></center>
								
								</tr>
								<tr>
									<th colspan="3">TOTAL COMISION</th>
									<th>{{number_format($total,'2','.',',')}}</th>
								
								</tr>
								

								<tr>
								
									<th>CANTIDAD</th>
									<th>DESCRIPCION</th>
									<th>TOTAL</th>
									<th>COMISION</th>
								
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
									
									<td>{{number_format($comprobante->CANTIDAD,'2','.',',')}}</td>
									<td>{{$comprobante->pronom}}</td>
									<td>{{number_format($comprobante->TOTAL,'2','.',',')}}</td>
									<td>{{number_format($comprobante->COMISION,'2','.',',')}}</td>
									
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
	