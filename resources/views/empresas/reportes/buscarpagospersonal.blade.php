
{!! Form::open(array('url'=>'/generarrptpagospersonal','method'=>'POST','autocomplete'=>'off','role'=>'buscar'))!!}

<style>
input[type=date]::-webkit-inner-spin-button, 
input[type=date]::-webkit-clear-button,
input[type=date]::-webkit-outer-spin-button { 
	-webkit-appearance: none; 
	margin: 0; 
}

</style>
<div class="row">
	
	<div class="col-lg-4">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecin">Fecha</label>
			
			{!!Form::date('fecin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecin']);!!}
		</div>
	</div>
	<div class="col-lg-4">
		<div class="form-group form-group-sm">
			<label class="control-label" for="fecfin">Fecha</label>
			
			{!!Form::date('fecfin',Carbon::now()->startOfMonth()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecfin']);!!}
		</div>
	</div>
	<div class="col-lg-4">
		<div class="form-group form-group-sm">
			<label class="control-label" for="personal">Personal</label>
			<select name="personal" class="form-control" >
				@foreach($personal as $per)
					<option value="{{$per->IdUsuario}}">{{$per->name}} {{$per->apeusu}}</option>
					
			@endforeach
			</select>
			
			
		</div>
	</div>



</div>
<div class="row">
	
	<div class="col-lg-12">
		<div class="btn-toolbar" role="toolbar" aria-label="...">
		<div class="btn-group">

				<button type="submit" class=" btn btn-primary btn-sm">GENERAR REPORTE</button>
		
		
		</div>

		
			
	</div>
	</div>
</div>


{{Form::close()}}
