


<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-guia">

    <div class="modal-dialog modal-lg" style="width:90%">
        <div class="modal-content">
            <!--<div class="box-header" style="background-color:blue;">
                <font color="white"><center><strong>REGISTRA INGRESO / GASTO</strong></center></font>
            
            </div>-->

            <div class="modal-body" id="botonesgasto" >

  <div class="container-fluid">
            <div class="row" >
                <div class="col-lg-12">
                <div class="box">
                    <div class="box-header" style="background:blue;">
                        <font color="white" size="4"><center><strong>EMITIR GUIA DE REMISIÓN ELECTRÓNICA</strong></center></font>
                    </div>
                    
                    <div class="box-body">
                     
                       
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Serie</label>
                                <input name="serdocguia"  value="{{$senudoc->serieguia}}" id="serdocguia" class="form-control" readonly="readonly">
                            </div>
                        </div>
                        <div class="col-lg-1 col-md-1 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>N° Doc.</label>
                              
                                <input type="number"  id="numdocguia" name="numdocguia" value="{{$senudoc->numeroguia+1}}" class="form-control" readonly="readonly">

                            </div>
                        </div>
                        <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Motivo</label>
                                <select name="motivo" class="form-control">
                                    @foreach ($motivos as $mot)
                                        <option value='{{$mot->IdMotivo}}'>{{$mot->motivo}}</option>
                                    @endforeach
                                </select>
                                
                            </div>
                        </div>
                        <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Modalidad</label>
                                <select name="modalidad" class="form-control">
                                    @foreach ($modalidades as $mod)
                                        <option value='{{$mod->IdModalidad}}'>{{$mod->modalidad}}</option>
                                    @endforeach
                                </select>
                              
                            </div>
                        </div>
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Bultos</label>
                                <input type="number" id="bultos" name="bultos"  min="1" value="1" class="form-control">
                              
                            </div>
                        </div>
                        
                        <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Emision</label>
                                <input type="date" id="fecEmiguia" name="fecEmiguia" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                             
                            </div>
                        </div>
                         <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Fecha Traslado</label>
                                <input type="date" id="fechatraslado" name="fechatraslado" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                                @if ($errors->has('fechatraslado'))
                                        <span class="help-block"><strong><font color="red">{{ $errors->first('fechatraslado') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                         <div hidden="hidden"  class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>DESCONTAR STOCK</label>
                                <select name="estadostock" class="form-control">
                                        <option selected="" value='0'>No</option>
                                        <option value='1'>Si</option>
                                </select>
                              
                            </div>
                        </div>
                        
                 
                        <input type="hidden" name="tdocodguia" id="tdocodguia" value="09" class="form-control">
                    </div>
                </div>  
            </div>
   
       

         <div class="col-lg-6">
                <div class="box">
                    <div class="box-header" style="background:blue;">
                        <font color="white"><strong>Direcci&oacute;n de Partida</strong></font>
                    </div>
                    <div class="box-body">
                      
                        <div class="row">
                           <div class="col-lg-5 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="ubigeopartida">Ubigeo</label>
                                 <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="ubigeopartida" id="ubigeopartida">
                                     @foreach($ubigeos as $ubi)
                                      <option value="{{$ubi->ubi_cod}}">{{$ubi->ubi_des}}</option>
                                     @endforeach
                                  </select>
                               
                               
                              </div>
                          </div>
                        

                        <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Direcci&oacute;n</label>
                                <input type="text" name="direccionpartida" id="direccionpartida"  class="form-control">
                            
                            </div>
                        </div>
                 
                        <input type="hidden" name="codubigeopartida" >
                    </div>
                    </div>
                </div>  
        </div>

         <div class="col-lg-6">
                <div class="box">
                    <div class="box-header" style="background:blue;"> 
                        <font color="white"><strong>Direcci&oacute;n de Llegada</strong></font>
                    </div>
                    <div class="box-body">
                      
                        <div class="row">
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="ubigeollegada">Ubigeo</label>
                                    <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="ubigeollegada" id="ubigeollegada">
                                     @foreach($ubigeos as $ubi)
                                      <option value="{{$ubi->ubi_cod}}">{{$ubi->ubi_des}}</option>
                                     @endforeach
                                  </select>
                                
                              </div>
                          </div>
                       
                        <div class="col-lg-5 col-md-4 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Direcci&oacute;n</label>
                                <input type="text" name="direccionllegada" id="direccionllegada"  class="form-control">
                                
                            </div>
                        </div>
                 
                        <input type="hidden" name="codubigeollegada" >
                    </div>
                    </div>
                </div>  
        </div>
</div>
</div>
  <div class="container-fluid">
            <div class="row">
                <div class="col-lg-6">
                <div class="box">
                    <div class="box-header" style="background:blue;"> 
                        <font color="white"><strong>Datros de Transportista</strong></font>
                    </div>
                    
                     <div class="box-body">
                        <div class="row">
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Documento</label>
                                  <select name="transportistatdicod" id="transportistatdicod" class="form-control">
                                      @foreach($documentos as $doc)
                                          @if($doc->tdicod == '1')
                                          <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @else
                                          <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @endif
                                      @endforeach
                                  </select>
                               
                              </div>
                          </div>

                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="transportistanum">N° Documento</label>
                                  <input type="text"  name="transportistanum" id="transportistanum" onKeypress="if(event.keyCode == 13) buscartransportista();"    placeholder="" class="form-control">
                                
                              </div>
                          </div>
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Cliente</label>
                                  <input type="text" name="transportistanom" id="transportistanom"  class="form-control">
                                 
                              </div>
                          </div>
                 
                        </div>
                    </div>
                </div>  
            </div>
     
                <div class="col-lg-6">
                  <div class="box">
                    <div class="box-header" style="background:blue;"> 
                        <font color="white"><strong>Datos de Conductor</strong></font>
                    </div>
                    <div class="box-body">
                        <div class="row">
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Documento</label>
                                  <select name="conductortdicod" id="conductortdicod" class="form-control">
                                      @foreach($documentos as $doc)
                                          @if($doc->tdicod == '1')
                                          <option  selected="selected" value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @else
                                          <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                          @endif
                                      @endforeach
                                  </select>
                               
                              </div>
                          </div>
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label for="conductornum">N° Documento</label>
                                  <input type="text"  name="conductornum" id="conductornum" onKeypress="if(event.keyCode == 13) buscarconductor();"    placeholder="" class="form-control">
                                 
                              </div>
                          </div>
                          <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Cliente</label>
                                  <input type="text" name="conductornom" id="conductornom"  class="form-control">
                                  
                              </div>
                          </div>
                 
                          <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
                              <div class="form-group form-group-sm">
                                  <label>Placa</label>
                                  <input name="placa" id="placa" class="form-control">
                                 
                              </div>
                          </div>
                         
                        </div>
                    </div>
                </div>  
              </div>
            </div>
          </div>

</div>
    <div class="modal-footer">
                  <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadguia" id="imgloadguia"></center>
                <button type="button" id="btnguia" name="btnguia"  class="btn btn-lg btn-primary botonesguia" ><strong>REGISTRAR</strong></button>
                <button type="button" id="btncancelar" class="btn btn-lg btn-danger botonesguia" data-dismiss="modal">Cancelar</button>
            </div>

</div>
</div>

</div>