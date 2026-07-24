		
		<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>DETALLE DEL SERVICIO</strong></center></font>
				</div>
				<div class="box-body">
					<table class="table table-hover" id="grdet">
						<thead>

							<th>Producto</th>

							<th>Total</th>
							<th>U.M</th>
							
						</thead>

						<tbody>
							@foreach($productos as $p)
							<tr>
								<td hidden="hidden"><input type="hidden" readonly="readonly" name="IdProducto[]" value="{{$p->IdProducto}}"></td>
								<td>{{$p->pronom}}</td>
								<td><input type="number" step="any"  name="cant[]" value="{{$p->cantidad_suma*$cantidad}}"></td>
								<td>{{$p->umenom}}</td>
								
							</tr>
					
							@endforeach
						</tbody>
					</table>

				</div>
			</div>