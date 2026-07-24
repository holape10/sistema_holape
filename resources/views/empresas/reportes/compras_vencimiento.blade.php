
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="2"><center><strong>REPORTE DE PRODUCTOS CON FECHA DE VENCIMIENTO - COMPRAS DESDE: {{$fecin}} HASTA {{$fecfin}}</strong></center>
									
								</tr>
							
								<tr>
							
									
									<th>PRODUCTO</th>
									<th>LOTE</th>
									<th>VENCIMIENTO</th>
									
												
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
								<tr>
						
									<td>{{$comprobante->pronom}}</td>
									<td>{{$comprobante->lote}}</td>
									<td>{{$comprobante->vencimiento}}</td>
								
								</tr>
								@endforeach
							</tbody>
						</table><br>
					