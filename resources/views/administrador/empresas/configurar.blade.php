@extends ('layouts.configurar')
@section ('contenido')
<script type="text/javascript">

    $(document).ready(function()
    {
         $("#formfact").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        })

         $("#btnEmpresa").click(function(e) {
            
                  var formulario = $("#formfact").serializeArray();
                  $("#imgloadenviar").show();
                  $("#botonesenviar").hide();
                  $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/administrador/empresas',
                    data: formulario,
                  }).done(function(respuesta){

                    if(respuesta.error){

                        alert(respuesta.error);

                    }else{

                        alert(respuesta.mensaje);
                        window.location.href = "/login";
                    }
                    
                    $("#imgloadenviar").hide();
                    $("#botonesenviar").show();
                  
                  
                  });
        })  
    })

</script>
<script type="text/javascript">
   function  buscarcliente(){

  var formulario = $("#rucEmpresa").val();
  
  if(formulario.length !== 11) {
      alert("El RUC debe tener 11 dígitos");
      return;
  }

  $("#imgload").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocompleteruc/'+formulario,

  }).done(function(respuesta){

  if(respuesta.error){
     alert(respuesta.error);
      $("#imgload").hide();

  }else{

   $('#nomEmpresa').val(respuesta.nom);
   $('#dirEmpresa').val(respuesta.dir);
   
   // Lógica para auto-seleccionar el Ubigeo
   if(respuesta.ubigeo) {
       $('#ubigeo').val(respuesta.ubigeo);
       $('#ubigeo').selectpicker('refresh');
   }

   $("#imgload").hide();
    $(".botones").show(); 

  }
         
  }).fail(function() {
      alert("Ocurrió un error al intentar conectar con el servidor API.");
      $("#imgload").hide();
  });
  

}
</script>

<section class="content">
   {!!Form::open(array('url'=>'/administrador/empresas','method'=>'POST','id'=>'formfact','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-header" style="background:blue;">
                        <font size="2" color="white"><center><strong>REGISTRAR EMPRESA PRINCIPAL</strong></center></font>
                     
                    </div>
                    <div class="box-body">
    
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="rucEmpresa">RUC</label><font color="red">*</font><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload">
                <input type="text" name="rucEmpresa" onKeypress="if(event.keyCode == 13) buscarcliente();" id="rucEmpresa" value="{{old('rucEmpresa')}}" class="form-control" >
              
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-4 col-xs-4">
            <div class="form-group form-group-sm">
                <label for="id_tipo_sistema">Tipo de Negocio</label><font color="red">*</font>
                <select name="id_tipo_sistema" id="id_tipo_sistema" class="form-control">
                    @foreach($tipos_sistemas as $tipo)
                        <option value="{{$tipo->id_tipo}}">{{$tipo->nombre_sistema}}</option>
                    @endforeach
                </select>
            </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="ubigeo">Ubigeo</label>
                <select name="ubigeo" id="ubigeo" class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true">
                    @foreach($ubigeos as $ubigeo)
                        <option value="{{$ubigeo->ubi_cod}}">{{$ubigeo->ubi_des}}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
            <div class="form-group form-group-sm">
                <label for="nomEmpresa">Razón Social</label><font color="red">*</font>
                <input type="text" name="nomEmpresa" id="nomEmpresa" value="{{old('nomEmpresa')}}" class="form-control" >
                @if ($errors->has('nomEmpresa'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('nomEmpresa') }}</font></strong></span>
                @endif
           </div>
        </div>
        
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
            <div class="form-group form-group-sm">
                <label for="dirEmpresa">Dirección</label>
                <input type="text" name="dirEmpresa" id="dirEmpresa" value="{{old('dirEmpresa')}}" class="form-control" >
                @if ($errors->has('dirEmpresa'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('dirEmpresa') }}</font></strong></span>
                @endif
           </div>
        </div>
        </div>

</div> 
 <div class="box-header" style="background:blue;">
                        <font size="2" color="white"><center><strong>FACTURACIÓN ELECTRÓNICA</strong></center></font>
                     
</div>
<div class="box-body">
        <div class="row">
             <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="url">TIPO DE ENVIO</label>
                <select name="tip_env_fac" class="form-control">
                    @foreach($tip_env_fac as $tef)
                     <option value="{{$tef->tip_env_fac_id}}">{{$tef->tip_env_fac_des}}</option>
                    @endforeach
                </select>
                
            </div>
        </div>
          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="contrasena">Produccion</label>
                <select name="produccion" class="form-control">
                    
                    <option value="0">BETA</option>
                    <option value="1">PRODUCCION</option>
                </select>
                
            </div>
        </div>
            <div class="col-lg-2">
            <div class="form-group form-group-sm">
            <LABEL>Env&iacute;o a SUNAT</LABEL>
            <select class="form-control" name="envio">
                <option value="1">ENVIO AUTOMATICO</option>
                <option value="0">ENVIO MANUAL</option>
            </select>
        </div>
        </div>
           <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="url">Certificado Digital</label>
                <input type="file" name="txtCertificado" class="form-control" >
                
            </div>
        </div>
        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <label for="url">Contraseña Certificado</label>
                <input type="text" name="txtPassCert"  class="form-control" >
                
            </div>
        </div>
        <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="usuario">Usuario SUNAT</label>
                <input type="text" name="txtWsUsuario" value="{{old('txtUsuario')}}" class="form-control" >
                
            </div>
        </div>
        <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="contrasena">Contraseña SUNAT</label>
                <input type="text" name="txtWsContrasena" value="{{old('puerto')}}" class="form-control" >
                
            </div>
        </div>
        
   
        <div class="col-lg-2">
             <div class="form-group form-group-sm">
            <label>ICBPR</label>
            <input type="number" step="any" name="icbper" class="form-control" min='0' value="0.50">
        </div>
        </div>
     
    </div>

</div>

 <div class="box-header" style="background:blue;">
                        <font size="2" color="white"><center><strong>CONFIGURACIÓN DE IMPRESIÓN</strong></center></font>
                     
</div>
<div class="box-body">
      <div class="row">
        
        <div class="col-lg-2">
             <div class="form-group form-group-sm">
            <label>IMPRESION DE PEDIDOS</label>
            <input type="number" step="any" name="imp_pedido" class="form-control" min='1' value="1">
        </div>
    </div>
  
          <div class="col-lg-2">
              <div class="form-group form-group-sm">
            <label>IMPRESION COMPROBANTES</label>
            <input type="number" step="any" name="imp_venta" class="form-control" min='1' value="1">
        </div>
    </div>
     <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="formato">Formato</label>
                <select name="formato" class="form-control">
                    <option value="TICKET">TICKET</option>
                    <option value="A4">A4</option>
                </select>
            </div>
        </div>
           <div class="col-lg-2 col-md-2 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="ticket_pantalla">Imprimir Pantalla</label>
                <select name="ticket_pantalla" class="form-control">
                        <option  value="1">SI</option>
                        <option value="0">NO</option>
                </select>
            </div>
        </div>
    </div>

    <div class="row">
         <center><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgloadenviar" id="imgloadenviar"></center>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6" id="botonesenviar">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="button" id="btnEmpresa">Guardar</button>
                <a href="{{config('global.ruta')}}/administrador/empresas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
            </div>
        </div>
    </div>
</div>
    </div>
    </div>
    </div>
     {!!Form::close()!!}   
</section>
    
@endsection