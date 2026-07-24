<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$impresora->Id}}">
	{!!Form::open(array('url'=>'/impresoras/eliminarimpresora','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">Eliminar impresora</h4>
				</div>
				<div class="modal-body">
					<p>Confirme si desea Eliminar la impresora</p>
					
					  <input type="hidden" name="Id" value="{{$impresora->Id}}">

					  <input type="hidden" name="id_empresa_negocio" value="{{$id_empresa_negocio}}">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Confirmar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>

