{!! Form::open(array('url'=>'/stockproductos','method'=>'GET','autocomplete'=>'off','role'=>'search','id'=>'formstock'))!!}
<div class="row">
	<div class="col-lg-2">
	<div class="form-group form-group-sm">
		<label>Empresas</label>
		<select name="sucursal" id="sucursal" class="form-control">
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
 <div class="col-lg-2" id="divalmacen">
	<div class="form-group form-group-sm">
		<label>Almacenes</label>
		<select name="almacen" id="almacen" class="form-control">
	
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

 		<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label>FECHA INVENTARIO</labeL>
                <input type="date" class="form-control" name="fecha" value="{{$fecha}}">
           </div>
        </div>  
</div>
   
<div>
	<div class="row">
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">
			<button type="button" id="buscar" onclick="buscarinventario('1');" class=" btn btn-primary btn-sm">Buscar</button>
		</div>
		<div class="btn-group" >
			<button type="button" id="inventario"  onclick="crearinventario('2');" class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nuevo Inventario</button>
		</div>
		<div class="btn-group" >
			<button type="button" id="inventario"  onclick="exportarinventario();" class=" btn btn-warning btn-sm"><span class="glyphicon glyphicon-plus"></span> Exportar Productos</button>
		</div>
		<div class="btn-group" >
		
			 <a href="" data-target="#modal-importar-inventario" data-toggle="modal"><button class="btn btn-sm btn-info">IMPORTAR INVENTARIO</button></a>
		</div>
		<div class="btn-group" >
			<button type="button" id="reporte"  onclick="exportarreporte();" class=" btn btn-primary btn-sm"><span class="glyphicon glyphicon-plus"></span> Generar Reporte</button>
		</div>
	
	</div>
	</div>

</div>
</div>                 

{{Form::close()}}