
	{!!Form::open(array('url'=>'/generarreporte/ventas','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
	{{Form::token()}}
	<div class="box-header box-success" style="background-color:#337ab7;">
		<font color="white" size="3"><center><strong>REPORTE DE VENTAS</strong></center></font>
	</div>
	<div class="box-body">


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
					<label class="control-label" for="fecin">Desde </label>
					<input type="date" name="fec_ini" value="{{Carbon::now()->startOfMonth()->format('Y-m-d')}}" class="form-control">

				</div>
			</div>
			<div class="col-lg-2">
				<div class="form-group form-group-sm">
					<label class="control-label" for="fecfin">Hasta </label>
					<input type="date" name="fec_fin" value="{{Carbon::now()->endOfMonth()->format('Y-m-d')}}" class="form-control">
				</div>
			</div>

			<div class="col-lg-2">
				<div class="form-group form-group-sm">
					<label class="control-label">Tipo Reporte </label>
					<select name="tip_rep" class="form-control">
						<option value="1">VENTAS GENERAL</option>
						<option value="2">VENTAS DETALLADO</option>
						<option value="3">VENTAS DECLARACION SUNAT</option>
						<option value="4">MIGRAR VENTAS</option>
						<option value="9">VENTAS ANULADAS</option>
						<option value="10">VENTAS ANULADAS DETALLADAS</option>
						<option value="13">VENTAS MEDIOS DE PAGOS</option>
						<option value="18">MIGRAR CONCAR</option>
				<!--<option value="51">VENTAS ANULADAS</option>
				<option value="19">REGISTRO DE VENTAS SUNAT</option>
				<option value="80">VENTAS CONSOLIDADO</option>
				<option value="17">VENTAS RESUMEN</option>-->
				
				<!--<option value="52">PEDIDOS ANULADOS</option>-->

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
	<div class="col-lg-2">
		<div hidden="hidden" class="form-group form-group-sm">
			<label class="control-label">Vendedor</label>
			<select name="vendedor" class="form-control">
				<option value="Todos">Todos</option>
				@foreach($vendedores as $ven)
				<option value="{{$ven->IdUsuario}}">{{$ven->name}} {{$ven->apeusu}}</option>
				@endforeach
			</select>

		</div>
	</div>
	<div hidden="hidden" class="col-lg-2">
		<div class="form-group form-group-sm">
			<label class="control-label">Clientes</label>
			<select name="cliente" class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true">
				<option value="Todos">Todos</option>
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
</div>
{{Form::close()}}
