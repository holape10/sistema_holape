<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-atencion">
	{!!Form::open(array('url'=>'/','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmAtencion'))!!}
	{{Form::token()}}
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header" style="background-color:blue;">
				<button type="button" class="close" data-dismiss="modal" aria-label="close">
					<span aria-hidden="true">x</span>
				</button>
				<font color="white"><center><strong>Nueva Atención</strong></center></font>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="tdicod">Tipo Documento Ident.</label>
							<select class="form-control"  name="tdicod" id="tdicod">
								<option></option>
								@foreach($documentos_identidad as $doc)
								<option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
								@endforeach
							</select>
							<input type="hidden" name="opcion" value="1">
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="clinum">Número Documento Ident.</label>
							 <div class="input-group input-group-sm">
				                  	<input type="text" name="clinum" id="clinum" value="{{old('clinum')}}" onKeypress="if(event.keyCode == 13) buscarcliente();" class="form-control" >
				                    <span class="input-group-btn">
				                      <button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="buscarcliente();"><span class="fa fa-search"></span></button>
				                    </span>
				              </div>

						</div>
					</div>

					<div class="col-lg-6">
						<div class="form-group form-group-sm">
							<label for="clinom">Paciente</label>
							<input type="text" name="clinom" id="clinom" value="{{old('clinom')}}" class="form-control" >

						</div>
					</div>
						<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="fec_nac">Fecha Nacimiento</label>
							<input type="date" name="fec_nac" id="fec_nac" value="{{old('fec_nac')}}" class="form-control" >

						</div>
					</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Estado Civil</label>
							<select class="form-control"  name="est_civ_id" id="est_civ_id">
							
								@foreach($estados as $ec)
								<option value="{{$ec->est_civ_id}}">{{$ec->est_civ_des}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Sexo</label>
							<select class="form-control"  name="sex_id" id="sex_id">
								
								@foreach($generos as $gen)
								<option value="{{$gen->sex_id}}">{{$gen->sex_nom}}</option>
								@endforeach
							</select>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="form-group form-group-sm">
							<label for="clidir">Dirección</label>
							<input type="text" name="clidir" id="clidir" value="{{old('clidir')}}" class="form-control" >

						</div>
					</div>

					<div class="col-lg-3">
						<div class="form-group form-group-sm">
							<label for="clicor">Correo Electrónico</label>
							<input type="text" name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control" >

						</div>
					</div>
					<div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Teléfono / Celular</label>
							<input name="clitel" id="clitel" value="" class="form-control">
						</div>
					</div>

				
				</div>

					<div class="row">

				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Fecha Atención</label>
							<input type="date" name="ate_cli_fec" id="ate_cli_fec" value="" class="form-control">
						</div>
				</div>

				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Hora</label>
							<input type="time" name="ate_cli_hor" id="ate_cli_hor" value="" class="form-control">
						</div>
					</div>


				<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Especialidad</label>
							<select class="form-control"  name="esp_id" id="esp_id">
								
								@foreach($especialidades as $esp)
								<option value="{{$esp->esp_id}}">{{$esp->esp_des}}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
						<div class="form-group form-group-sm">
							<label>Doctores</label>
							<select class="form-control"  name="doctor" id="doctor">
								
								@foreach($doctores as $doc)
								<option value="{{$doc->IdUsuario}}">{{$doc->name}} {{$doc->apeusu}}</option>
								@endforeach
							</select>
						</div>
					</div>
			</div>

			</div>

		
			<div class="modal-footer">
				<img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgloadcliente" id="imgloadcliente">
				<button type="button" id="btnRegistrar" class="botonescliente btn btn-primary btn-block">REGISTRAR</button>

			</div>
		</div>
	</div>
	{{Form::Close()}}
</div>