
        		<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" style="font-size:10pt;"><center><strong>REPORTES VENTAS POR PRODUCTO</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		

  {!!Form::open(array('url'=>'/kardex','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
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
					<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
				@endforeach
				<option value="">Todos</option>
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

    <div class="col-lg-3">
		<div class="form-group form-group-sm">
			<label class="control-label">Tipo Reporte</label>
			<select name="tip_rep" class="form-control">
				<option value="7">Resumen Ventas por Producto</option>
				<option value="8">Detallado Ventas por Producto</option>
				<option value="14">Resumen por Categoría</option>
				<option value="15">Detallado por Categoría</option>
				<option value="16">Top de productos por Categoria</option>
				<option value="17">Reporte Horas Full</option>
			</select>		
		</div>
	</div>

          <div class="col-lg-3" >
		<div class="form-group form-group-sm">
			<label class="control-label">Productos</label>
			<select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="IdProducto" id="IdProducto">
				<option value="">Todos</option>
				@foreach($productoslista as $pro)
				@if($prod == $pro->IdProducto)
					<option selected="selected" value="{{$pro->IdProducto}}">{{$pro->procod}} {{$pro->pronom}} </option>
				@else
					<option value="{{$pro->IdProducto}}">{{$pro->procod}} {{$pro->pronom}} </option>
				@endif
				
				@endforeach
			</select>
			</div>
	</div>

</div>
<div class="row">
	<div class="col-lg-6">
		<div class="btn-group" >
			<button type="button" id="btnBuscarVentas" class=" btn btn-primary btn-sm">BUSCAR</button>
		</div>
		<div class="btn-group">
			<button type="button" id="btnExportar" dir="/generarexcelventas" class="btn btn-primary btn-sm">Exportar Excel</button>
		</div>
		<div class="btn-group">
			<button type="button" id="btnGenPdf" dir="/generarpdfventas" class="btn btn-primary btn-sm">GENERAR REPORTE</button>	
		</div>
		<div class="btn-group">
			<button type="button" id="btnGenTicket" dir="/generar_reporte_ticket" class="btn btn-primary btn-sm">IMPRIMIR TICKET</button>		
		</div>
	</div>
</div>

{{Form::close()}}

	           	</div>
	        