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
							 @if(($archivo = fopen($rutaArchivo, "r")) ==! false)

							 	<table class="table table-hover table-striped">
							 		@php
							 			$i=0;

							 		@endphp
							 		@while(($datos = fgetcsv($archivo, 1000, ",")) !== false)

							 			@php
							 				$j=0;
							 				$serie=0;
							 				$numero=0;	
							 			@endphp

							 			@if($i==0)
							 				<tr>
								 				@foreach($datos as $key => $dato)

								 					<td style="background-color:#337ab7;color:#fff">{{$dato}}</td>

								 				@endforeach
								 			</tr>
							 			@else
							 				<tr>
								 				@foreach($datos as $key => $dato)
								 				    
							 		
								 				    @if($j==7)
								 				    	@php
								 					
								 		@endphp
											 			@php
												 			$serie = $dato;
												 		@endphp	
											 		@elseif($j==8)
											 			@php
												 			$numero = $dato;

												 		@endphp	
											 		@endif

								 					<td>{{$dato}}</td>
								 					@php
											 			$j=$j+1;
											 		@endphp
								 				@endforeach
								 			</tr>
							 			@endif
							 			@php

							 				if($i>0){
							 					DB::TABLE('cpe_cabecera')->where('serdoc',$serie)->where('numdoc',$numero)->update(['sire'=>'1']);
							 				}
							 			
								 			$i=$i+1;

								 			
								 		@endphp
							 		@endwhile
							 	</table>
							 @endif
					</div>	

				</div>
			</div>
		</div>

</section>




@endsection


