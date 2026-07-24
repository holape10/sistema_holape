<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-importar-inventario">
	{!!Form::open(array('url'=>'importarinventario','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}   
		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-header" style="background:blue;">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<font color="white" size="2"><strong><center>IMPORTAR INVENTARIO</center></strong></font>
				</div>
				<div class="modal-body">

					<div class="row">
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>Empresas</label>
								<select name="sucursalimport" id="sucursalimport" class="form-control">
									@foreach($negocios as $negocio)
										@if($negocio->id_empresa_negocio == $sucursal)
											<option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
										@else
											<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
										@endif
										
									@endforeach
								</select>
							</div>
						</div>
						 <div class="col-lg-4" id="divalmacenimport">
							<div class="form-group form-group-sm">
								<label>Almacenes</label>
								<select name="almacenimport" id="almacenimport" class="form-control">
									@foreach($almacenes as $alm)
											@if($alm->id_almacen == $almacen)
												<option selected="selected" value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
											@else
												<option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
											@endif
									@endforeach
								</select>
							</div>
						</div>

					 	<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
					        <div class="form-group form-group-sm">
					            <label>FECHA INVENTARIO</label>
					            <input type="date" class="form-control" name="fechaimport" value="{{$fecha}}">
					        </div>
					    </div>  
					</div>
					   
					<div class="row">
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label>SUBIR ARCHIVO EXCEL</label>
								<input class="form-control" type="file" name="archivo" >
							</div>
						</div>
					</div>
					

				

				</div>
				<div class="modal-footer">
					<button type="submit" class="btn btn-primary">IMPORTAR</button>
					<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
					
				</div>
			</div>
		</div>
	{{Form::Close()}}
</div>
