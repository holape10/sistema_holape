

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-2" id="modal-comprobante">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					<h4 class="modal-title">DATOS DEL COMPROBANTE</h4>
				</div>
				<div class="modal-body">
					<div class="row">
                     
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                                  <div class="form-group form-group-sm">
                                      <label>Documento</label>
                                      <select name="cmbTdo" id="cmbTdo" class="form-control">
                                          <option></option>
                                          @foreach($doccomprobante as $doc)
                                             @if($doc->tdocod == '01')
                                              <option value='{{$doc->tdocod}}' selected="selected" @if(old('tdocod') == $doc->tdocod) {{ 'selected' }} @endif >{{$doc->tdodes}}</option>
                                              @else
                                              <option value='{{$doc->tdocod}}' @if(old('tdocod') == $doc->tdocod) {{ 'selected' }} @endif >{{$doc->tdodes}}</option>
                                              @endif
                                          @endforeach
                                      </select>
                                    
                                  </div>
                        </div>
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                      <label>Serie</label>
                                  
                                      <input name="serdoc"  id="serdoc" class="form-control" >
                                 
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                              <label>N Doc.</label>
                              <input type="number"  id="numdoc" name="numdoc"  class="form-control" >      
                            </div>
                        </div>
                        
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Emision</label>
                                <input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                            </div>
                        </div>
                             
                         <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                              <label>Fecha Ingreso</label>
                              <input type="date" name="fecIng" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control"> 
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                              <label>Fecha Vencimiento</label>
                              <input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control"> 
                            </div>
                        </div>
                           
                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
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
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Aceptar</button>
					
				</div>
			</div>
		</div>
</div>
