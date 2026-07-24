

{!! Form::model(Request::all(),['Route'=>'/SisFact','method'=>'GET','autocomplete'=>'off'])!!}


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
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>
			<input type="date" name="fecin" class="form-control" value="{{$fec_ini}}">
	
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="date" name="fecfin" class="form-control" value="{{$fec_fin}}">
		
		</div>
	</div>

	<!--<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Tipo Comanda </label>
		 	<select class="form-control" name="ped_tip" id="ped_tip">
		 		<option value="" >Todos</option>
		 		<option value="Salon">Salon</option>
		 		<option value="Delivery">Delivery</option>
		 		<option value="Llevar">Llevar</option>
		 	</select>
		
		</div>
	</div>-->

	<div class="col-lg-2">
	    <div class="form-group form-group-sm">
	        <label class="control-label">Tipo Comanda</label>
	        <select class="form-control" name="ped_tip" id="ped_tip">
	            <option value="">Todos</option>
	            <option value="Salon" {{ $ped_tip == 'Salon' ? 'selected' : '' }}>Salon</option>
	            <option value="Delivery" {{ $ped_tip == 'Delivery' ? 'selected' : '' }}>Delivery</option>
	            <option value="Llevar" {{ $ped_tip == 'Llevar' ? 'selected' : '' }}>Llevar</option>
	        </select>
	    </div>
	</div>

	<div class="col-lg-2">
    <div class="form-group form-group-sm">
        <label class="control-label" for="ped_est">Estado</label>
        <select class="form-control" name="ped_est" id="ped_est">
            <option value="" {{ $ped_est == '' ? 'selected' : '' }}>Todos</option>
            
            <option value="Aperturado" {{ $ped_est == 'Aperturado' ? 'selected' : '' }}>Aperturado</option>
            <option value="Cerrado" {{ $ped_est == 'Cerrado' ? 'selected' : '' }}>Cerrado</option>
            <option value="Eliminado" {{ $ped_est == 'Eliminado' ? 'selected' : '' }}>Eliminado</option>
        </select>
    </div>
</div>

	<!--<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="ped_est">Estado</label>
		 		<select class="form-control" name="ped_est" id="ped_est">

		 		<option value="Aperturado">Aperturado</option>
		 		<option value="Cerrado">Cerrado</option>
		 		<option value="Eliminado">Eliminado</option>
		 	</select>
		
		</div>
	</div>-->

	
	
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

			<button type="submit" class=" btn btn-primary btn-sm">Buscar</button>

			@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin'))

			<a href="{{ url('/exportarcomandasexcel') }}?{{ http_build_query(request()->all()) }}" class="btn btn-success btn-sm">
                <i class="fa fa-file-excel-o"></i> EXCEL
            </a>

    @endif
		
		</div>
			
	</div>
	</div>

	
</div>

{{Form::close()}}