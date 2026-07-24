<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$pro->IdProducto}}">
	{!!Form::open(array('url'=>'/reservar','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">RESERVA {{$pro->pronom}}</h4>
				</div>
				<div class="modal-body">
					<input type="hidden" name="IdProducto" value="{{$pro->IdProducto}}">
					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Fecha</label>
						<input class="form-control" type="date" name="fechares" value="{{Carbon::now()->format('Y-m-d')}}">
						</div>
					</div>

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Producto</label>
						<input readonly="readonly" class="form-control" type="text" name="producto" value='{{$pro->pronom}}'>
						</div>
					</div>

			

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Cantidad</label>
						<input class="form-control" type="text" name="cantidaduni" value='{{$pro->stock}}'>
						</div>
					</div>

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Kilos</label>
						<input class="form-control" type="text" name="cantidad" value=''>
						</div>
					</div>

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Precio</label>
						<input  class="form-control" type="text" name="precio" value=''>
						</div>	
					</div>
				

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Confirmar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
