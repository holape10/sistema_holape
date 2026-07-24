
<div class="modals modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-modificar-{{$mesas->mes_id}}">

<form  id="formmod{{$mesas->mes_id}}" class="formularios">
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				
				<div class="modal-body">
					<div class="row">
					<div class="col-lg-12">
                    <div class="panel panel-default">
                        <div  class="panel-heading">
                            <strong>Autorizaci&oacute;n - Motivo</strong>
                        </div>
                        <div  class="panel-body">
                            <div class="form-group form-group-sm">
                                <label>Usuarios</label>
                                <select name="usuariomod" id="usuariomod{{$mesas->mes_id}}" class="form-control">
                                    @foreach($users as $user)
                                    <option value="{{$user->IdUsuario}}">{{$user->email}}</option>
                                    @endforeach
                                </select>
                             </div>
                        	 <div class="form-group form-group-sm">
                            	<label>Codigo de Autorizaci&oacute;n</label>
                                <input class="form-control" type="password" id="codigomod{{$mesas->mes_id}}" name="codigomod" required="required">
                             </div>
                           
                        </div>
                    </div>
                  
                </div>
            </div>
				</div>
				<div class="modal-footer">
					@if(isset($mesas))
					<input type="hidden" id="mesamod{{$mesas->mes_id}}" name="mesamod" value="{{$mesas->mes_id}}">
					@else
					<input type="hidden" id="mesamod" name="mesamod" value="0">
					@endif
				
                    <button type="button"  value="{{$mesas->mes_id}}" name="btnmodped" class="btn btn-primary btnmodped">Confirmar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
</form>

</div>