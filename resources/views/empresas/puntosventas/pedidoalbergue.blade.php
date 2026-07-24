@extends('layouts.empresas')
@section('contenido')

@include('empresas.clientes.modalcrearcliente')
@include('empresas.puntosventas.modalpresentaciones')

<style>
#b1
{
 /*sirve para los caracteres cuando es una palabra grande se salte a la otra linea */
 white-space: normal;
}
#scroll
{
  height: 650px;
  width: 800px;
  overflow: scroll;
}

.ui-autocomplete {
     z-index: 9999 !important;

}



</style>

<body>


<script>

function mostrar(){
 	
 
  var prog = $("#prog_id").val();
   var cantidad = $("#cantidad").val();

 
     $("#divservicios").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/calcularpedidoalbergue/"+prog+"/"+cantidad,

  }).done(function(respuesta){
    $("#divservicios").html(respuesta.vista);
  });
 
 

}

   $(document).ready(function()
   {

    
          $("#part_suc").change(function() {

              


                var part_suc = $("#part_suc").val();
                $("#partida").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+part_suc,

                }).done(function(respuesta){
                  $("#partida").html(respuesta.vista);

                });

              });

              $("#almacen").change(function() {


                $("#btnCategorias").click();

              });



    $("#producto").focus();

    $('#clinum').val('00000000');
    $('#clinom').val('Varios');

  
    $("#btnRegComp").on("click", function() {


      var formulario = $("#formservicio").serializeArray();
      $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrarpedidoalbergue',
        data: formulario,
      }).done(function(respuesta){


        if(respuesta.estado =='error'){

            alert(respuesta.mensaje);

        }else{

            window.location.href = "/listarpedidoalbergue";
           
 
        }

          $("#imgload").hide();
          $(".botones").show();

      });

    });

});



</script>

<script>

 $(document).ready(function()
 {

   $("#formfact").keypress(function(e) {
    if (e.which == 13) {
      return false;
    }
  })


  

            });

function mostrar(){
  var id = $("#servicio").val();

  $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/consultarservicio/"+id,

  }).done(function(respuesta){
    $("#detalle").html(respuesta.vista);
  });

}



function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
  calculartotal();
};





function  buscarcliente(){


  var formulario = $("#clinumn").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#clinomn').val(respuesta[0].nom);
   $('#clidirn').val(respuesta[0].dir);
   $('#cliteln').val(respuesta[0].telefono);
   $('#clicorn').val(respuesta[0].cor);
   $('#clicodn').val(respuesta[0].clicod);
   $("#tdicodn").val(respuesta[0].tdicod).attr('selected', 'selected');

   $("#imgloadcliente").hide();
    $(".botones").show();
          
  });

  

}





    $("#btnRegCliente").on("click", function() {


      var formulario = $("#frmcliente").serializeArray();
      $("#imgloadcliente").show();
      $(".botonescliente").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrarcliente',
        data: formulario,
      }).done(function(respuesta){

        $("#divcliente").html(respuesta.vista);
        $("#imgloadcliente").hide();
     
         $(".botonescliente").show();
          $('#modal-cliente').modal('toggle');
      });



    });


function seleccionarcliente(){

  if($('#clicod').find(':selected').attr('data-clinum')==''){
      $('#clinum').val('00000000');
  }else{
      $('#clinum').val($('#clicod').find(':selected').attr('data-clinum'));
  }
  

  if($('#clicod').find(':selected').attr('data-documento')==''){
      $("#tdicod").val('1');
  }else{
      $("#tdicod").val($('#clicod').find(':selected').attr('data-documento'));
  }
  


   $('#clinom').val($('#clicod').find(':selected').attr('data-clinom'));
   $('#clidir').val($('#clicod').find(':selected').attr('data-direccion'));
   $('#clicor').val($('#clicod').find(':selected').attr('data-correo'));
   
   
    $("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));
    
      if($('#tdicod').val() =='6' ){
             $('#factura').prop("checked",true);
      }

      if($('#tdicod').val() =='1' ){
         $('#boleta').prop("checked",true);
      }



}




}
</script>


</br>
 

<div class="container-fluid" id="general">
   {!!Form::open(array('url'=>'/restaurantpunto','autocomplete'=>'off','method'=>'POST','name'=>'formservicio','id'=>'formservicio','role'=>'form','files'=>'true'))!!}
            {{Form::token()}}
    <input type="hidden" name="opcion" id="opcion" value="0">
    <div class="row" hidden="hidden">
      <div class="col-lg-12">
         <div class="box">
           <div class="box-header with-border  form-group form-group-sm" style="background-color:blue">
            <div  class="col-lg-2">
                <a href="" data-target="#modalproductos" data-toggle="modal"><button class="btn btn-sm btn-warning"><strong>AGREGAR PRODUCTOS</strong></button></a>
            </div>
       
             <div  class="col-lg-3">
               <font color="white"><strong>{{$datos->tipo_negocio}}</strong></font>
                
            </div>
          
             
                   
           </div>
      </div>
    </div>
    </div>


    
    <div class="row">
     
            <div class="col-lg-12">
              <div class="box">
                 <div class="box-header" style="background-color:blue;">
                    <font color="white"><strong><center>{{$datos->tipo_negocio}}</center></strong></font>


                 </div>
                <div class="box-header with-border form-group-sm">
                  
                      
                <div class="col-lg-2">
	                <div class="form-group form-group-sm">
	                   <label>FECHA</label>
	                     <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
	                </div>
               </div>
                <div class="col-lg-3" >
      <div class="form-group form-group-sm">
        <LABEL>SUCURSAL</LABEL>
        <select name="part_suc" id="part_suc" class="form-control">
          @foreach($negocios as $neg)
          <option value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="col-lg-3" id="partida">
      <div class="form-group form-group-sm">
        <LABEL>ALMACEN</LABEL>
        <select name="almacen" id="almacen" class="form-control">
          @foreach($almacenes as $alm)
          <option value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
          @endforeach
        </select>
      </div>
    </div>
                  <div  class="col-lg-3">

                    <div class="form-group form-group-sm">
                      <label>PROGRAMAS</label>
                        <select style=" font-weight: bold;" class="form-control selectpicker input-sm" onkeypress="if(event.keyCode == 13) mostrar();" onchange="mostrar();" data-show-subtext="true" data-live-search="true" name="prog_id" id="prog_id">
                          <option></option>
                          @foreach($programas as $prog)
                          <option style="font-weight:bold;color:black;font-size:10pt;" value="{{$prog->prog_cod}}">{{$prog->prog_cod}}</option>
                          @endforeach
                     </select>
                    </div>
                   
                  </div>
                
                      
                  <div  class="col-lg-3">

                    <div class="form-group form-group-sm">
                      <label>CANTIDAD</label>
                        <input type="number" step="any" name="cantidad" id="cantidad" class="form-control input-sm" value="0" onkeypress="if(event.keyCode == 13) mostrar();">
                    </div>
                   
                  </div>

                 
                  <div  hidden="hidden" class="col-lg-6">

                    <div class="form-group form-group-sm">
                    	<label>SERVICIOS</label>
                        <select style=" font-weight: bold;" class="form-control selectpicker input-sm" onkeypress="if(event.keyCode == 13) mostrar();" onchange="mostrar();" data-show-subtext="true" data-live-search="true" name="servicio" id="servicio">
                          <option></option>
                          @foreach($servicios as $ser)
                          <option style="font-weight:bold;color:black;font-size:10pt;" value="{{$ser->ser_cod}}">{{$ser->ser_nom}}</option>
                          @endforeach
                     </select>
                    </div>
                   
                  </div>
                
                </div>
               
              </div>
            </div>

     
 
      <div  class="col-lg-12" id="divservicios">
              <div class="box">
                <div class="box-header" style="background-color:blue;">
                   <font color="white"><center><strong>DETALLE DEL PEDIDO</strong></center></font>
                </div>
                 <div class="box-body">
                   <table class="table table-hover" id="grdet">
                        <thead>

                      <th>Producto</th>
                      <th>Cantidad</th>
                         <th>U.M</th>
                    
                     

                    </thead>

                    <tbody>

                    </tbody>
                  </table>
                 
                </div>
            </div>
      </div>

      <div class="col-lg-12">
      	<div class="box">
      		<div class="box-body">

           		<center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>

      			 <div class="col-lg-6">

              <button type="button" id="btnRegComp" class="btn btn-block btn-success btn-lg botones">REGISTRAR</button><br>

            </div>
             <div class="col-lg-6">
             <a href="/listarpedidos"> <button type="button" id="btnSalir" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a><br>
            </div>
      		</div>
      	</div>
      </div>
</div>
      


<div hidden="hidden" class="col-lg-6">
  <div class="col-lg-12">
        <div class="box">
    
      
         <div class="box-header" style="background-color:blue;">
            <font color="white"><center><strong>DATOS DEL CLIENTE</strong></center></font>
            <div class="box-tools pull-right">
             <a  data-target="#modal-cliente" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">NUEVO CLIENTE</button></a>
          </div>
         </div>
         <div class="box-body" id="divcliente">
             <div class="row form-group form-group-sm">
              <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Documento</label>
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
              
              <div hidden="hidden" class="col-lg-2">
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
              <!--<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Tel&eacute;fono</label>
                  <input name="clitel" id="clitel" value="{{old('clitel')}}" class="form-control">
                </div>
              </div>-->


         </div>
          </div>

    
   
     </div>
</div>
</div>


{!!Form::close()!!}
</div>
</div>


@endsection
