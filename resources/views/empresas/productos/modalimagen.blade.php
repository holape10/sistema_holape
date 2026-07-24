<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-imagen-{{$pro->IdProducto}}">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title"><center>{{$pro->pronom}}</center></h4>
				</div>
				<div class="modal-body">
					<div class="row">
					<center><img src="{{asset('imagenes/productos/'.$pro->imagenproducto)}}"  height="500px" width="500px" class="img-thumbnail"></center>
				</div>
				
				</div>
				<div class="modal-footer">
					
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
</div>
