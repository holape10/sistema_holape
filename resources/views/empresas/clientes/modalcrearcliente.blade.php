<div class="modal fade modal-slide-in-right" aria-hidden="true" role="dialog" tabindex="-1" id="modal-cliente">
    {!!Form::open(array('url'=>'clientes','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmcliente'))!!}
    {{Form::token()}}
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header" style="background-color:blue;">
                    <button type="button" class="close" data-dismiss="modal" aria-label="close">
                        <span aria-hidden="true">x</span>
                    </button>
                    <font color="white"><center><strong>Nuevo Cliente</strong></center></font>
                </div>
                <div class="modal-body">
                    <div class="row">
        <div class="col-lg-6">
            <div class="form-group form-group-sm">
                <label for="tdicod">Tipo Documento Ident.</label>
                <select class="form-control"  name="tdicod" id="tdicodn">
                    <option></option>
                    @foreach($documentos as $doc)
                        <option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
                    @endforeach
                </select>
                 <input type="hidden" name="opcion" value="1">
           </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group form-group-sm">
                <label for="clinum">Número Documento Ident.</label>
           

                <div class="input-group input-group-sm">
               
                      <input type="text" name="clinum" id="clinumn" value="{{old('clinum')}}" onKeypress="if(event.keyCode == 13) buscarcliente();" class="form-control" placeholder="Número de Documento de Identidad...">
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="buscarcliente();"><span class="fa fa-search"></span></button>
                    </span>
              </div>

                 
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group form-group-sm">
                <label for="clinom">Nombre de Cliente o Razón Social</label>
                <input type="text" name="clinom" id="clinomn" value="{{old('clinom')}}" class="form-control" >
                 
           </div>
        </div>
        <div class="col-lg-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección</label>
                <input type="text" name="clidir" id="clidirn" value="{{old('clidir')}}" class="form-control" >
               
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico</label>
                <input type="text" name="clicor" id="clicorn" value="{{old('clicor')}}" class="form-control" >
         
           </div>
        </div>
       
        <div class="col-lg-6">
            <div class="form-group form-group-sm">
                <label for="clitel">Teléfono</label>
                <input type="text" name="clitel" id="cliteln" value="{{old('clitel')}}" class="form-control" >
         
           </div>
        </div>
        
    </div>
                </div>
                <div class="modal-footer">
                    <img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgloadcliente" id="imgloadcliente">
                    <button type="button" id="btnRegCliente" class="botonescliente btn btn-primary btn-block">REGISTRAR</button>
                    
                </div>
            </div>
        </div>
    {{Form::Close()}}
</div>