
        			<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" size="3"><center><strong>PRODUCTOS (+)/(-) VENDIDOS</strong></center></font>
        		</div>
	           	<div class="box-body">

  {!!Form::open(array('url'=>'/reportecomprobantes','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
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
			 <input type="date" name="fecin" class="form-control" value="{{$fecin}}">
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="date" name="fecfin" class="form-control" value="{{$fecfin}}">
			
		</div>
	</div>
	
    <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo</label>
			<select class="form-control" name="opcion">
			
					    <option value="5">PRODUCTOS VENDIDOS</option>
					
			</select>
		</div>
	</div>
	  <div class="col-lg-2" >
		<div class="form-group form-group-sm">
			<label class="control-label">Negocios</label>
			<select class="form-control" name="sucursal" id="sucursal">
				
				@foreach($negocios as $negocio)
				@if($sucursal == $negocio->id_empresa_negocio)
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
                              <select class="form-control" name="almacen" id="almacen">
                               
                                @foreach($almacenes as $alma)
                                	@if($alma->id_almacen == $almacen)
                                   <option selected="selected" value="{{$alma->id_almacen}}">{{$alma->descripcion}}</option>
                                   @else
                                   	 <option value="{{$alma->id_almacen}}">{{$alma->descripcion}}</option>
                                   @endif
                                @endforeach
                              </select>
                            </div>
                          </div>
	  
	
</div>
<div class="row">
	<div class="col-lg-2">
		<div class="btn-group" >
				<button type="button" id="btnBuscar" class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
			<div class="btn-group">
			
		
				<button type="button" id="btnExport" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		
	
		
	</div>
</div>



{{Form::close()}}
</div>
