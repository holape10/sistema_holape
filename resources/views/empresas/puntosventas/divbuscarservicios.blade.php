		
		<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>DETALLE DEL SERVICIO</strong></center></font>
				</div>
				<div class="box-body">
					<table class="table table-hover" id="grdet">
						<thead>

							<th>Producto</th>

							<th>Total</th>
							<th>Opciones</th>

						</thead>

						<tbody>
							@foreach($productos as $p)
							<tr>
								<td hidden="hidden"><input type="hidden" readonly="readonly" name="IdProducto[]" value="{{$p->IdProducto}}"></td>
								<td>{{$p->pronom}}</td>
								<td>{{$p->total}}</td>
								<td><a href="/modificarpedidoalber/{{$p->ped_ser_id}}"><buttton class="btn btn-sm btn-primary btn-block">Modificar</buttton></a></td>
							</tr>
								@include('empresas.puntosventas.modalmodificar')
							@endforeach
						</tbody>
					</table>

				</div>
			</div>