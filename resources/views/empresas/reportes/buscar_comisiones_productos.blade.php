
        			<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" size="3"><center><strong>REPORTE DE VENTAS POR VENDEDOR</strong></center></font>
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
			
				<option value="9">COMISIONES POR PRODUCTOS</option>
			
			</select>
		
		</div>
	</div>

	     <div class="col-lg-2" >
		<div class="form-group form-group-sm">
			<label class="control-label">Productos</label>
			<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="IdProducto" id="IdProducto">
				<option value="0">Todos</option>
				@foreach($productoslista as $pro)
				@if($prod == $pro->IdProducto)
					<option selected="selected" value="{{$pro->IdProducto}}">{{$pro->pronom}} </option>
				@else
					<option value="{{$pro->IdProducto}}">{{$pro->pronom}} </option>
				@endif
				
				@endforeach
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
	