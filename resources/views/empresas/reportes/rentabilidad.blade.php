
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="8"><center><strong>RENTABILIDAD DESDE: {{$fecin}} HASTA {{$fecfin}}  </strong></center>
									<th>TOTAL VENTAS</th>
									<th>TOTAL COSTO</th>
									<th colspan="2">UTILIDAD</th>
								</tr>
								<tr>
									<th colspan="8"></th>
									<th>{{number_format($totalventas,'2','.',',')}}</th>
									<th>{{number_format($totalcosto,'2','.',',')}}</th>
									<th colspan="2">{{number_format($totalventas-$totalcosto,'2','.',',')}}</th>
								</tr>
								<tr>
									<th>FECHA</th>
									<th>SERIE</th>
									<th>N°</th>
									<th style="width:210px;">CLIENTE</th>
									<th>PRODUCTO</th>
									<th>CANTIDAD</th>
									<th>PRECIO UNITARIO</th>
									<th>COSTO UNITARIO</th>
									<th>VENTA TOTAL</th>
									<th>COSTO TOTAL</th>
									<th>UTILIDAD</th>
								</tr>
							</thead>
							
							
							<tbody>
								@foreach($comprobantes as $comprobante)
							      <tr>
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->producto}}</td>
									<td>{{number_format($comprobante->cantidad,'2','.',',')}}</td>
									<td>{{number_format($comprobante->precio,'2','.',',')}}</td>
									<td>{{number_format($comprobante->costo+$comprobante->flete,'2','.',',')}}</td>
									<td>{{number_format($comprobante->precio*$comprobante->cantidad,'2','.',',')}}</td>
									<td>{{number_format(($comprobante->costo+$comprobante->flete)*$comprobante->cantidad,'2','.',',')}}</td>
									<td>{{number_format(($comprobante->precio*$comprobante->cantidad)-($comprobante->costo*$comprobante->cantidad),'2','.',',')}}</td>
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
		