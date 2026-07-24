@extends('layouts.empresas')
@section('contenido')
 
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
</style>

 <body>
<script>

   $(document).ready(function()
    {


       $("#btnRegComp").on("click", function() {
        var formulario = $("#formfact").serializeArray();
        $("#imgload1").show();
        $(".botones").hide();
        $.ajax({
          type: "POST",
          dataType: 'json',
          url: '/registrarcobroplaca',
          data: formulario,
        }).done(function(respuesta){

        
           window.location.href = "/ingresovehiculo";
           $("#imgload").hide();
             
      
       });



      });


         $('#clinum').val("00000000");
        $('#clinom').val("VARIOS");
        
       var calnotsub = parseFloat($("#base").val()/1.1055).toFixed(2);
        
      $("#estadopago").on("change", function() {

       if($('#estadopago option:selected').val() !='1'){

           $('#divmediopago').hide();
       }else{
          $('#divmediopago').show();
       }

      });

      // $("#total").val(calnotsub);
      // $("#totalgeneral").val(calnotsub);
      // $("#subtotal").val(calnotsub);
       //$("#igv").val('0.00');

        $("#formfact").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        })


     
         $('#formfact').validate({

            rules: {
                
        
                clinum:{
                    required:true,
                    digits:true,
                    maxlength:11
                },
                clinom:"required",
                clidir:"required",
                clicor: {
                    email:true
                },
                obser:{
                    maxlength: 250
                }

               },


            messages: {
             
             
                clinum:{
                    required:"Ingresar N° Documento de Identidad",
                    digits:"Ingresar un N° de documento válido",
                    maxlength:"El N° documento de identidad es como máximo de 11 dígitos"
                    
                },
                clinom:"Ingresar el nombre del cliente",
                clidir:"Ingresar la dirección del cliente",
                clicor:{
                    email:"Ingresar un email válido"
                }
            }

        })


    
       $('#clinum').on('dblclick', function() {
            $('#clinum').prop("readonly",false);
            $('#clinom').prop("readonly",false);
            $('#clidir').prop("readonly",false);
            $('#clicor').prop("readonly",false);
            $('#clinum').val("");
            $('#clinom').val("");
            $('#clidir').val("--");
            $('#clicor').val("");
        })
    
        var comprobante = $("#comprobante").val();
        var documento = $("#documento").val();
        $("#btnPrint").printPage({

          url: "/imprimir/"+comprobante+"/"+documento,
          attr: "href",
          messageBox:false
          
        })

      $('#boleta').attr('checked', 'checked');
      $('#soles').attr('checked', 'checked');
      $('#efectivo').attr('checked', 'checked');

      if($('#efectivo').is(':checked')){
         $('#txtTipPag').val('Efectivo');
      }

      if($('#tarjeta').is(':checked')){
         $('#txtTipPag').val('Tarjeta');
      }

        $("#tarjeta").on('change', function (){

         if($('#tarjeta').is(':checked')){
              $('#txtTipPag').val('Tarjeta');
          }

      })


    $("#efectivo").on('change', function (){

         if($('#efectivo').is(':checked')){
         
               $('#txtTipPag').val('Efectivo');
          }

      })



       if($('#nota').is(':checked')){
           
            $("#tdicod").val('1');
             $('#tdocod').val('13');
      }

      if($('#boleta').is(':checked')){
            $("#estadopago").val("1");
            $("#tdicod").val('1');
             $('#tdocod').val('03');
      }

      if($('#vale').is(':checked')){
           
           $("estadopago").val("3");;
            $("#tdicod").val('1');
             $('#tdocod').val('50');
      }


      if($('#factura').is(':checked')){
        $("#estadopago").val('1');
          $('#clinum').val('');
          $('#clinom').val('');
          $("#tdicod").val('6');
          $('#tdocod').val('01');
      }

    /*  if($('#nota').is(':checked')){
          $('#clinum').val('');
          $('#clinom').val('');
          $("#tdicod").val('1');
          $('#tdocod').val('13');
      }*/

      if($('#soles').is(':checked')){
          $('#key').prop('disabled',true);
          $('#moncod').val('PEN');
            $('#key').val('0.00');

      }

      if($('#dolares').is(':checked')){
          $('#key').prop('disabled',false);
            $('#moncod').val('USD');
      }

       $("#soles").on('change', function (){

         if($('#soles').is(':checked')){
              $('#key').prop('disabled',true);
               $('#moncod').val('PEN');
                 $('#key').val('0.00');
          }

      })


         $("#dolares").on('change', function (){

         if($('#dolares').is(':checked')){
              $('#key').prop('disabled',false);
               $('#moncod').val('USD');
          }

      })


       $("#factura").on('change', function (){

         if($('#factura').is(':checked')){
            $("#estadopago").val('1');
            $("#tdicod").val('6');
            $('#tdocod').val('01');
          }

      })

      $("#boleta").on('change', function (){
          if($('#boleta').is(':checked')){
            $("#estadopago").val('1');
            $("#tdicod").val('1');
            $('#tdocod').val('03');
          }
      })

      $("#nota").on('change', function (){
          if($('#nota').is(':checked')){
            $("#tdicod").val('1');
            $('#tdocod').val('13');
          }
      })

        $("#vale").on('change', function (){
          if($('#vale').is(':checked')){
            $("#estadopago").val("3");
            $("#tdicod").val('1');
            $('#tdocod').val('50');
          }
      })

 /*$('#btnRegComp').on('click',function(){
            var formData = {
               "_token": "{{ csrf_token() }}",
              'cant' :   $('#formfact').find( "input[name='cant[]']" ).val(),


            }

              $.ajax({
                type: "POST",
                dataType: 'json',
                url: "/pos",
                data: formData,
              }).done(function(respuesta){
                $("#detmenu").html(respuesta.mensaje);
              });

              });

*/
    });

      function mostrar(comp){
      var id = comp.id;
      var val = comp.value;
      $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
      $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/consultarmenucobrar/"+val,

      }).done(function(respuesta){
        $("#detmenu").html(respuesta.vista);
      });

    }

    $(function(){
      $('#key').keyboard();
    });


  function  buscarcliente(){

    
          var formulario = $("#clinum").val();
          $("#imgload").show();
         // $(".botones").hide();
          $.ajax({
            type: "get",
            dataType: 'json',
            url: '/autocomplete/'+formulario,
            
          }).done(function(respuesta){



             $('#clinom').val(respuesta[0].nom);
                     $('#clidir').val(respuesta[0].dir);
                     $('#clicor').val(respuesta[0].cor);
                     $('#clicod').val(respuesta[0].clicod);
                     $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

          $("#imgload").hide();
          //$(".botones").show();
          
          });

  

}

  function deleteRow(btn) {
      var row = btn.parentNode.parentNode;
      row.parentNode.removeChild(row);
      calculartotal();
    };

  function Calcular(ele) {

    if(ele == '03' || ele =='01' || ele =='50' ){

      var caltot = parseFloat($("#base").val()).toFixed(2);
      var calsub = parseFloat(caltot/1.1055).toFixed(2);

      var caligv = (caltot-calsub).toFixed(2);

      $("#totalgeneral").val(caltot);
      $("#total").val(caltot);
      $("#igv").val(caligv);

    }

    if(ele =='13'){

      
    var calnotsub = parseFloat($("#base").val()/1.1055).toFixed(2);
    
     $("#totalgeneral").val(calnotsub);
     $("#total").val(calnotsub);
     $("#subtotal").val(calnotsub);
     $("#igv").val('0.00');

    }
     
 

  };

  function calculartotal(){

   var ntotal = 0,nigv=0 ,bsubtotal=0,descuento=0,igv=0,total=0,subtotal=0;
   var tdocod =  $('#tdocod').val();
  
   descuento = $('#descuento').val();
   
   if(descuento > 0){
        if(tdocod =='13'){
          
          var calnotsub = parseFloat($("#base").val()/1.1055).toFixed(2)-parseFloat(descuento);
        
          $("#total").val(calnotsub);
          $("#subtotal").val(calnotsub);
          $("#igv").val('0.00');

          

       }else{

          var caltot = parseFloat($("#base").val()).toFixed(2)-parseFloat(descuento);
          var calsub = parseFloat(caltot/1.1055).toFixed(2);

          var caligv = (caltot-calsub).toFixed(2);

          $("#total").val(caltot);
          $("#igv").val(caligv);

       }
  
   }else{

     if(tdocod =='13'){
      
      var calnotsub = parseFloat($("#base").val()/1.1055).toFixed(2);
    
      $("#total").val(calnotsub);
      $("#subtotal").val(calnotsub);
      $("#igv").val('0.00');

      

   }else{

      var caltot = parseFloat($("#base").val()).toFixed(2);
      var calsub = parseFloat(caltot/1.1055).toFixed(2);

      var caligv = (caltot-calsub).toFixed(2);

      $("#total").val(caltot);
      $("#igv").val(caligv);

   }

   }
   
   
     $('#predeterminado_1').val($("#total").val());


};

</script>


</br>


<div class="container-fluid">
  <div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
      @if(session()->has('info'))
        <div class="alert alert-danger">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong>Alerta!</strong> {{ session('info') }}
      </div>
      @endif


      @if(session()->has('success'))
        <div class="alert alert-success">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
          <strong>Información!</strong> {{ session('success') }}
        </div>
      @endif
    </div>
  </div>

{!!Form::open(array('url'=>'/restaurant','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
  <div class="row">
    <div class="col-lg-12">
      
         <div class="box-header with-border" style="background-color:blue;">
           <strong><font color="white"><center>{{$pedidos->placa}}</center></font> </strong>
         </div>
 
    </div>
  </div>
  <div class="row">
    <div class="col-lg-6">
      <div class="box">
       
         <div class="box-header with-border" style="background-color:#585858;">
           <strong><font color="white"><center>DATOS DEL VEHICULO</center></font> </strong>
         </div>
          <div class="box-body">
           
              <input type="hidden" name="ped_id" value="{{$pedido->ped_id}}">

              <input style="display:none;" type="date" class="form-control input-sm" value="{{Carbon::now()->format('Y-m-d')}}" name="fecha" id="fecha">
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>PLACA</label>
                  <input class="form-control" type="text" readonly="readonly" value="{{$pedido->placa}}" name="placa">
                </div>
              </div>

              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>TIPO</label>
                  <select disabled="disabled" class="form-control">
                      @foreach($tiposvehiculos as $tipovehiculo)
                        @if($tipovehiculo->id_tipo_vehiculo == $pedido->tipovehiculo)
                          <option selected="selected" value="{{$tipovehiculo->id_tipo_vehiculo}}">{{$tipovehiculo->descripcion}}</option>
                        @else
                          <option value="{{$tipovehiculo->id_tipo_vehiculo}}">{{$tipovehiculo->descripcion}}</option>
                        @endif  
                    @endforeach
                  </select>
                </div>
                <input type="hidden" readonly="readonly" name="tipovehiculo" value="{{$pedido->tipovehiculo}}">
              </div>
              

              <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>DESCRIPCION</label>
                  <input class="form-control" readonly="readonly" type="text" value="{{$pedido->descripcion}}" name="descripcion">
                </div>
              </div>
          </div>

          <div class="box-header with-border" style="background-color:#585858;">
            <strong><font color="white"><center>TARIFA</center></font> </strong>
          </div>
          <div class="box-body">
              <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                  <div class="form-group form-group-sm">
                    <label>TARIFAS</label>
                    <select disabled="disabled"  class="form-control">
                      @foreach($tarifas as $tarifa)
                       @if($tarifa->id_tarifa == $pedido->tarifa)
                         <option  selected="selected" value="{{$tarifa->id_tarifa}}">{{$tarifa->descripcion}} / S/. {{$tarifa->precio}}</option>
                      @else
                         <option value="{{$tarifa->id_tarifa}}">{{$tarifa->descripcion}} / S/. {{$tarifa->precio}}</option>
                      @endif

                     
                      @endforeach
                    </select>
                  </div>
                  <input type="hidden" readonly="readonly" name="tarifa" value="{{$pedido->tarifa}}">
              </div>

              <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  @if($unitiempo->id_uni_tie =='DIA')
                    <label>DIAS</label>
                  @elseif($unitiempo->id_uni_tie =='HR')
                    <label>Horas</label>
                  @endif
                  <input readonly="readonly" class="form-control" type="text" value="{{$detallepedido->cantidad}}" name="horas">
                </div>
              </div>
          </div>

          <div class="box-header with-border" style="background-color:#585858;">
            <strong><font color="white"><center>CLIENTE</center></font> </strong>
          </div>
          <div class="box-body">
              <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Tipo Doc.</label>
                    <select name="tdicod" id="tdicod" class="form-control">
                            @foreach($tipodocumento as $doc)
                                 @if($doc->tdicod == $pedido->tdicod)
                                    <option selected="selected"  value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                 @else
                                    <option value='{{$doc->tdicod}}' @if(old('tdicod') == $doc->tdicod) {{ 'selected' }} @endif >{{$doc->tdides}}</option>
                                @endif
                            @endforeach
                                      </select>
                    @if ($errors->has('tdicod'))
                            <span class="help-block"><strong><font color="red">{{ $errors->first('tdicod') }}</font></strong></span>
                    @endif
                </div>
            </div>
           <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" style="display:none;" class="form-control">
           <input  type="hidden" readonly="readonly" id="tdocod" name="tdocod" class="form-control">
           <input  type="hidden" readonly="readonly" id="moncod" name="moncod" class="form-control">
              
           <input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}" style="display:none;" class="form-control">

            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="clinum">Num. Doc</label><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload">
                    <input type="text"  name="clinum" id="clinum" value="{{$pedido->dni}}" onKeypress="if(event.keyCode == 13) buscarcliente();" placeholder="" class="form-control" >
                
                </div>
            </div>

            <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Raz&oacute;n Social</label>
                    <input type="text" name="clinom" id="clinom" value="{{$pedido->nombre}}" class="form-control">
                  
                </div>
            </div>
            <div class="col-lg-5 col-md-4 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Direcci&oacute;n</label>
                    <input name="clidir" id="clidir" value="--" class="form-control">
                   
                </div>
            </div>
            <div class="col-lg-5 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>Correo Electr&oacute;nico</label>
                    <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
                   
                </div>
            </div>
          </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="box">
        <div class="box-header with-border" style="background-color:#585858;">
          <strong><font color="white"><center>DATOS DE PAGO</center></font> </strong>
        </div>
          <div class="box-body">
            <div class="row">
              <div class="col-lg-5">
                <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label  class="btn btn-primary">
                      <input type="radio" name="options" class="options" onchange="Calcular(03);" id="boleta" value="03" autocomplete="off"> BO
                    </label>
                    <label  class="btn btn-success">
                      <input type="radio" name="options" class="options" onchange="Calcular(01);" id="factura" value="01" autocomplete="off"> FA
                    </label>
                    <label style="display:none;" class="btn btn-default">
                      <input type="radio" name="options" class="options" onchange="Calcular(13);" id="nota" value="13" autocomplete="off" checked="checked"> NV
                    </label>
                      <label class="btn btn-warning">
                      <input type="radio" name="options" class="options" onchange="Calcular(50);" id="vale" value="50" autocomplete="off" > VC
                    </label>
                </div>
            </div>
          </div><BR>

          <div class="row">
             <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label>CREDITO</label>
                    <select name="estadopago" id="estadopago" class="form-control">
                      @foreach($creditos as $cre)
                        <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
                      @endforeach
                    </select>
                  
                </div>
            </div>
          </div>
          <div class="row" >
             <div class="col-lg-2">
              <div class="form-group form-group-sm">
                <label>DESCUENTO</label>
                 <input type="text"  class="form-control" onkeyup="calculartotal();" value="{{$pedido->descuento}}" id="descuento" name="descuento">
              </div>
            </div>
            <div class="col-lg-2">
              <div class="form-group form-group-sm">
                <label>SUBTOTAL</label>
                 <input type="text" readonly="readonly" class="form-control" value="{{$pedido->subtotal}}" id="subtotal" name="subtotal">
              </div>
            </div>
            <div class="col-lg-2">
                <div class="form-group form-group-sm">
                  <label>IGV</label>
                  <input type="text" readonly="readonly" class="form-control" value="{{$pedido->igv}}" id="igv" name="igv">
              </div>
            </div>
            <div class="col-lg-2">
               <div class="form-group form-group-sm">
                 <label>TOTAL</label>
                 <input type="text" readonly="readonly" class="form-control" value="{{$pedido->total}}" id="total" name="total">
                 <input type="hidden" readonly="readonly" class="form-control" value="{{$pedido->total}}" id="totalgeneral" name="totalgeneral">
                 <input type="hidden" readonly="readonly" readonly="readonly" class="form-control" value="{{$pedido->total}}" id="base" name="base">
              </div>
            </div>
          </div>
          <br>

        
       
          <div class="row" id="divmediopago">
            @foreach($mediospagos as $mp)
              <div class="col-lg-3">
                <div class="form-group form-group-sm">
                  <label>{{$mp->nom_med_pag}}</label>
                  @if($mp->predeterminado=='1')
                  	    <input class="mediopago form-control"  style="font-size:16pt;font-weight:bold;"  id="predeterminado_{{$mp->predeterminado}}" data-predeterminado="{{$mp->predeterminado}}" name="monto[]" type="number" step="any" value="{{$pedido->total}}">
                  @else
                  	     <input class="mediopago form-control"  style="font-size:16pt;font-weight:bold;"  id="predeterminado_{{$mp->predeterminado}}" data-predeterminado="{{$mp->predeterminado}}" name="monto[]" type="number" step="any">
                  @endif
             
                  <input class="form-control" style="font-size:16pt;font-weight:bold;" name="medio[]" type="hidden" value="{{$mp->id_med_pag}}">
                </div>
                  
              </div>
            @endforeach
          
    
                <div class="col-lg-3" hidden="hidden">
            <div class="form-group form-group-sm">
              <select name="cuen_ban_id" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                <option></option>
                @foreach($bancos as $banco)
                  <option value="{{$banco->cuen_ban_id}}">{{strtoupper($banco->ban_nom)}} - CUENTA {{strtoupper($banco->tip_cuen_nom)}} {{strtoupper($banco->monnom)}} {{strtoupper($banco->cuen_ban_num)}}</option>
                @endforeach
              </select>
            </div>
          </div>
          <div class="col-lg-3" hidden="hidden">
            <div class="form-group form-group-sm">
              <input class="form-control" type="text" name="operacion" id="operacion" placeholder="Vouche - Operaci&oacute;n">
            </div>

          </div>
         

        </div>
      
        <br>
        <div class="row">
          <div class="col-lg-12">
            <div class="btn-toolbar" role="toolbar" aria-label="...">
              <div class="btn-group">
                <button type="button" id="btnRegComp" class="botones btn btn-block btn-success btn-lg">COBRAR</button><center><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload1" id="imgload1"></center>
              </div>
           
              <div class="btn-group" >
                <a href="/ingresovehiculo"><button type="button" class=" btn btn-block btn-danger btn-lg botones" >SALIR</button></a>
              </div>

               <div class="btn-group">
                <a href="" data-target="#modal-eliminar-{{$pedido->ped_id}}" data-toggle="modal"><button type="button" id="btnRegComp" class="botones btn btn-block btn-danger btn-lg">ELIMINAR</button></a>
              </div>
            </div>
          </div>
        </div>


          </div>
        </div>
      </div>
    </div>
  </div>

 {!!Form::close()!!}
 @include('valetparking.modaleliminaringreso')
</div>


@endsection
