<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-whastapp-{{$comp->IdCpe_cabecera}}">
	{!!Form::open(array('url'=>'enviar_whastapp','method'=>'POST','autocomplete'=>'off','files'=>'true','target="_blank'))!!}
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">NUMERO DEL CLIENTE</h4>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-6">
							 <div class="form-group form-group-sm">
							 	 <label>Número del Cliente</label>
							 	 <input type="text" name="numero" class="form-control" value="{{$comp->telefono}}">
							 	 <input type="hidden" readonly="readonly" name="nombre" class="form-control" value="{{$comp->IdEmpresa}}-{{$comp->tdocod}}-{{$comp->serdoc}}-{{$comp->numdoc}}.pdf">
							 	 <input type="hidden" readonly="readonly" name="id"  class="form-control" value="{{$comp->IdCpe_cabecera}}">
							 </div>
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
