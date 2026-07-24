

  {!!Form::open(array('url'=>'/reportecomprobantes','autocomplete'=>'off','method'=>'POST','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
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
			 <input type="text" name="fecin" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">
			
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="text" name="fecfin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
		</div>
	</div>
	<div style="display:none;" class="col-lg-3">
		<div class="form-group form-group-sm">
			<label  class="control-label">Razon Social</label>
			<input class="form-control" type="text" name="searchText" placeholder="Razón Social o Ruc">
			
		</div>
	</div>
    <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo Comprobante</label>
			<select name="docomp" class="form-control">
				<option value="1">VENTAS</option>
				<option value="2">VENTAS DETALLADO</option>
				<option value="3">UTILIDAD</option>
				<option value="4">REPORTE CONTADOR</option>
				<option value="5">COMPRAS</option>
			</select>
		
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Vendedor</label>
			<select name="vendedor" class="form-control">
				<option value="0">Todos</option>
				@foreach($vendedores as $ven)
					<option value="{{$ven->IdUsuario}}">{{$ven->name}} {{$ven->apeusu}}</option>
				@endforeach
			</select>
		
		</div>
	</div>

</div>
<div class="row">
	<div class="col-lg-2">
		<div class="btn-group" >
				<button type="submit" class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
			<div class="btn-group">
			
		
				<button type="button" id="btnExport" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		
	
		
	</div>
</div>



{{Form::close()}}
