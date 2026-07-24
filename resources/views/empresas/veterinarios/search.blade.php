{!! Form::open(array('url'=>'/clientes','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}
<div class="row">
	<div class="col-lg-6">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Cliente </label>
			
			<input type="text" class="form-control" name="buscli" placeholder="Razón Social ó RUC" value="{{$buscli}}">
		</div>
	</div>
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Mes Cumpleaños </label>
			<select class="form-control" name="mes_nac">
				<option></option>
				<option value="01">ENERO</option>
				<option value="02">FEBRERO</option>
				<option value="03">MARZO</option>
				<option value="04">ABRIL</option>
				<option value="05">MAYO</option>
				<option value="06">JUNIO</option>
				<option value="07">JULIO</option>
				<option value="08">AGOSTO</option>
				<option value="09">SETIEMBRE</option>
				<option value="10">OCTUBRE</option>
				<option value="11">NOVIEMBRE</option>
				<option value="12">DICIEMBRE</option>
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
		<div class="btn-group">

				<button type="button" id="exportarclientes" class="btn btn-primary btn-sm">Exportar Clientes</button>
		
		
		</div>
		<div class="btn-group">

		<a href="{{config('global.ruta')}}/clientes/create"><button type="button" class="btn btn-success btn-sm"> Nuevo</button></a>
		
		</div>
	</div>
	</div>

	
</div>


{{Form::close()}}