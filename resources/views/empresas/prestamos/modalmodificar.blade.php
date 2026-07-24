

	<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-modificar-{{$p->ped_ser_id}}">

		<div class="modal-dialog">
			<div class="modal-content">
	{!!Form::open(array('url'=>'modificarpedidoalbergue','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}} 
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">MODIFICAR PEDIDO</h4>
				</div>

				<div class="modal-body">
			
						<input type="hidden" name="IdProducto" value="{{$p->IdProducto}}">
						<input type="hidden" name="ped_ser_id" value="{{$p->ped_ser_id}}">
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>Movimiento</label>
								<select name="cmb_movimiento" class="form-control">
									<option value="Ingreso" selected="selected">AGREGAR</option>
									<option value="Salida">DISMINUIR</option>
								</select>
							</div>	
						</div>
						
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>Producto</label>
								<input readonly="readonly" class="form-control" type="text" name="producto" value='{{$p->pronom}}'>
							</div>
						</div>
				
				
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>Cantidad</label>
								<input class="form-control" type="text" name="cantidad" value='0'>
							</div>
						</div>
					</div>


				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Registrar</button>
					<button type="submit" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					
				</div>

	{!!Form::close()!!}
			</div>
		</div>

	</div>


