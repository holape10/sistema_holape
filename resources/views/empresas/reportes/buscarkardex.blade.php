

  {!!Form::open(array('url'=>'/buscarkardex','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
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
			 <input type="date" name="fecin" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">
			
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fecfin">Hasta </label>
		 	<input type="date" name="fecfin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
		</div>
	</div>

    <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo</label>
			<select name="docomp" class="form-control">
				<option value="2">FISICO</option>
				<option value="1">VALORIZADO</option>
				
			
			</select>
		
		</div>
	</div>
	 <div class="col-lg-2" >
		<div class="form-group form-group-sm">
			<label class="control-label">Negocios</label>
			<select class="form-control" name="sucursal" id="sucursal">
				
				@foreach($negocios as $negocio)
				
				
					<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
			
				
				@endforeach
			</select>
			</div>
	</div>
	    <div class="col-lg-2" id="divalmacen">
                            <div class="form-group form-group-sm">
                              <label>Almacenes</label>
                              <select class="form-control" name="almacen" id="almacen">
                              
                                @foreach($almacenes as $alma)
                                	
                                   	 <option value="{{$alma->id_almacen}}">{{$alma->descripcion}}</option>
                                
                                @endforeach
                              </select>
                            </div>
                          </div>

          <div class="col-lg-2" >
		<div class="form-group form-group-sm">
			<label class="control-label">Productos</label>
			<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="IdProducto" id="IdProducto">
				<option value="Todos">Todos</option>
				@foreach($productoslista as $pro)
				
					
					<option value="{{$pro->IdProducto}}">{{$pro->procod}} - {{$pro->pronom}} </option>
				
				
				@endforeach
			</select>
			</div>
	</div>

</div>
<div class="row">
	<div class="col-lg-6">
		<div class="btn-group" >
				<button type="button" id='btnSubmit' dir="/buscarkardex"  class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
			<div class="btn-group">
			
		
				<button type="button" id="btnGenExcel" dir="/generarkardexexcel" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		
		<div class="btn-group">
			
		
				<button type="button" id="btnGenPdf" dir="/generarkardexpdf" class="btn btn-primary btn-sm">GENERAR KARDEX</button>	
			</div>
	
		
	</div>
</div>



{{Form::close()}}
