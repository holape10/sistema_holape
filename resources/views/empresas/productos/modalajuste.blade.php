<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-ajuste-{{$pro->IdProducto}}">
	
	<form method="POST" name="formAjuste{{$pro->IdProducto}}" id="formAjuste{{$pro->IdProducto}}">
    {{Form::token()}}
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<font style="font-size:14pt;" color="white"><center><strong>Ajuste de Stock  /  {{$pro->pronom}}</strong></center></font>
				</div>
				<div class="modal-body">
					<div class="row">
					<input type="hidden" name="IdProducto" value="{{$pro->IdProducto}}">
					
				
					<div hidden='hidden' class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Fecha</label>
						<input class="form-control" type="date" name="fecha" value="{{Carbon::now()->format('Y-m-d')}}">
						</div>
					</div>

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Producto</label>
						<input readonly="readonly" class="form-control" type="text" name="producto" value='{{$pro->pronom}}'>
						</div>
					</div>
				
					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>STOCK</label>
						<input class="form-control" readonly="readonly" type="text" name="stock" value='{{$pro->stock}}'>
						</div>
					</div>
			

					<div class="col-lg-4">
						<div class="form-group form-group-sm">
						<label>Cantidad</label>
						<input class="form-control" type="text" name="cantidad" value='0'>
						</div>
					</div>
					<input type="hidden" readonly="readonly" name="suc_id" value="{{$sucursal}}">
					<input type="hidden" readonly="readonly" name="alm_id" value="{{$almacen}}">
				</div>
				</div>
				<div class="modal-footer">
					<button type="button" value="{{$pro->IdProducto}}" class="btnAjuste btn btn-primary">Registrar</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
</form>
</div>
