	<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>PEDIDOS</strong></center></font>
				</div>
				<div class="box-body">
					<table class="table table-hover">
						<thead>
							<th>FECHA</th>
							<th>SUCURSAL</th>
							<th>ALMACEN</th>
							<th>OPCIONES</th>
						</thead>
						<tbody>
							@foreach($pedidos as $p)
							
							<tr>
								<td>{{$p->fec_ped}}</td>
								<td>{{$p->tipo_negocio}}</td>
								<td>{{$p->descripcion}}</td>
								<td>{{$p->prog_cod}}</td>
								<td>
									<a href="/detallepedidoalbergue/{{$p->ped_ser_id}}"><buttton type="button" class="btn btn-sm btn-success">Detalle</buttton></a>
									 <a href="" data-toggle="modal"><buttton type="button" class="btn btn-sm btn-warning">Modificar</buttton></a> 
								<a href="/eliminarpedido/{{$p->ped_ser_id}}" data-toggle="modal"><buttton type="button" class="btn btn-sm btn-danger">Eliminar</buttton></a>

								<a href="/generarguia/{{$p->ped_ser_id}}" data-toggle="modal"><buttton type="button" class="btn btn-sm btn-primary">Generar Guía</buttton></a></td>
							</tr>
							
							@endforeach
						</tbody>
					</table>
				</div>
			</div>