@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
	
	$(document).ready(function(){

		$("#btnConsultar").click(function(){

			$("#registros").hide();
			$("#imgload").show();

		})

		$("#imgExcel").click(function(){

			//$("#registros").hide();
			//$("#imgload").show();

		})

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
					<font color="white" size="3"><center><strong>SISTEMA INTEGRADO DE REGISTROS ELECTRÓNICOS</strong></center></font>
				</div>
				<div class="box-body">
					{!!Form::open(array('url'=>'/sire/descargarpropuesta','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
					{{Form::token()}}

					<div class="row">
						<div class="col-lg-2" >
							<div class="form-group form-group-sm">
								<label class="control-label">Negocios</label>
								<select class="form-control" name="suc_id" id="suc_id">
									@foreach($sucursal as $negocio)
									<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
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
								<label class="control-label">Regitros</label>
								<select name="tip_rep" class="form-control">
									<option value="1">REGISTRO DE VENTAS E INGREOS ELECTRÓNICOS (RVIE)</option>
									<option value="2">REGISTRO DE COMPRAS</option>

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
						 <table class="table table-hover table-striped">
						 	<thead>
						 	 <tr>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Fecha<br>Solicitud</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">REGISTRO</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Periodo</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Ticket</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Archivo<br>Reporte ZIP</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Archivo<br>Contenido CSV</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Archivo<br>Contenido XLSX</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Archivo<br>Concar</th>
						 	 	<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Consultar<br>Ticket</th>
						 	 	<!--<th style="text-align:center;font-weight:bold;vertical-align:middle;color:#fff;background-color:#337ab7;">Ver<br>Registros</th>-->
						 	 </tr>
						 	</thead>
						 	 <tbody id="registros">
						 	 @if(!empty($solicitudes))
						 	 	@foreach($solicitudes as $sol)
						 	 		<tr>
						 	 			
						 	 			<td style="text-align:center;">{{Carbon::parse($sol->fecSol)->format('d-m-Y H:i:s')}}</td>
						 	 			<td style="text-align:center;">
						 	 				@if($sol->tipo =='1')
						 	 					<button type="button" class="btn btn-xs btn-block btn-primary">VENTAS</button>
						 	 				@else
						 	 					<button type="button" class="btn btn-xs btn-block btn-success">COMPRAS</button>
						 	 				@endif
						 	 			</td>
						 	 			<td style="text-align:center;">{{$sol->periodo}}</td>
						 	 			<td style="text-align:center;">{{$sol->numTicket}}</td>

						 	 			@if(!is_null($sol->archReporte))
						 	 				<td style="text-align:left;text-align:center;vertical-align:middle;"><a href="/sire/descargarzip/{{$sol->solsire_id}}"><img src="/icon/icon_zip.png" width="40px"></a></td>

							 	 			<td style="text-align:left;text-align:center;vertical-align:middle;"><a href="/sire/descargarcsv/{{$sol->solsire_id}}"><img src="/icon/icon_csv.png" width="40px"></a></td>

							 	 			@if($sol->tipo=='1')

							 	 				<td style="text-align:left;text-align:center;vertical-align: middle;"><a href="/sire/ventasexcel/{{$sol->solsire_id}}"><img src="/icon/icon_excel.png" id="imgExcel" width="40px"></a></td>
							 	 			@elseif($sol->tipo=='2')
							 	 				<td style="text-align:left;text-align:center;vertical-align: middle;"><a href="/sire/comprasexcel/{{$sol->solsire_id}}"><img src="/icon/icon_excel.png" id="imgExcel"  width="40px"></a></td>
							 	 			@endif

							 	 				@if($sol->tipo=='1')
							 	 				<td style="text-align:left;text-align:center;vertical-align: middle;"><a href="/sire/ventasconcar/{{$sol->solsire_id}}"><img src="/icon/icon_concar.png" width="40px"></a></td>
							 	 			@elseif($sol->tipo=='2')
							 	 				<td style="text-align:left;text-align:center;vertical-align: middle;"><a href="/sire/comprasconcar/{{$sol->solsire_id}}"><img src="/icon/icon_concar.png" width="40px"></a></td>
							 	 			@endif
							 	 		

						 	 			@else
						 	 				<!--<td></td>-->
						 	 				<!--<td></td>-->
						 	 				<!--<td></td>-->
						 	 				<!--<td></td>-->

						 	 			@endif
						 	 			
						 	 			

						 	 		
						 	 			<td style="text-align:center;"><a href="/sire/consultarticket/{{$sol->solsire_id}}"><button type="button" class="btn btn-sm btn-success" id="btnConsultar">Consultar</button></td>
						 	 			<!--<td style="text-align:center;"><a href="/sire/verregistros/{{$sol->solsire_id}}/{{$sol->tipo}}"><button type="button"  class="btn btn-sm btn-success">Registros</button></td>-->
						 	 		</tr>
						 	 	@endforeach
						 	 @endif
						 	</tbody>
						 	
						 </table>
						 <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
					</div>	

				</div>
			</div>
		</div>

</section>




@endsection
