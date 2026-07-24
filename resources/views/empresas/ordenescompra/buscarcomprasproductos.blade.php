

{!! Form::model(Request::all(),['Route'=>'/compras','method'=>'GET','autocomplete'=>'off'])!!}

<!--<div class="col-lg-3">
	<div class="form-group form-group-sm">
		<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR COMPROBANTES</h4>
	</div>
</div>-->
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
			
			<input type="date" name="fecin" value="{{$fecin}}" class="form-control input-sm">
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		<!--	<input for="fecfin" name="fecfin" id="fecfin" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control" type="date">-->
			<input type="date" name="fecfin" value="{{$fecfin}}" class="form-control input-sm">
			
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label>Empresa</label>
			<select class="form-control" name="sucursal">
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
	       <div class="col-lg-3">
            <div class="form-group form-group-sm">
                <label>Proveedores</label>
            	    <select name="proveedor" id="proveedor" class="form-control">
            	    		<option value="Todos">Todos</option>
	                	@foreach($proveedores as $prov)

	                		@if($prov->prov_id == $proveedor)
	                		<option selected="selected" value='{{$prov->prov_id}}'>{{$prov->prov_raz}}</option>

	                		@else
	                        <option value='{{$prov->prov_id}}'>{{$prov->prov_raz}}</option>
	                        @endif
	                    @endforeach
                    </select>
            </div>
                        
        </div>
        
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">
			<button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
		</div>
		<div class="btn-group" >
			<a href="/ordenes/crear"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nueva Orden Compra</button></a>
		</div>

		
		
	</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		<span class="input-group-btn">
				
		</span>
		</div>
	</div>
	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}