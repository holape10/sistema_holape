<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-eliminar-historia-{{$comp->his_cli_id}}">
	{!!Form::open(array('url'=>'/eliminarhistoria','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmAtencion'))!!}
	{{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">Eliminar Historia Clínica</h4>
				</div>
				<div class="modal-body">
					<p>Confirme si desea Eliminar la Historía Clínica</p>
					<input type="hidden" readonly="readonly" name="id" value="{{$comp->his_cli_id}}">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Confirmar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
