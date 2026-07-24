<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;" >
							<thead>
								<tr>
									<th colspan="6"><center><strong>RESUMEN DE VENTAS POR PRODUCTO DESDE {{$fecin}} HASTA {{$fecfin}} </strong></center></th>
									
								</tr>
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								
								<tr>
									<th style="width:30px;">CODIGO</th>
									<th style="width:190px;">DESCRIPCION</th>
									<th style="width:30px;">UM</th>
									<th style="width:30px;">CANTIDAD</th>
									<th style="width:30px;">PRECIO</th>
									<th style="width:30px;">TOTAL</th>
								</tr>
							</thead>
							<tbody>
								@foreach($productos as $producto)
								<tr>
									<td style="width:30px;">{{$producto->procod}}</td>
									<td style="width:190px;">{{$producto->cdedes}}</td>
									<td  style="width:30px;">{{$producto->umecod}}</td>
									<td  style="text-align:right;width:30px;">{{number_format($producto->cantidad,'2','.',',')}}</td>
									<td  style="text-align:right;width:30px;">{{number_format($producto->precio/$producto->cantidad,'2','.',',')}}</td>
									<td  style="text-align:right;width:30px;">{{number_format($producto->precio,'2','.',',')}}</td>

								</tr>
								@endforeach
								<tr>
									<th colspan="6"><hr></th>
								</tr>
								
								<tr>
									<td colspan='5' style="text-align:right;width:85px;"><strong>TOTAL</strong></td>
									<td  style="text-align:right;width:30px;"><strong>{{number_format($total,'2','.',',')}}</strong></td>
								</tr>
							</tbody>
						</table><br>
				

