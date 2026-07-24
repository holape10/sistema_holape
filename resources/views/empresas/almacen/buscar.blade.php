

{!! Form::model(Request::all(),['Route'=>'/compras','method'=>'GET','autocomplete'=>'off','id'=>'formalmacen'])!!}

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
		<label>Empresas</label>
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
		<!--	<input for="fecfin" name="fecfin" id="fecfin" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control" type="date">-->

			@if(empty($fecfin))
				 <input type="date" name="fecfin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
			@else
				 <input type="date" name="fecfin" value="{{$fecfin}}" class="form-control">
			@endif
			
		</div>
	</div>
	 <div hidden="hidden" class="col-lg-2">
	<div class="form-group form-group-sm form-group form-group-sm-sm">
		<label>Tipo Producto</label>
		<select name="promocion" id="promocion" class="form-control">
			@if($tipo=='Todos')
					<option selected="selected" value="Todos">Todos</option>
			@else
					<option value="Todos">Todos</option>
			@endif
		
			@foreach($tipos_productos as $tipos)
			@if($tipo == $tipos->tip_prod_cod)
			<option selected="selected" value="{{$tipos->tip_prod_cod}}">{{$tipos->tip_prod_nom}}</option>
			@else
			<option value="{{$tipos->tip_prod_cod}}">{{$tipos->tip_prod_nom}}</option>
			@endif
			
			@endforeach
		</select>
	</div>
</div>
                
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
	<div class="form-group form-group-sm form-group form-group-sm-sm">
		<label>Descripci&oacute;n</label>
		<div class="input-group">
			<input type="text" class="form-control" name="buspro" id="buspro" placeholder="Nombre o Código del producto" value="{{$buspro}}">

			<span class="input-group-btn">
				<button type="submit" id="enviar" class="btn btn-sm btn-primary">Buscar</button>
			</span>
		</div>
	</div>
</div>
	
</div>


<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}
