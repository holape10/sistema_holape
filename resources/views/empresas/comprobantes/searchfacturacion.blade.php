{{-- resources/views/empresas/comprobantes/searchfacturacion.blade.php --}}

{!!Form::open(array('url'=>'/SisFact','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}

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
			<label>Empresa</label>
			<select class="form-control" name="sucursal">
				@foreach($negocios as $negocio)
				   @if(isset($sucursal) && $negocio->id_empresa_negocio == $sucursal)
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
			<input type="date" name="fecin" class="form-control" value="{{$fecin ?? Carbon\Carbon::now()->format('Y-m-d')}}">
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="date" name="fecfin" class="form-control" value="{{$fecfin ?? Carbon\Carbon::now()->format('Y-m-d')}}">
		</div>
	</div>

	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Cliente</label>
			<input type="text" name="cliente" class="form-control" value="{{$razsoc ?? ''}}">
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Comprobante</label>
			<input type="text" name="comp" class="form-control" value="{{$documento ?? ''}}">
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
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}