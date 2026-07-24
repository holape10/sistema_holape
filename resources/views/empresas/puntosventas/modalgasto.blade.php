

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-gasto">
	 {!!Form::open(array('url'=>'/gastos','autocomplete'=>'off','method'=>'POST','id'=>'formgasto','role'=>'form','files'=>'true'))!!}
    				{{Form::token()}}
	<div class="modal-dialog modal-lg" style="width:90%">
		<div class="modal-content">
			<div class="box-header" style="background-color:blue;">
				<font color="white"><center><strong>REGISTRA INGRESO / GASTO</strong></center></font>
			
			</div>
			<center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadgasto" id="imgloadgasto"></center>
			<div class="modal-body" id="botonesgasto" >
				<div class="box">
					<div class="box-header" style="background-color:gray;">
						<font color="white"><center><strong>DATOS COMPROBANTE</strong></center></font>
					</div>
					<div class="box-body">
						<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>DOCUMENTO</label>
								<select name="cmbTdo" id="cmbTdo" class="form-control">
									<option></option>
									@foreach($comprobantes as $doc)
										<option value='{{$doc->tdocod}}' @if(old('tdocod') == $doc->tdocod) {{ 'selected' }} @endif >{{$doc->tdodes}}</option>
									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Serie</label>
								<input name="serdoc"  id="serdoc" class="form-control" >
							</div>
						</div>
						<div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>N° Doc.</label>
								<input type="number"  id="numdoc" name="numdoc"  class="form-control" >
							</div>
						</div>
						<div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Fecha Emision</label>
								<input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
							</div>
						</div>
						<div class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Fecha Vencimiento</label>
								<input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">

							</div>
						</div>
						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>TIPO MOVIMIENTO</label>
								<select name="cmbmovimiento" id="cmbmovimiento" class="form-control">
									<option value='GASTO'>GASTO</option>
									<option value='INGRESO'>INGRESO</option>
								</select>
							</div>
						</div>
						<div style="display:none;" class="col-lg-2 col-md-4 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Moneda</label>
								<select name="mondoc" id="mondoc" class="form-control">
									@foreach ($monedas as $mon)
									<option value='{{$mon->moncod}}' @if(old('mondoc') == $mon->moncod) {{ 'selected' }} @endif >{{$mon->monnom}}</option>
									@endforeach
								</select>
							</div>
						</div>
					</div>

					 <div  class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                <div class="box box-default">
                       <div class="box-header" style="background-color:gray;">
                        <center><strong><font color="white" size="2">DATOS DEL PROVEEDOR</font></strong></center>
                         <div class="box-tools pull-right">
          </div>
                    </div>
                    <div class="box-body">
                        <input type='hidden' name='txt_IdEmpresa' id="txt_IdEmpresa" value='{{Auth::user()->IdEmpresa}}'>
                        <div class="row">
                        <input type="hidden" name="txtProvId" id="txtProvId" >
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Documento</label>
                                <select name="cmbTdi" id="cmbTdi" class="form-control">
                                    <option></option>
                                    @foreach($documentos as $doc)
                                        @if($doc->tdicod =='6')
                                        <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @else
                                        <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @endif
                                    @endforeach
                                </select>
                   
                            </div>
                          </div>

                     

                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="txtProvNum">N° Documento</label>
                                <input type="text" id='clinum' name="txtProvNum" id="txtProvNum" value="{{old('txtProvNum')}}"  placeholder="" class="form-control clinum">
                              
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre ó Razón Social</label>
                                <input type="text" name="txtProvRaz" id="clinom" value="{{old('txtProvRaz')}}" class="form-control">
                             
                            </div>
                        </div>
                   
                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Dirección</label>
                                <input name="txtProvDir" id="clidir" value="--" class="form-control">
                               
                            </div>
                        </div>
                        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electrónico</label>
                                <input name="txtProvCor" id="clicor" value="{{old('txtProvCor')}}" class="form-control">
                               
                            </div>
                        </div>
                        <div hidden="hidden" style="display:none;" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Número Contacto</label>
                                <input name="txtProvNumCon" id="txtProvNumCon" value="{{old('txtProvNumCon')}}" class="form-control">
                              
                            </div>
                        </div>
                        <div style="display:none;" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Persona de Contacto</label>
                                <input name="txtProvCon" id="txtProvCon" value="{{old('txtProvCont')}}" class="form-control">
                              
                            </div>
                        </div>
                    </div>
                    </div>
                </div>  
            </div>
            </div>
        </div>

					<div class="box-header" style="background-color:gray;">
						<font color="white"><center><strong>DATOS COMPROBANTE</strong></center></font>
					</div>
					<div class="box-body">
                        <table id="detgasto" class="table table-border table-striped">
                            <thead>
                            	<th>TIpo</th>
                                <th>Detalle</th>
								<th>Monto</th>
                                <th><button type="button" onClick="agregarlineagasto();"  class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                            </thead>
                            <tbody id="">
                             
                            </tbody>
                        </table>

                        <div class="col-lg-2">
                        	 <div class="form-group form-group-sm">
                        	  <label>TOTAL</label>
                        <input class="form-control" type="number" min="0"  step="any" style="text-align:right;" id="total_gasto" name="total_gasto" value='0.00' readonly="readonly">
                        </div>
                      
                        </div>
                       
                   
					</div>
					
				
				</div>
				
			</div>
			<div class="modal-footer">
				<button type="button" id="btngasto" name="btngasto"  class="btn btn-sm btn-primary"><strong>REGISTRAR</strong></button>
                <button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">Cerrar</button>
		    </div>

		</div>
	</div>
	 {!!Form::close()!!}  
</div>
