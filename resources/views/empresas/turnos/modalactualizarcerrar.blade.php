<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-actualizarcerrar-{{$turno->id_turno}}">
	
	{!!Form::open(array('url'=>'/cerrarturno','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'formturno','name'=>'formturno'))!!}
    {{Form::token()}}
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header" style="background-color:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					@if($turno->estado=='Cerrado')
					<font size="3" color="white" class="modal-title"><center><strong>MODIFICAR TURNO</strong></center></font>
					<input type="hidden" name="opcion" value="1">
					<input type="hidden" name="txtTurno" value="{{$turno->id_turno}}">
					@else
					<font size="3" color="white" class="modal-title"><center><strong>CERRAR TURNO</strong></center></font>
					@endif
				</div>
				<div class="modal-body" hidden="hidden">
					<div class="row">
						@foreach($medios as $medio)
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
				            <div class="form-group form-group-sm">
				                <label for="">{{$medio->nom_med_pag}}</label>
				                <input type="hidden" name="txtMedPago[]" value="{{$medio->id_med_pag}}">
				                <input type="number" name="txtMonto[]" step="0.01" class="form-control" value="0" min='0'>
				           </div>
				        </div>
					@endforeach

					
			     
				</div>
			</div>
				<div class="modal-footer">
					@if($turno->estado=='Cerrado')
						<button type="submit" class="btn btn-primary">Modificar</button>
					@else
						<button type="submit" class="btn btn-primary">Cerrar Turno</button>
					@endif
					
					<button type="button" class="btn btn-danger" data-dismiss="modal">Salir</button>
					
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
