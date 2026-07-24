<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-iniciar-{{$pro->proc_comp_id}}">
	   {!!Form::open(array('url'=>'/iniciarproceso','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}

		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<font style="font-size:10pt;color:white;"><center><strong>INICIAR PROCESO</strong></center></font>
				</div>
				<div class="modal-body">
					<div class="row">
						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>OPERADOR</label>
								<select class="form-control" name="operador">
									@foreach($operadores as $oper)
									<option value="{{$oper->IdUsuario}}">{{$oper->name}} {{$oper->apeusu}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>

					<div class="row">
						@for ($i=1;$i<=$contar;$i++)
							<div class="col-lg-6">
								<div class="form-group form-group-sm">
									<label>MAQUINA {{$i}}</label>
									<select class="form-control" name="maquina[]">
										<option></option>
										@foreach($maquinas as $maq)
										<option value="{{$maq->maq_id}}">{{$maq->maq_nom}}</option>
										@endforeach
									</select>
								</div>
								
							</div>
						@endfor
					</div>
					
					<input type="hidden" name="proc_comp_id" value="{{$pro->proc_comp_id}}">
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="submit" class="btn btn-primary">Confirmar</button>
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>