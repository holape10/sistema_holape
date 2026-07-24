

{!! Form::model(Request::all(),['Route'=>'/salidas','method'=>'GET','autocomplete'=>'off'])!!}

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
			<label>Empresa</label>
			<select class="form-control" name="sucursal">
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
			<input type="date" name="fecin" class="form-control" value="{{$fecin}}">
	
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="date" name="fecfin" class="form-control" value="{{$fecfin}}">
		
		</div>
	</div>

	  <div class="col-lg-3" hidden="hidden" >
              <div class="form-group">
                <label class="control-label">TIPO REPORTE</label>
                <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="tipo" id="tipo" >
                  <option value="1">DETALLADO</option>
                  <option value="2">POR PRODUCTO</option>
                 
                </select>
               
              </div>
            </div>

	    <div class="col-lg-3" >
              <div class="form-group">
                <label class="control-label">&Aacute;reas</label>
                <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="area" id="clicod" onchange="seleccionarcliente();">
                  <option></option>
                  @foreach($areas as $are)
                   @if($are->are_emp_id == $area)
                    <option selected="selected" value="{{$are->are_emp_id}}">{{$are->are_emp_des}}</option>
                    @else
  					<option value="{{$are->are_emp_id}}">{{$are->are_emp_des}}</option>
                    @endif
                  @endforeach
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
	
			
				<button type="button" id="btnExport" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
			<div class="btn-group" >
	
			
				<a href="/nuevasalida"><button type="button" class="btn btn-success btn-sm">Nueva Salida</button></a>
		</div>
			
	</div>
	</div>

	
</div>
<input type="hidden" readonly class="form-control" name="searchIdEmp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">

{{Form::close()}}