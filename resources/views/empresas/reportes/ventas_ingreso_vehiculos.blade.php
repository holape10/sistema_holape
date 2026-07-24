
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="13"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}} @if(!empty($dat_ven)) - Vendedor: {{$dat_ven->name}} {{$dat_ven->apeusu}} @endif @if(!empty($dat_cli)) - Cliente: {{$dat_cli->clinom}} @endif   </strong></center>
									<th>TOTAL VENTAS </th>
								</tr>
								<tr>
									<th colspan="13"></th>
									<th> {{$total}}</th>
								
								
								</tr>

								<tr>
									
									<th>FECHA</th>
									<th>COMPROBANTE</th>
									<th>SERIE</th>
									<th>N°</th>
									<th>DOC. IDENTIDAD</th>
									<th>N° DOC. IDENTIDAD</th>
									<th style="width:210px;">CLIENTE</th>
									<th>Usu. REGISTRA</th>
									<th>USU. COBRA</th>
									<th>PLACA</th>
									<th>FEC. INGRESO</th>
									<th>FEC. SALIDA</th>
									<th>MONEDA</th>
			
									<th>VENTA TOTAL</th>
								</tr>
							</thead>
							
							<tbody>
									
									@foreach($comprobantes as $comprobante)
								<tr>
								
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$comprobante->comprobante}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<td>{{$comprobante->documentoidentidad}}</td>
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->usuario_registra}}</td>
									<td>{{$comprobante->usurio_cobra}}</td>
									<td>{{$comprobante->placa}}</td>
									<td>{{$comprobante->fecha_hora}}</td>
									<td>{{$comprobante->fecha_salida}}</td>

									<td>{{$comprobante->moneda}}</td>
							
									<td>{{$comprobante->total}}</td>
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
