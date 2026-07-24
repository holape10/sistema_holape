<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-series">
	<form role="form" method="post" action="{{action('ProductosController@RegistrarPresentacion')}}">
	     <input type="hidden" name="_token" value="{{ csrf_token() }}">  
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">AGREGAR PRESENTACIONES</h4>
				</div>
				<div class="modal-body">
					
					<div class="row">
						<div class='col-lg-8'>
							<label>Descripción</label>
							<input class="form-control input-sm" value='' name='descripcion' type='text'>
						</div>
						<div class='col-lg-4'>
							<label>Cantidad</label>
							<input class="form-control input-sm" value='' name='presentacion' type='text'>
						</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Agregar</button>
				</div>
			</div>
		</div>
</form>
</div>
