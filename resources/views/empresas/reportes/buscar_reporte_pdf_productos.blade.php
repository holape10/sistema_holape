
        		<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" style="font-size:10pt;"><center><strong>RESUMEN DE VENTAS POR PRODUCTO</strong></center></font>
        		</div>

        		
	           	<div class="box-body">

  {!!Form::open(array('url'=>'/generarreportepdfproductos','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
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
	
    <div hidden="hidden" class="col-lg-2">
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
                               <option value="Todos">Todos</option>
                                @foreach($almacenes as $alma)
                                   	<option value="{{$alma->id_almacen}}">{{$alma->descripcion}}</option>
                                @endforeach
                              </select>
                            </div>
                          </div>
	  
	
</div>
<div class="row">
	<div class="col-lg-6">
		<div class="btn-group" >
				<button type="button" id="btnBuscar" class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
			<div class="btn-group">
			
		
				<button type="button" id="btnExport" class="btn btn-primary btn-sm">EXPORTAR EXCEL</button>
		</div>
		<div class="btn-group">
			
		
				<button type="button" id="btnGenPdf" dir="/generarreportepdf" class="btn btn-primary btn-sm">GENERAR REPORTE</button>
		</div>
	
		
		
	
		
	</div>
</div>



{{Form::close()}}
</div>
