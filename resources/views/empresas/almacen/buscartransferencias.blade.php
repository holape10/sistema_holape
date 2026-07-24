

{!! Form::model(Request::all(),['Route'=>'/transferencias','method'=>'GET','autocomplete'=>'off','id'=>'formalmacen'])!!}

<!--<div class="col-lg-3">
	<div class="form-group form-group-sm">
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
	<div class="form-group form-group-sm">
		<label>SUCURSALES</label>
		<select name="sucursal" id="sucursal" class="form-control">
			@foreach($negocios as $negocio)

				@if($negocio->id_empresa_negocio == $sucursal)
					<option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
				@else
					<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
				@endif
				
			@endforeach
		</select>
	</div>
</div>

 <div class="col-lg-2" id="divalmacen">
	<div class="form-group form-group-sm">
		<label>Almacenes</label>
		<select name="almacen" id="almacen" class="form-control">
	
			@foreach($almacenes as $alm)
					@if($alm->id_almacen == $almacen)
						<option selected="selected" value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
					@else
						<option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
					@endif
			@endforeach
		</select>
	</div>
</div>

	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>

			 @if(empty($fecfin))
				  <input type="date" name="fecin" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">
			@else
				 <input type="date" name="fecfin" value="{{$fecin}}" class="form-control">
			@endif

		
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
	
			@if(empty($fecfin))
				 <input type="date" name="fecfin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
			@else
				 <input type="date" name="fecfin" value="{{$fecfin}}" class="form-control">
			@endif
			
		</div>
	</div>
	 <div  class="col-lg-2">
	<div class="form-group form-group-sm form-group form-group-sm-sm">
		<label>Documento</label>
		<select name="tipo" id="tipo" class="form-control">
		@foreach($tipodocumentos as $tp)
			@if($tp->tdocod =='81' || $tp->tdocod=='82')
				@if($tipo == $tp->tdocod)
					<option selected="selected" value="{{$tp->tdocod}}">{{$tp->tdodes}}</option>
				@else
					<option value="{{$tp->tdocod}}">{{$tp->tdodes}}</option>
				@endif
			
			@endif
		@endforeach
		</select>
	</div>
</div>
</div>
<div class="row">
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
	<div class="form-group form-group-sm">
	
				<button type="submit" id="enviar" class="btn btn-md btn-primary">Buscar</button>

		
	</div>
</div>
</div>
                
	

{{Form::close()}}
