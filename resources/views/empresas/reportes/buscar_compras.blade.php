
        		<div class="box-header box-success" style="background-color:blue;">
        			<font color="white" size="3"><center><strong>REPORTE DE COMPRAS</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		 {!!Form::open(array('url'=>'/reporteventas','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
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
			<select class="form-control" name="suc_id" id="suc_id">
				
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
          <select class="form-control" name="id_almacen" id="id_almacen">
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

	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			 <label class="control-label" for="fec_ini">Desde </label>
			 <input type="date" name="fec_ini" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">
			
		</div>
	</div>
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
		 	<label class="control-label" for="fec_fin">Hasta </label>
		 	<input type="date" name="fec_fin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
		</div>
	</div>
	
    <div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo Reporte</label>
			<select name="tip_rep" class="form-control">
				<option value="1">COMPRAS</option>
				<option value="2">COMPRAS DETALLADO</option>
				<option value="3">REGISTRO DE COMPRAS SUNAT</option>
				<option value="4">RESUMEN DE COMPRAS</option>
			</select>
		</div>
	</div>

</div>
<div class="row">
	<div class="col-lg-6">
		<div class="btn-group" >
				<button type="button" id="btnBuscarCompras"   class=" btn btn-primary btn-sm">BUSCAR</button>
	
		</div>
		<div class="btn-group">
			
		
				<button type="button" id="btnExportar" dir="/generarexcelcompras" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		<div class="btn-group">
			
		
				<button type="button" id="btnGenPdf" dir="/generarpdfcompras" class="btn btn-primary btn-sm">GENERAR REPORTE</button>		</div>
	
		
	</div>
</div>



{{Form::close()}}

	           	</div>
	
