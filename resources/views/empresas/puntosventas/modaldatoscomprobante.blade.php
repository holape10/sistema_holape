

<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-datos-comprobantes">
		<div class="modal-dialog modal-lg" style="width:90%">
			<div class="modal-content">
				<!--<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-label="close">
						<span aria-hidden="true">x</span>
					</button>
					
				</div>-->
				<div class="modal-body">
					<div class="row">
						<div class="box" >
							<div class="box-header" style="background-color:blue;">
              <font color="white"><center><strong>DATOS DEL COMPROBANTE</strong></center></font>
              <div class="box-tools pull-right">
             <a  data-target="#modal-cliente" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">NUEVO CLIENTE</button></a>
          </div>
         </div>
         <div class="box-body" style="display: none;">
             

            @if(Auth::User()->hasRole('admin'))
             <div class="row"  style="display:none;">
            <div class="col-lg-3">
                <div class="form-group form-group-sm">
                    <LABEL>Estado de Pago</LABEL>
                    <select name="estadopago" id="estadopago" class="form-control">
                      @foreach($creditos as $cre)
                        <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
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

               <div class="col-lg-3"  id="divfecVen">
                   <div class="form-group form-group-sm">
                    <label>F. Vencim.</label>
                      <input type="date" name="fecVen" id="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
                  </div>
               </div>
             </div>
               @else
               <div  style="display:none;" class="row" hidden="hidden">
                <div class="col-lg-3">
                <div style="display:none;" class="form-group form-group-sm">
                    <LABEL>Estado de Pago</LABEL>
                    <select name="estadopago" id="estadopago" class="form-control">
                      @foreach($creditos as $cre)
                        <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
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

               <div class="col-lg-3"  id="divfecVen">
                   <div class="form-group form-group-sm">
                    <label>F. Vencim.</label>
                      <input type="date" name="fecVen" id="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
                  </div>
               </div>
             </div>
                    @endif
          

              <a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
              @if(isset($cpe))
              <input type="hidden" name="comprobante" id="comprobante" value="{{$cpe}}">
              @endif

              @if(isset($tdocod))
              <input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
              @endif
             
            <div class="row">
             

              <div style="display:none;" class="col-lg-3">
                <div class="form-group form-group-sm">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                   <label >
                    <input type="radio" name="moncod" value="PEN" checked="checked"> SOLES
                    </label>
                     <label >
                    <input type="radio" name="moncod" value="USD" > DOLARES
                    </label>
                </div>

                </div>
              

              </div>
          </div>

         </div>
         <div class="box-header" style="background-color:blue;display:none;">
            <font color="white"><center><strong>DATOS DEL CLIENTE</strong></center></font>
            <div class="box-tools pull-right">
             <a  data-target="#modal-cliente" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">NUEVO CLIENTE</button></a>
          </div>
         </div>
         <div class="box-body">
         	 <div class="row form-group form-group-sm">
             	 <div class="col-lg-4">
                <div class="form-group form-group-sm">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                  <label  >
                    <input type="radio" name="tdocod" id="boleta" value="03"  checked="checked"> BO
                  </label>
                  <label  >
                    <input type="radio" name="tdocod" id="factura" value="01" > FA
                  </label>
                  <label >
                    <input type="radio" name="tdocod" id="nota" value="13"  > NV
                  </label>
                
                </div>
                </div>
               
              </div>
            </div>
         </div>
         <div class="box-body" id="divcliente">
            
              <div class="row form-group form-group-sm">
              <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Tipo Documento</label>
                  <select name="tdicod" id="tdicod" class="form-control">
                    @foreach($tipodocumento as $doc)
                    @if($doc->tdicod =='6')
                    <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                    @else
                    <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                    @endif
                    @endforeach
                  </select>
                </div>
              </div>
              
              <div class="col-lg-2">
                <div class="form-group form-group-sm">
                  <label for="clinum">Num. Doc</label>
                  <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}"  placeholder="" class="form-control" >

                </div>
              </div>

              
             <div class="col-lg-3" >
              <div class="form-group">
                <label class="control-label">Cliente</label>
                <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="clicod" id="clicod" onchange="seleccionarcliente();">
                  <option>VENTA AL PORTADOR</option>
                  @foreach($clientes as $cliente)
                    <option value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}" data-direccion="{{$cliente->clidir}}" data-clinom="{{$cliente->clinom}}" data-correo="{{$cliente->clicor}}" data-telefono="{{$cliente->telefono}}">{{$cliente->clinum}} - {{$cliente->clinom}}</option>
                  @endforeach
                </select>
                <input type="hidden" readonly="readonly" name="clinom" id="clinom">
              </div>
            </div>

              <div class="col-lg-4">
                <div class="form-group form-group-sm">
                  <label>Direcci&oacute;n</label>
                  <input name="clidir" id="clidir" value="--" class="form-control">
                </div>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Correo Electr&oacute;nico</label>
                  <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
                </div>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Tel&eacute;fono</label>
                  <input name="clitel" id="clitel" value="{{old('clitel')}}" class="form-control">
                </div>
              </div>


         </div>
          </div>

        <div class="box-header" style="background-color:blue;display:none;">
            <font color="white"><center><strong>MONTO A PAGAR</strong></center></font>
         </div>
          <div class="box-body">
          
         
          
          <div class="row">
            <div class="col-lg-3">
              <div class="form-group-sm">
                <label>Total</label>
                <input type="text" class="form-control"  id="total" name="total" value='0.00' readonly="readonly">
              </div>
            </div>
              <div  class="col-lg-3">
              <div class="form-group-sm">
                <label>Paga con:</label>
                <input type="number"  step="any" class="form-control"  id="pagar" name="pagar" value="0.00" onkeyup="calculartotal();">
              </div>
            </div>
            <div  class="col-lg-3">
              <div class="form-group-sm">
                <label>Vuelto</label>
                <input type="text" class="form-control"  id="vuelto" name="vuelto" value="0.00" readonly="readonly">
              </div>
            </div>
         
            <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
            </div>
        
          <br>

          <div class="row" id="divmediopago">
            @foreach($mediospagos as $mp)
              <div class="col-lg-3">
                <div class="form-group form-group-sm">
                  <label>{{$mp->nom_med_pag}}</label>
                  <input class="mediopago form-control" id="predeterminado_{{$mp->predeterminado}}" data-predeterminado="{{$mp->predeterminado}}" name="monto[]" type="number" step="any">
                  <input class="form-control" name="medio[]" type="hidden" value="{{$mp->id_med_pag}}">
                </div>
                  
              </div>
            @endforeach
          </div>
          <div class="row">
            <div class="col-lg-4">

              <button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-lg botones">COBRAR</button><br>

            </div>
             <div class="col-lg-4">
              <button type="button" id="btnRegCompReg" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button><br>
            </div>
            
          

             <div class="col-lg-4">
              <a href="/pvgrifo"><button type="button" class=" btn btn-block btn-danger btn-lg botones">CANCELAR</button></a><br>
            </div>
          </div>
        </div>

						</div>
					</div>
				
				</div>
			</div>
		</div>
</div>
