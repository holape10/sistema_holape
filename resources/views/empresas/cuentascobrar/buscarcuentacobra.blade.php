
{!! Form::open(array('url'=>'/cuentascobrar','method'=>'POST','autocomplete'=>'off','role'=>'buscar'))!!}

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
			<label class="control-label" for="fecin">Tipo Fecha</label>
			<select class="form-control" name="tipfec">
				@if($tipo =='1')
					<option value="0">Fecha Vencimiento</option>
					<option selected="selected" value="1">Fecha Emisi&oacute;n</option>
				@else
					<option selected="selected" value="0">Fecha Vencimiento</option>
				    <option value="1">Fecha Emisi&oacute;n</option>
				@endif
				
			</select>
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecin">Desde </label>
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecfin">Hasta </label>
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>

	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo Documento</label>
			<select name="tipdoc" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
				<option value="Todos">Todos</option>
				@foreach($documentos as $documento)

					<option value="{{$documento->tdocod}}">{{$documento->tdodes}}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">N° Documento</label>
			<input type="text" name="numdoc" class="form-control">
		</div>
	</div>

	<div  class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Cliente</label>
			<select name="clicod" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
				<option value="Todos">Todos</option>
				@foreach($clientes as $cliente)
					<option value="{{$cliente->clicod}}">{{$cliente->clinom}}</option>
				@endforeach
			</select>
		</div>
	</div>
		<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Estado</label>
			<select name="estado" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
				<option value="Todos">Todos</option>
				<option value="pendiente">Pendiente</option>
				<option value="Cancelado">Cancelado</option>
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

		

			
	</div>
	</div>
</div>


{{Form::close()}}
