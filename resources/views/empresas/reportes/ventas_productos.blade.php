
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="15"><center><strong>REPORTE DE VENTAS DETALLADAS DESDE: {{$fecin}} HASTA {{$fecfin}} @if(!empty($dat_ven)) - Vendedor: {{$dat_ven->name}} {{$dat_ven->apeusu}} @endif @if(!empty($dat_cli)) - Cliente: {{$dat_cli->clinom}} @endif  </strong></center>
								
								</tr>
								<tr>
									<th colspan="11">TOTAL</th>
									<th>{{number_format($total,'2','.',',')}}</th>
								
								</tr>
								

								<tr>
								
									<th>FECHA</th>
									<th>COMPROBANTE</th>
									<th>SERIE</th>
									<th>N°</th>
									<th>DOC. IDENTIDAD</th>
									<th>N° DOC. IDENTIDAD</th>
									<th style="width:210px;">CLIENTE</th>
									<th>PRODUCTO</th>
									<th>CANTIDAD</th>
									<th>CANT. * FACTOR</th>
									<th>PRECIO UNITARIO</th>
							
									<th>VENTA TOTAL</th>
									
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
								  <td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
									<td>{{$comprobante->comprobante}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<td>{{$comprobante->documentoidentidad}}</td>
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->producto}}</td>
									<td>{{number_format($comprobante->cantidad,'2','.',',')}}</td>
									<td>{{number_format($comprobante->cantidad*$comprobante->cpe_det_factor,'2','.',',')}}</td>
									<td>{{number_format($comprobante->precio,'2','.',',')}}</td>
									
									<td>{{number_format($comprobante->precio*$comprobante->cantidad,'2','.',',')}}</td>
									
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
	