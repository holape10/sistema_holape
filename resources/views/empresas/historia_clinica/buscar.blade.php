

{!! Form::model(Request::all(),['Route'=>'/historiaclinica','method'=>'GET','autocomplete'=>'off'])!!}


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
			<label class="control-label">Paciente</label>
			<input type="text" name="cli_num_nom" class="form-control" value="{{$cli_num_nom}}">
		
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
	
				<a href="" data-target="#modal-atencion" data-toggle="modal"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nueva Atención</button></a>
		</div>
		
			
	</div>
	</div>

	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." >

{{Form::close()}}