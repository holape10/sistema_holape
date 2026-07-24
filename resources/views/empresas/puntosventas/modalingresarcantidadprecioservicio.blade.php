

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-2" id="modal-cantidad-precio-servicio">
		<div class="modal-dialog  modal-lg">
			<div class="modal-content" id="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<center><h4 class="modal-title"><font color="white"><strong>INGRESAR CANTIDAD - PRECIO</strong></font></h4></center>
				</div>
				<div class="modal-body" id="cantidad_precio">
					<div class="row">
						<div class="col-lg-12 form-group form-group-sm">
							
							<label>PRODUCTO</label>
							<input class="form-control" type="text" id="des_productoserv" readonly="readonly" name="des_productoserv">
				
						</div>
					</div>
					<div class="row">
						<div class="col-lg-6 form-group form-group-sm">
							<label>CANTIDAD</label>
							<input class="form-control can_productoserv" type="text" id="can_productoserv"  value="1" name="can_productoserv">
						</div>
						<div class="col-lg-6 form-group form-group-sm">
							<label>PRECIO</label>
							<input class="form-control" type="text" readonly="readonly" id="pre_productoserv"  name="pre_productoserv">
							 @if(Auth::User()->hasRole('caja') ||    Auth::User()->hasRole('vendedor'))
							<input class="form-control" type="hidden" id="pre_producto_refserv" readonly="readonly"  name="pre_producto_refserv">
							
							@endif
							<input class="form-control" type="hidden" id="codigoser"  name="codigoser">
							<input class="form-control" type="hidden" id="id_productoserv"  name="id_productoserv">
							<input class="form-control" type="hidden" id="uni_productoser"  name="uni_productoser">
							<input class="form-control" type="hidden" id="lab_productoserv"  name="lab_productoserv">
							<input class="form-control" type="hidden" id="id_almacen_proserv" name="id_almacen_proserv">
							<input class="form-control" type="hidden" id="cod_productoserv" name="cod_productoserv">
						</div>
					</div>
						
						

				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-success" id="btnAgregarListaServicio">Agregar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
</div>
