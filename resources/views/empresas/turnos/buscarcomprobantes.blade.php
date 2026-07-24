

{!! Form::model(Request::all(),['Route'=>'/ventasturno','method'=>'GET','autocomplete'=>'off'])!!}

<style>
	input[type=date]::-webkit-inner-spin-button, 
	input[type=date]::-webkit-clear-button,
    input[type=date]::-webkit-outer-spin-button { 
      -webkit-appearance: none; 
      margin: 0; 
    }

</style>
<div class="row">
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>

	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Cliente</label>
			<input type="text" name="cliente" value="{{$cliente}}" class="form-control" placeholder="Ruc ó Razón Social">
		
		</div>
	</div>	
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Comprobante</label>
			<input type="text" name="documento" class="form-control" value="{{$documento}}" placeholder="Serie-Número">
		
		</div>
	</div>	
	<input type="hidden" readonly="readonly" name="turno" value="{{$turno}}">
</div>
<div class="row">
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

				<button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
		
		
		</div>
		
		
			
	</div>
	</div>

	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}