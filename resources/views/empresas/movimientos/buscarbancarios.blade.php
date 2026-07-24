
{!! Form::open(array('url'=>'/movimientosbancarios','method'=>'POST','autocomplete'=>'off','role'=>'buscar'))!!}

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
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecfin">Hasta </label>
			{!!Form::date('fecfin',Carbon::now()->endOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>

	<div class="col-lg-3">
		<div class="form-group form-group-sm">
			<label class="control-label">Banco - Cuenta</label>

			<select name="cuen_ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
				@foreach($bancos as $banco)
					<option value="{{$banco->cuen_ban_id}}">{{strtoupper($banco->ban_nom)}} - CUENTA {{strtoupper($banco->tip_cuen_nom)}} {{strtoupper($banco->monnom)}} {{strtoupper($banco->cuen_ban_num)}}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="col-lg-2">
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
			<select name="est_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
				<option value="Todos">Todos</option>
				<option value="1">Validado</option>
				<option value="0">Por Validar</option>
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
	
				<a href="/movimientosbancarios/crear"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span> Nuevo Movimiento Bancario</button></a>
		</div>
		
			
	</div>
	</div>
</div>


{{Form::close()}}
