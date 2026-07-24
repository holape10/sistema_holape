

{!! Form::model(Request::all(),['url'=>'/documentos/bajas','method'=>'GET','autocomplete'=>'off'])!!}

<!--<div class="col-lg-3">
	<div class="form-group">
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
		<div class="form-group">
			 <label class="control-label" for="fecin">Desde </label>
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
		 	<label class="control-label" for="fecfin">Hasta </label>
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>
	
	<div class="col-lg-2">
		<div class="form-group">
			<label></label>
		<span class="input-group-btn">
				<button type="submit" class="btn btn-primary btn-sm">Buscar</button>
		</span>
	</div>
	</div>
	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}