

{!! Form::model(Request::all(),['Route'=>'/gastos','method'=>'GET','autocomplete'=>'off'])!!}

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
			 <label class="control-label">Empresas </label>
			 <select class="form-control" name="sucursal">
			 	 @foreach($negocios as $negocio)
			 	 	@if($negocio->id_empresa_negocio == $sucursal)
			 	 		<option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->tipo_negocio}}</option>
			 	 	@else
			 	 		<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->tipo_negocio}}</option>
			 	 	@endif
			 	 	
			 	 @endforeach
			 </select>
		</div>
	</div>


	<div class="col-lg-2" style="display:none;">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Tipo Movimiento </label>
			 <select class="form-control" name="tipo">
			 	 	<option value="INGRESO">Ingresos</option>
			 </select>
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>
			 <input type="date" name="fecin" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
			 <input type="date" name="fecfin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
		
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
	
				<a href="/ingreso/crear"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nuevo Ingreso</button></a>
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