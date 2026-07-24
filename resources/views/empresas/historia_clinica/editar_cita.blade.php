@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
	
	$(document).ready(function(){


		$("#btnRegistrar").on("click", function() {


			var formulario = $("#frmAtencion").serializeArray();
			$("#imgload").show();
			$(".botones").hide();
			$.ajax({
				type: "POST",
				dataType: 'json',
				url: '/actualizarcita',
				data: formulario,
			}).done(function(respuesta){


				if(respuesta.estado =='error'){

					alert(respuesta.mensaje);

					$("#imgload").hide();
					$(".botones").show();

				}else{


					window.location.href = "/historiaclinica";


				}



			});

		});

	})

	function  buscarcliente(){


		var formulario = $("#clinum").val();
		$("#imgloadcliente").show();

		$.ajax({
			type: "get",
			dataType: 'json',
			url: '/autocomplete/'+formulario,

		}).done(function(respuesta){



			if(respuesta.error){




				alert(respuesta.error);
				$("#imgloadcliente").hide();



			}else{

				$('#clinom').val(respuesta[0].nom);
				$('#clidir').val(respuesta[0].dir);
				$('#clitel').val(respuesta[0].telefono);
				$('#clicor').val(respuesta[0].cor);
				$('#clicod').val(respuesta[0].clicod);
				$("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');
				$("#sex_id").val(respuesta[0].sex_id).attr('selected', 'selected');
				$("#est_civ_id").val(respuesta[0].est_civ_id).attr('selected', 'selected');

				$("#imgloadcliente").hide();
				$(".botones").show(); 

			}


		});



	}


</script>
<section class="content">
		{!!Form::open(array('url'=>'/','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmAtencion'))!!}
	{{Form::token()}}
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>CONSULTA MÉDICA<br><br> Historia Clínica N° {{$data_historia->his_cli_cod}} - {{$data_historia->clinom}}</strong></center></font>
				</div>
				<div class="box-body">
					<input type="hidden" readonly="readonly" name="id" value="{{$id}}">
					
					<div class="row">
					<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="tdicod">Tipo Documento Ident.</label>
							<select class="form-control"  name="tdicod" id="tdicod">
								<option></option>
								@foreach($documentos_identidad as $doc)
									@if($doc->tdicod == $historias->tdicod)
										<option selected="selected" value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
									@else
										<option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
									@endif
								
								@endforeach
							</select>
							
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="clinum">Número Documento Ident.</label>
							 <div class="input-group input-group-sm">
				                  	<input type="text" name="clinum" id="clinum" readonly="readonly" value="{{$historias->clinum}}" onKeypress="if(event.keyCode == 13) buscarcliente();" class="form-control" >
				                    <span class="input-group-btn">
				                      <button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="buscarcliente();"><span class="fa fa-search"></span></button>
				                    </span>
				              </div>

						</div>
					</div>

					<div class="col-lg-6">
						<div class="form-group form-group-sm">
							<label for="clinom">Paciente</label>
							<input type="text" name="clinom" id="clinom" readonly="readonly" value="{{$historias->clinom}}"" class="form-control" >

						</div>
					</div>
						<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="fec_nac">Fecha Nacimiento</label>
							<input type="date" name="fec_nac" id="fec_nac" value="{{$historias->fecha_nacimiento}}" class="form-control" >

						</div>
					</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Estado Civil</label>
							<select class="form-control"  name="est_civ_id" id="est_civ_id">
							
								@foreach($estados as $ec)
									@if($ec->est_civ_id == $historias->est_civ_id)
										<option selected value="{{$ec->est_civ_id}}">{{$ec->est_civ_des}}</option>
									@else
										<option value="{{$ec->est_civ_id}}">{{$ec->est_civ_des}}</option>
									@endif
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Sexo</label>
							<select class="form-control"  name="sex_id" id="sex_id">
								
								@foreach($generos as $gen)
									@if($gen->sex_id == $historias->sex_id)
										<option selected="selected" value="{{$gen->sex_id}}">{{$gen->sex_nom}}</option>
									@else
										<option value="{{$gen->sex_id}}">{{$gen->sex_nom}}</option>	
									@endif
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group form-group-sm">
							<label for="clidir">Dirección</label>
							<input type="text" name="clidir" id="clidir" value="{{$historias->clidir}}" class="form-control" >

						</div>
					</div>

					<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="clicor">Correo Electrónico</label>
							<input type="text" name="clicor" id="clicor" value="{{$historias->clicor}}"" class="form-control" >

						</div>
					</div>
					<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Teléfono / Celular</label>
							<input name="clitel" id="clitel" value="{{$historias->telefono}}" class="form-control">
						</div>
					</div>

				
				</div>

					<div class="row">

				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Fecha Atención</label>
							<input type="date" name="ate_cli_fec" id="ate_cli_fec" value="{{$historias->ate_cli_fec}}" class="form-control">
						</div>
				</div>

				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Hora</label>
							<input type="time" name="ate_cli_hor" id="ate_cli_hor" value="{{$historias->ate_cli_hor}}" class="form-control">
						</div>
					</div>


				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Especialidad</label>
							<select class="form-control"  name="esp_id" id="esp_id">
								
								@foreach($especialidades as $esp)
									@if($esp->esp_id == $historias->esp_id)
										<option selected value="{{$esp->esp_id}}">{{$esp->esp_des}}</option>
									@else
										<option value="{{$esp->esp_id}}">{{$esp->esp_des}}</option>
									@endif
								@endforeach
							</select>
						</div>
					</div>

					<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Doctores</label>
							<select class="form-control"  name="doctor" id="doctor">
								
								@foreach($doctores as $doc)
									@if($doc->IdUsuario == $historias->doctor)
										<option selected="selected" value="{{$doc->IdUsuario}}">{{$doc->name}} {{$doc->apeusu}}</option>
									@else
										<option value="{{$doc->IdUsuario}}">{{$doc->name}} {{$doc->apeusu}}</option>
									@endif
								@endforeach
							</select>
						</div>
					</div>
			</div>



					<div class="row">
						<div class="col-lg-12">
							<div class="btn-toolbar" role="toolbar" aria-label="...">
							<div class="btn-group">

									<button type="button" name="btnRegistrar"  id="btnRegistrar" class=" btn btn-primary btn-md">Actualizar</button>
							
							
							</div>
							<div class="btn-group" >
						
									<button type="button" class=" btn btn-danger btn-md">Cancelar</button>
							</div>
							
								
						</div>
						</div>

						
					</div>

				</div>
			</div> 
		</div>
	</div>
	{{Form::Close()}}
	</section>

	@endsection