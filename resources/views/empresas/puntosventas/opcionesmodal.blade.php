<div class="modals modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-opciones-{{$mesas->mes_id}}">

		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header" style="background-color:blue;">
					
					<font color="white" size="4" ><strong><center>OPCIONES</center></strong></font>
				</div>
				<div class="modal-body">
				 <div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    		 			<div class="form-group">

						

							<!--<a href="" data-target="#modal-modificar-{{$mesas->mes_id}}" data-toggle="modal"><button type="button" class="btn btn-primary" data-dismiss="modal">MODIFICAR</button></a>-->
						
							<a href="/modificarpedido/{{$mesas->mes_id}}"><button type="button" class="btn btn-primary">MODIFICAR</button></a>
							
							 @if(Auth::User()->hasRole('caja') || (Auth::User()->hasRole('admin')))
							<a href="/cobrarmesa/{{$mesas->mes_id}}"><button type="button" class="btn btn-success ">COBRAR</button></a>
							@endif
							<!--<a href="" data-target="#modal-delete-{{$mesas->mes_id}}" data-toggle="modal"><button type="button" class="btn btn-warning" data-dismiss="modal">ELIMINAR</button></a>-->
							<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>

							

							
						</div>
					</div>
				</div>

				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12"> 
					
				</div>
				</div>
				</div>
			</div>
		</div>
	</div>