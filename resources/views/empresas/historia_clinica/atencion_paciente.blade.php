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
				url: '/registrardiagnostico',
				data: formulario,
			}).done(function(respuesta){


				if(respuesta.estado =='error'){

					alert(respuesta.mensaje);

					$("#imgload").hide();
					$(".botones").show();

				}else{


					window.location.href = "/historiasasignadas";


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
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Fecha Cita</label>
								<input class="form-control" readonly="readonly" type="date" value="{{$historias->ate_cli_fec}}">
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Fecha Cita</label>
								<input class="form-control" readonly="readonly" type="time" value="{{$historias->ate_cli_hor}}">
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Especialidad</label>
								<input class="form-control" readonly="readonly" type="text" value="{{$historias->esp_des}}">
							</div>
						</div>
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Doctor</label>
								<input class="form-control" readonly="readonly" type="text" value="{{$historias->name}} {{$historias->apeusu}}">
							</div>
						</div>

					</div>

					<div class="row">
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Frecuencia Cardíaca</label>
								<input class="form-control"  type="number" step="any" name="fre_car" value="">
							</div>
						</div>

						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Frecuencia Respiratoria</label>
								<input class="form-control" name="fre_res" type="number" step="any" value="">
							</div>
						</div>

						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Peso</label>
								<input class="form-control" name="peso" step="any" type="number" value="">
							</div>
						</div>

						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Talla</label>
								<input class="form-control" name="talla" type="number" step="any" value="">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>Motivo Consulta</label>
								<textarea class="form-control" rows="5" name="mot_con"></textarea>
							</div>
						</div>
						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>Antecedentes</label>
								<textarea class="form-control" rows="5" name="antecedente"></textarea>
							</div>
						</div>
						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>Alergías</label>
								<textarea class="form-control" rows="5" name="alergia"></textarea>
							</div>
						</div>
						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>Intervenciones Quirúrgicas</label>
								<textarea class="form-control" rows="5" name="int_qui"></textarea>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>Exámen Físico</label>
								<textarea class="form-control" rows="5" name="exa_fis"></textarea>
							</div>
						</div>

						<div class="col-lg-12">
							<div class="form-group form-group-sm">
								<label>Diagnóstico</label>
								<textarea class="form-control" rows="5" name="diagnostico"></textarea>
							</div>
						</div>
							<div class="col-lg-12">
								<div class="form-group form-group-sm">
									<label>Tratamiento</label>
									<textarea class="form-control" rows="5" name="tratamiento"></textarea>
								</div>
							</div>
						
					</div>

					<div class="row">
						<div class="col-lg-2">
							<div class="form-group form-group-sm">
								<label>Próxima Cita</label>
								<input type="date" class="form-control" name="pro_cit">
							</div>
						</div>
					</div>

					<div class="row">
						<div class="col-lg-12">
							<div class="btn-toolbar" role="toolbar" aria-label="...">
							<div class="btn-group">

									<button type="button" name="btnRegistrar"  id="btnRegistrar" class=" btn btn-primary btn-md">Registrar</button>
							
							
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