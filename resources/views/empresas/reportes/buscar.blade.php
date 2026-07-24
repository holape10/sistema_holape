

  {!!Form::open(array('url'=>'/reportecomprobantes','autocomplete'=>'off','method'=>'POST','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
<style>
	input[type=date]::-webkit-inner-spin-button, 
	input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }

</style>
<div class="row">
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>
			 <input type="date" name="fecin" class="form-control" value="{{$fecin}}">
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="date" name="fecfin" class="form-control" value="{{$fecfin}}">
			
		</div>
	</div>
	
    <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo</label>
			<select class="form-control" name="docomp">
					@if($docomp =='6')
						<option selected="selected" value="6">PRODUCTOS VENDIDOS</option>
						<option value="7">STOCK PRODUCTOS</option>
					@elseif($docomp =='7')
						<option value="6">PRODUCTOS VENDIDOS</option>
						<option selected="selected" value="7">STOCK PRODUCTOS</option>
					@else
					    <option value="6">PRODUCTOS VENDIDOS</option>
						<option value="7">STOCK PRODUCTOS</option>
					@endif
			</select>
		</div>
	</div>
	  <div class="col-lg-2" >
		<div class="form-group form-group-sm">
			<label class="control-label">Negocios</label>
			<select class="form-control" name="sucursal" id="sucursal">
				
				@foreach($negocios as $negocio)
				@if($sucursal == $negocio->id_empresa_negocio)
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
                              <select class="form-control" name="almacen" id="almacen">
                               
                                @foreach($almacenes as $alma)
                                	@if($alma->id_almacen == $almacen)
                                   <option selected="selected" value="{{$alma->id_almacen}}">{{$alma->descripcion}}</option>
                                   @else
                                   	 <option value="{{$alma->id_almacen}}">{{$alma->descripcion}}</option>
                                   @endif
                                @endforeach
                              </select>
                            </div>
                          </div>
	  <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Clientes</label>
			<select class="form-control" name="clicod">
				<option value="0">Todos</option>
				@foreach($clientes as $cliente)
				@if($clicod == $cliente->clicod)
					<option selected="selected" value="{{$cliente->clicod}}">{{$cliente->clinom}}</option>
				@else
					<option value="{{$cliente->clicod}}">{{$cliente->clinom}}</option>
				@endif
				
				@endforeach
			</select>
			</div>
	</div>
	

	<!--<div class="col-lg-1">
		<div class="form-group form-group-sm">
			<label class="control-label">Numero</label>
			{!!Form::text('numdoc',null,['class'=>'form-control input-sm','id'=>'numdoc','placeholder'=>'Número']);!!}
		</div>
	</div>-->
	<!--<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Estado</label>
			{!! Form::select('tiper',['1'=>'Todos','0'=>'Enviado y Aceptado','2'=>'En Proceso'],null,['class'=>'tiper form-control input-sm','id'=>'tiper']); !!}
		</div>
	</div>-->
</div>
<div class="row">
	<div class="col-lg-2">
		<div class="btn-group" >
				<button type="submit" class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
			<div class="btn-group">
			
		
				<button type="button" id="btnExport" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		
	
		
	</div>
</div>



{{Form::close()}}
