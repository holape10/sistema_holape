

{!! Form::model(Request::all(),['Route'=>'/guiasremision','method'=>'GET','autocomplete'=>'off'])!!}


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
			<label class="control-label">Cliente</label>
			{!!Form::text('cliente','',['class'=>'form-control input-sm','id'=>'cliente','placeholder'=>'cliente']);!!}
		</div>
	</div>	
	<div class="col-lg-2">
		<div class="form-group">
			<label class="control-label">Comprobante</label>
			{!!Form::text('comp','',['class'=>'form-control input-sm','id'=>'comp','placeholder'=>'Serie-Número']);!!}
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
	
				<a href="/guiasremision/create"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nueva Gu&iacute;a</button></a>
		</div>
		
	
	</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group">
		<span class="input-group-btn">
				
		</span>
		</div>
	</div>
	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}