<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-comprobante">

		<div class="modal-dialog modal-lg">
			<div class="modal-content">
				<div class="modal-body">
					<div class="box-header" style="background-color:#E8E8E8;">
					<strong><font style="font-size:10pt;font-weight:bold;"><center>DATOS DEL COMPROBANTE</center></font></strong>
					<div class="box-tools pull-right">
						<div class="form-check">
							<label class="form-check-label" for="flexCheckDefault">
								IMPRIMIR
							</label>
							<input class="form-check-input" name="imprimir" type="checkbox" value="1" checked="checked">
						</div>
					</div>
				</div>
				<div class="box-body" >
					<div class="row">
						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>Comprobante</label>
								<select class="form-control" name="tdocod" id="tdocod">
									@foreach($comprobantes as $comp)
									@if($comp->tdocod==$negocio->tdocod_pred)
									<option selected="selected" value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
									@else
									<option  value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
									@endif
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>Estado Pago</label>
								<select class="form-control" name="estadopago"  id="estadopago">
									@foreach($estadopagos as $est_pag)
									<option value="{{$est_pag->cre_dia_id}}" data-medio="{{$est_pag->cre_dia_tip}}" data-dias="{{$est_pag->cre_dia_fac}}">{{$est_pag->cre_dia_nom}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>F. Emisión</label>
								<input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
							</div>
						</div>
						<div class="col-lg-3"  id="divfecVen" hidden="hidden">
							<div class="form-group form-group-sm">
								<label>F. Vencim.</label>
								<input type="date" name="fecVen" id="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
							</div>
						</div>
						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>X CONSUMO</label>
								<select class="form-control" name="consumo">
									<option value="0">NO</option>
									<option value="1">SI</option>
								</select>
							</div>
						</div>
						<div class="col-lg-1">
							<div class="form-group form-group-sm">
								<center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadcliente" id="imgloadcliente"></center>
							</div>
						</div>
					</div>
					<div class="row form-group form-group-sm">
						<div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Tipo</label>
								<select name="tdicod" id="tdicod" class="form-control">
									@foreach($documentos as $doc)

									@if($doc->tdicod == '1')
									<option selected="selected"  value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
									@else
									<option value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
									@endif

									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>DNI / RUC</label>
								<div class="input-group input-group-sm">
									<input name="clinum" id="clinum" value="00000000" class="form-control" onkeypress="if(event.keyCode == 13) buscarclienteruc();">
									<input type="hidden" name="clicod" id="clicod"  class="form-control">
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary btn-flat" onclick="buscarclienteruc();"><span class="fa fa-search"></span></button>

									</span>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Nombre o Razon Social -Cliente</label>
								<div class="input-group input-group-sm">
									<input name="clinom" id="clinom" value="VENTA AL PORTADOR" class="form-control" onkeypress="if(event.keyCode == 13) buscarclientenombre();">
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary btn-flat" onclick="buscarclientenombre();"><span class="fa fa-search"></span></button>
									</span>
								</div>
							</div>
						</div>
					</div>
					<div class="row form-group form-group-sm">
						<div class="col-lg-4">
							<div class="form-group">
								<label>Direcci&oacute;n</label>
								<div class="input-group input-group-sm">
									<input name="clidir" id="clidir" value="--" class="form-control">
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="seleccionardireccion();"><span class="fa fa-search"></span></button>
									</span>
								</div>
							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Correo</label>
								<input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
							</div>
						</div>
						<div hidden="hidden" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Correo Electr&oacute;nico 2</label>
								<input name="clicor2" id="clicor2" value="" class="form-control">
							</div>
						</div>
						<div hidden="hidden"  class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Correo Electr&oacute;nico 3</label>
								<input name="clicor3" id="clicor3" value="" class="form-control">
							</div>
						</div>
						<div hidden="hidden"  class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Correo Electr&oacute;nico 4</label>
								<input name="clicor4" id="clicor4" value="" class="form-control">
							</div>
						</div>
	              	<!--<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
	                		<div class="form-group form-group-sm">
	                  			<label>Tel&eacute;fono</label>
	                  			<input name="clitel" id="clitel" value="{{old('clitel')}}" class="form-control">
	                		</div>
	                	</div>-->
	                </div>
	                <div class="row" hidden="hidden">
	                	<div class="col-lg-12">
	                		<label>Observaciones:</label>
	                		<textarea class="form-control" rows="5" name="observaciones"></textarea>
	                	</div>
	                </div>
	            </div>
				</div>
				<div class="modal-footer">
	
					<button type="button" class="btn btn-primary"  data-dismiss="modal">Confirmar</button>
				</div>
			</div>
		</div>

</div>
