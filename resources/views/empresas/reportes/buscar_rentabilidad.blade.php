

        			<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" size="3"><center><strong>RENTABILIDAD</strong></center></font>
        		</div>

	           	<div class="box-body">
	           		 {!!Form::open(array('url'=>'/generarreporte/ventas','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
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
	
   <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo Reporte</label>
			<select name="opcion" class="form-control">
			
				<option value="11">RENTABILIDAD</option>
				<option value="12">RENTABILIDAD - COSTO FIJO</option>
	
			</select>
		</div>
	</div>
	
	    <div hidden="hidden" class="col-lg-2" id="divalmacen">
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
	<div class="col-lg-5">
		<div class="btn-group" >
			<button type="button" id="btnBuscar" class=" btn btn-primary btn-sm">BUSCAR</button>
		</div>
		<div class="btn-group">
			<button type="button" id="btnExport" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		<div class="btn-group">
			<button type="button" id="btnGenPdf" dir="/generarreportepdf" class="btn btn-primary btn-sm">GENERAR REPORTE</button>
		</div>
	</div>
</div>



{{Form::close()}}

	           	</div>
	
