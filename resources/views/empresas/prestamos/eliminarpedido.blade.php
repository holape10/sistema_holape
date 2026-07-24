
<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-delete-{{$totales->ped_id}}">

		<div class="modal-dialog">
			<div class="modal-content">
				
				<div class="modal-body">
					<div class="row">
					<div class="col-lg-12">
                    <div class="panel panel-default">
                        <div  class="panel-heading">
                            <strong>Autorizaci&oacute;n - Motivo</strong>
                        </div>
                        <div hidden="hidden" class="panel-body">
                            <div class="form-group form-group-sm">
                                <label>Usuarios</label>
                                <select name="usuario" id="usuario" class="form-control">
                                    @foreach($users as $user)
                                    <option value="{{$user->IdUsuario}}">{{$user->email}}</option>
                                    @endforeach
                                </select>
                             </div>
                        	 <div class="form-group form-group-sm">
                            	<label>Codigo de Autorizaci&oacute;n</label>
                                <input class="form-control" type="password" id="codigo" name="codigo" required="required">
                             </div>
                            <div class="form-group form-group-sm">
                            	<label>Motivo</label>
                                <textarea class="form-control" id="obser" name="obser" required="required" rows="4"></textarea>
                             </div>
                        </div>
                        <div class="modal-body">
                            <p>Confirme si desea eliminar el pedido.</p>
                        </div>
                    </div>
                  
                </div>
            </div>
				</div>
				<div class="modal-footer">
					@if(isset($mesas))
					<input type="hidden" id="mesa" value="{{$mesas->mes_id}}">
					@else
					<input type="hidden" id="mesa" value="0">
					@endif
					<input type="hidden" name="pedido" id="pedido" value="{{$totales->ped_id}}">
				

					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="button" id="btnelimped"  class="btn btn-primary btnelimped">Confirmar</button>
				</div>
			</div>
		</div>
¿
</div>