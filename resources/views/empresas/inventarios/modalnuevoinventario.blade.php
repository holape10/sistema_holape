<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-nuevo-inventario">
	{!!Form::open(array('url'=>'nuevoinventario','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}   
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<font color="white" size="2"><strong><center>NUEVO INVENTARIO</center></strong></font>
				</div>
				<div class="modal-body">

					<div class="row">
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>Empresas</label>
								<select name="suc_nue_inv" id="suc_nue_inv" class="form-control">
									@foreach($negocios as $negocio)
								
											<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
										
									@endforeach
								</select>
							</div>
						</div>
						 <div class="col-lg-4" id="divalmnueinv">
							<div class="form-group form-group-sm">
								<label>Almacenes</label>
								<select name="alm_nue_inv" id="alm_nue_inv" class="form-control">
									@foreach($almacenes as $alm)
										<option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
									@endforeach
								</select>
							</div>
						</div>

					 	<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					        <div class="form-group form-group-sm">
					            <label>FECHA INVENTARIO</label>
					            <input type="date" class="form-control" name="fecha_nue_inv" value="{{now()->format('Y-m-d')}}">
					        </div>
					    </div>  
					</div>
					   
				
					

				

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">Generar</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
