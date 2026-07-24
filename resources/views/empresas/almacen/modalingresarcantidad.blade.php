

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-2" id="modal-cantidad-precio">
		<div class="modal-dialog  modal-lg">
			<div class="modal-content" id="modal-content">
				<div class="modal-header" style="background:#337ab7;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<center><h4 class="modal-title"><font color="white"><strong>INGRESAR CANTIDAD - PRECIO</strong></font></h4></center>
				</div>
				<div class="modal-body" id="cantidad_precio">
					<div class="row">
						<div class="col-lg-8 form-group form-group-sm">
							
							<label>PRODUCTO</label>
							<input class="form-control" type="text" id="des_producto" readonly="readonly" name="des_producto">
				
						</div>
					
						<div class="col-lg-4 form-group form-group-sm">
							<label>CANTIDAD</label>
							<input class="form-control can_producto" type="text" id="can_producto"  value="1" name="can_producto">
						</div>
						<div style="display:none;" class="col-lg-6 form-group form-group-sm">
							<label>PRECIO</label>
							<input class="form-control" type="text"  id="pre_producto"  name="pre_producto">
							 @if(Auth::User()->hasRole('caja') ||    Auth::User()->hasRole('vendedor'))
							<input class="form-control" type="hidden" id="pre_producto_ref" readonly="readonly"  name="pre_producto_ref">
							
							@endif
							<input class="form-control" type="hidden" id="codigo"  name="codigo">
							<input class="form-control" type="hidden" id="id_producto"  name="id_producto">
							<input class="form-control" type="hidden" id="uni_producto"  name="uni_producto">
							<input class="form-control" type="hidden" id="lab_producto"  name="lab_producto">
							<input class="form-control" type="hidden" id="id_almacen_pro" name="id_almacen_pro">
							<input class="form-control" type="hidden" id="cod_producto" name="cod_producto">
							<input class="form-control" type="hidden"  id="icbper" name="icbper">
							<input class="form-control" type="hidden" id="mon_icbper" name="mon_icbper">
						</div>

					</div>
						
						

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="btnAgregarLista">Agregar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
</div>
