
<div class="modals modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-autorizar">

		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
                    <font color="white" size="1"><strong><center>Autorizaci&oacute;n</center></strong></font>
                </div>
				<div class="modal-body">
					<div class="row">
					<div class="col-lg-12">
                  
                            <div class="form-group form-group-sm">
                                <label>Usuarios</label>
                                <select name="usuario_aut" id="usuario_aut" class="form-control">
                                    @foreach($users as $user)
                                    <option value="{{$user->IdUsuario}}">{{$user->email}}</option>
                                    @endforeach
                                </select>
                             </div>
                        	 <div class="form-group form-group-sm">
                            	<label>Codigo de Autorizaci&oacute;n</label>
                                <input class="form-control" type="password" id="codigo_aut" name="codigo_aut" required="required">
                             </div>
                  
                </div>
            </div>
				</div>
				<div class="modal-footer">
				
                    <button type="button" name="btnAutorizar" id="btnAutorizar" class="btn btn-primary">Confirmar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>


</div>