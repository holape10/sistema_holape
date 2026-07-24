{!! Form::open(array('url'=>'/empleado','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}
<div class="row">
	<div class="col-lg-6">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Empleado </label>
			
			<input type="text" class="form-control" name="bus_emp" placeholder="Nombre o DNI" value="{{$bus_emp}}">
		</div>
	</div>
	
</div>
	<div class="row">
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

				<button type="submit" class=" btn btn-primary btn-sm">Buscar</button>
		
		
		</div>
		<div style="display:none;" class="btn-group">

				<button type="button" id="exportarempleados" class="btn btn-primary btn-sm">Exportar Empleados</button>
		
		
		</div>
		<div class="btn-group">

		<a href="/empleado/create"><button type="button" class="btn btn-success btn-sm"> Nuevo</button></a>
		
		</div>
	</div>
	</div>

	
</div>


{{Form::close()}}