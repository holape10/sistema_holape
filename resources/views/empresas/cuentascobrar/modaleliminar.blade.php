<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$det->cue_cob_det_id}}">
	{!!Form::open(array('url'=>'/eliminarcuentacobrar','method'=>'POST','autocomplete'=>'off','files'=>'true','name'=>'formfact','id'=>'formfact'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">Eliminar Cuenta por Cobrar </h4>
				</div>
				<div class="modal-body">
					<p>Confirme si desea Eliminar Cuenta por Cobrar </p>
					<input type="hidden" readonly="readonly" name="id" value="{{$det->cue_cob_det_id}}">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Confirmar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
