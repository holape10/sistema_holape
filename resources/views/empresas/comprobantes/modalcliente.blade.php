

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-2" id="modal-cliente">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">DATOS DEL CLIENTE</h4>
				</div>
				<div class="modal-body">
					 <div class="row">
                     
                          <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo Doc.</label>
                                <select name="tdicod" id="tdicod" class="form-control">
                                    <option></option>
                                    @foreach($docidentidad as $doc)
                                        @if($doc->tdicod =='1')
                                        <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @else
                                        <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                        @endif
                                    @endforeach
                                </select>
                              
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="clinum">N&deg;</label><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload">
                                <input type="text"  name="clinum" id="clinum" value="00000000"  onKeypress="if(event.keyCode == 13) buscarcliente();"  placeholder="" class="form-control">
                             
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Nombre &oacute; Raz&oacute;n Social</label>
                                <input type="text" name="clinom" id="clinom" value="Varios"  class="form-control">
                              
                            </div>
                        </div>
                  

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Direcci&oacute;n</label>
                                <input name="clidir" id="clidir" value="--" class="form-control">
                              
                            </div>
                        </div>
                        <div  class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Correo Electr&oacute;nico</label>
                                <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
                               
                            </div>
                        </div>
                      
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Aceptar</button>
					
				</div>
			</div>
		</div>
</div>
