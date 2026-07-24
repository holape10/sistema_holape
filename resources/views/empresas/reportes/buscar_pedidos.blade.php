
        		<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" size="3"><center><strong>REPORTE PEDIDOS POR CLIENTE</strong></center></font>
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
				<option value="8">PEDIDOS</option>
				
			</select>
		
		</div>
	</div>
	
	<div class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Clientes</label>
			<select name="cliente" class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true">
				<option value="0">Todos</option>
				@foreach($clientes as $ven)
					<option value="{{$ven->clicod}}">{{$ven->clinom}} </option>
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
	