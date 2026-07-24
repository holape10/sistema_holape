<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr style="background-color: #3c8dbc;color:#ffff;">
									<th colspan="2"><center><strong>REPORTE DE OBSERVACIONES DE COMANDAS DESDE: {{$fec_ini}} HASTA {{$fec_fin}}</strong></center>
									
									</tr>
								

									<tr>

										<th>PRODUCTO</th>
										<th>OBSERVACIONES</th>
									</tr>
								</thead>

								<tbody>
										@foreach($pedidos as $ped)
											@if(!empty($ped->item_obs))
											<tr>
												<td>{{$ped->descripcion}}</td>
												<td>{{$ped->item_obs}}</td>
											</tr>
											@endif
										@endforeach
								</tbody>
							</table><br>