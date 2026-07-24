<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-productos">
	 <form method="post" action="{{url('import-productos')}}" enctype="multipart/form-data">
        {{csrf_field()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">AGREGAR PRODUCTOS</h4>
				</div>
				<div class="modal-body">
				
					<div class="row">
				
					
						<div class='col-lg-8'>
							<label>Archivo - Productos: </label>
							<input class="form-control input-sm"  name='archivo' type='file'>
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
