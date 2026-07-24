<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-deleteitem-{{$p->ped_det_id}}">

		<div class="modal-dialog">
			<div class="modal-content">
				
				<div class="modal-body">
					<div class="row">
					<div class="col-lg-12">
                    <div class="panel panel-default">
                        <div  class="panel-heading">
                            <strong>Autorizaci&oacute;n - Motivo</strong>
                        </div>
                        <div hidden="hidden"  class="panel-body">
                            <div class="form-group form-group-sm">
                                <label>Usuarios</label>
                                <select name="usuario{{$p->ped_det_id}}" id="usuario{{$p->ped_det_id}}" class="form-control">
                                    @foreach($users as $user)
                                    <option value="{{$user->IdUsuario}}">{{$user->email}}</option>
                                    @endforeach
                                </select>
                             </div>
                        	 <div class="form-group form-group-sm">
                            	<label>Codigo de Autorizaci&oacute;n</label>
                                <input class="form-control" type="password" id="codigo{{$p->ped_det_id}}" name="codigo{{$p->ped_det_id}}" required="required">
                             </div>
                            <div class="form-group form-group-sm">
                            	<label>Motivo</label>
                                <textarea class="form-control" id="obser{{$p->ped_det_id}}" name="obser{{$p->ped_det_id}}" required="required" rows="4"></textarea>
                             </div>
                        </div>
                        <div  class="modal-body">
                            <p>Confirme si desea eliminar el pedido.</p>
                        </div>
                    </div>
                  
                </div>
            </div>
				</div>
				<div class="modal-footer">

                @if(isset($mesas))
					<input type="hidden" id="mesa{{$p->ped_det_id}}" value="{{$mesas->mes_id}}">
                @endif
					<input type="hidden" name="pedido" id="pedido{{$p->ped_det_id}}" value="{{$p->ped_id}}">
					<input type="hidden" name="producto" id="producto{{$p->ped_det_id}}" value="{{$p->IdProducto}}">

					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					<button type="button" id="{{$p->ped_det_id}}"  class="btn btn-primary btnenviar">Confirmar</button>
				</div>
			</div>
		</div>
¿
</div>