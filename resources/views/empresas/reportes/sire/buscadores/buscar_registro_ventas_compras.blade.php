@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
	
	$(document).ready(function(){

		$("#sucursal").change(function(){

			var sucursal = $("#sucursal").val();

			$("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
			$.ajax({
				type: "GET",
				dataType: 'json',
				url: "/buscaralmacen/"+sucursal,
			}).done(function(respuesta){
				$("#divalmacen").html(respuesta.vista);
			});
		});


		/*$("#btnBuscar").on("click", function() {

			var formulario = $("#frmReporte").serializeArray();
			$("#imgload").show();
			$(".botones").hide();
			$.ajax({
				type: "POST",
				dataType: 'json',
				url: '/generarsire',
				data: formulario,
			}).done(function(respuesta){
				$("#divreporte").html(respuesta.vista);
			});
		});*/

	});
</script>



<section class="content">	
	<div class="row">
		<div class="col-xs-12">
			<div class="box">	
				<div class="box-header box-success" style="background-color:#337ab7;">
					<font color="white" size="3"><center><strong>GENERAR TXT PARA EL SIRE</strong></center></font>
				</div>
				<div class="box-body">
					{!!Form::open(array('url'=>'/generarsire','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
					{{Form::token()}}

					<div class="row">
						<div class="col-lg-2" >
							<div class="form-group form-group-sm">
								<label class="control-label">Negocios</label>
								<select class="form-control" name="suc_id" id="suc_id">
									@foreach($sucursal as $negocio)
									<option value="{{$negocio->IdEmpresa}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label class="control-label" for="fecin">Desde </label>
								<input type="month" name="mes_ano" value="{{Carbon::now()->startOfMonth()->format('Y-m')}}" class="form-control">
							</div>
						</div>
						
						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label class="control-label">Código de Oportunidad Presentación</label>
								<select name="cod_oport" class="form-control">
									<option value="01">Acepta Propuesta</option>
									<option value="02">Reemplaza Propuesta</option>
									<option value="03">Realiza Ajustes Posteriores</option>
									<option value="04">Reporte de Ajustes Posteriores de Periodos Anteriores al Nuevo Sistema de Registros - Formato General</option>
									<option value="05">Reporte de Ajustes Posteriores de Periodos Anteriores al Nuevo Sistema de Registros - Formato Simplificado</option>
									<!--option value="1">VENTAS GENERAL</option>-->

								</select>
							</div>
						</div>

						<div class="col-lg-4">
							<div class="form-group form-group-sm">
								<label class="control-label">Regitros</label>
								<select name="tip_rep" class="form-control">
									<option value="1">REGISTRO DE VENTAS E INGREOS ELECTRÓNICOS (RVIE)</option>
									<!--option value="1">VENTAS GENERAL</option>-->

								</select>
							</div>
						</div>

					</div>
					<div class="row">
						<div class="col-lg-6">
							<div class="btn-group" >
								<button type="submit" id="btnBuscar" class=" btn btn-primary btn-sm">GENERAR</button>
							</div>

						</div>
						{{Form::close()}}
					</div>
				</div>
			</div>
		</div>
	</div>
		<div class="row">
			<div class="col-xs-12">
				<div class="box table table-responsive">
					<div class="box-body" id="divreporte">

					</div>	

				</div>
			</div>
		</div>

</section>




@endsection
