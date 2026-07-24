
	<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" size="3"><center><strong>REPORTE DE VENTAS POR CLIENTE</strong></center></font>
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
				<input type="text" name="fec_ini" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">

			</div>
		</div>
		<div class="col-lg-2">
			<div class="form-group form-group-sm">
				<label class="control-label" for="fec_fin">Hasta </label>
				<input type="text" name="fec_fin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
			</div>
		</div>

		<div class="col-lg-2">
			<div class="form-group form-group-sm">
				<label class="control-label">Tipo Reporte</label>
				<select name="tip_rep" class="form-control">
					<option value="1">VENTAS</option>
					<option value="2">VENTAS DETALLADO</option>
					<option value="6">RESUMEN VENTAS</option>
				</select>

			</div>
		</div>
	
	
	
		<div class="col-lg-2">
			<div class="form-group form-group-sm">
				<label class="control-label">Clientes</label>
				<select name="cli_id" class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true">
					<option value="">Todos</option>
					@foreach($clientes as $ven)
					<option value="{{$ven->clicod}}">{{$ven->clinom}} </option>
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
