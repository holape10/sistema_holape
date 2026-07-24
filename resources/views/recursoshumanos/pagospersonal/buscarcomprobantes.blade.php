

{!! Form::model(Request::all(),['Route'=>'/gastos','method'=>'GET','autocomplete'=>'off'])!!}

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
			 <label class="control-label">Empresas </label>
			 <select class="form-control" name="sucursal">
			 	 @foreach($negocios as $negocio)
			 	 	@if($negocio->id_empresa_negocio == $sucursal)
			 	 		<option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->tipo_negocio}}</option>
			 	 	@else
			 	 		<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->tipo_negocio}}</option>
			 	 	@endif
			 	 	
			 	 @endforeach
			 </select>
		</div>
	</div>

   <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Personal</label>
                                <select name="personal" id="personal" class="form-control">
                                    <option value="0">Todos</option>
                                    @foreach($personal as $per)
                                    
                                        <option value='{{$per->IdUsuario}}'>{{$per->name}} {{$per->apeusu}}</option>
                                       
                                    @endforeach
                                </select>
                   
                            </div>
     </div>
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Tipo Movimiento </label>
			 <select class="form-control" name="tipo">
			 
			 </select>
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fecin">Desde </label>
			 <input type="date" name="fecin" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
			 <input type="date" name="fecfin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
		
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
			<a href="/gastopersonal/crear"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-plus"></span>Registrar Pago</button></a>
		</div>

		
		
	</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		<span class="input-group-btn">
				
		</span>
		</div>
	</div>
	
</div>

{{Form::close()}}