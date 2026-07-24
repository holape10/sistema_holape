@extends('layouts.empresas')
@section('contenido')
@include('empresas.puntosventas.eliminarpedido')

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

	  $('#clinum').val('00000000');
            $('#clinom').val('Varios');
    
    $("#clinom").autocomplete({
      source: '{!!URL::route('autocompletenom')!!}',
      dataType: "json",
      minLength: 2,
      autoFocus:true,
      select: function(event,ui) {   
        
        $('#clinum').val(ui.item.clinum);
        $('#clidir').val(ui.item.dir);
        $('#clicor').val(ui.item.cor);
        $('#clicod').val(ui.item.clicod);
        $('#telefono').val(ui.item.telefono);
        $("#tdicod").val(ui.item.tdicod).attr('selected', 'selected');
        
      }
    })

    $("#btnRegComp").on("click", function() {


        if ($('#grdet >tbody >tr').length == 0){
              $('#alertitem').show();
              event.preventDefault(); 
          }

          var formulario = $("#formfact").serializeArray();
          $("#imgload").show();
          $(".botones").hide();
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/restaurant',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/mostrarmesas/"+respuesta.id_ped+"/cobrar";

  
          $("#imgload").hide();
     
          });

          
          
        });


       $("#btnRegCompReg").on("click", function() {

        $("#opcion").val('1');

        if ($('#grdet >tbody >tr').length == 0){
              $('#alertitem').show();
              event.preventDefault(); 
          }

          var formulario = $("#formfact").serializeArray();
          $("#imgload").show();
          $(".botones").hide();
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/restaurant',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/mostrarmesas/"+respuesta.id_ped+"/cobrar";

  
          $("#imgload").hide();
     
          });

          
          
        });



   


      $("#buscarproducto").keyup(function() {
            var val = $(this).val();
            var contarcarateres = $(this).val().length;

            if(contarcarateres >0){
                $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/busquedaproducto/"+val,

                }).done(function(respuesta){
                $("#detmenu").html(respuesta.vista);
               
                });
            }
          

        });

    $("#buscarproducto").keypress(function(e) {
       
      var code = (e.keyCode ? e.keyCode : e.which);
        if(code==13){



                  var valor = $(this).val();
                  var cont = 0, cantidad=0,total=0;
                   $.ajax({
                        type: 'get',
                        url: '/consultarprod',
                        dataType: 'json',
                        data: {'value' : $(this).val() },
                        success : function(data) {
                         
                         if(data[0].contar =='1'){


                         $("#buscarproducto").val('');

                          if ($('#grdet >tbody >tr').length > 0){

                              $("#grdet tbody tr").each(function(){
                                 var codigo = $(this).find("td:eq(3) > input").val();
                                 if( valor == codigo){
                                    cont = cont+1;
                                    cantidad = parseFloat($(this).find("td:eq(0) > input").val())+1;
                                    totalitem = parseFloat($(this).find("td:eq(5) > input").val())*cantidad;
                                    subtotalitem = totalitem/1.1055;
                                    igvitem = totalitem-subtotalitem;
                                    presigv = subtotalitem/cantidad;
                                    
                                 }
                                if(cont >0){
                                        $(this).find("td:eq(1) > input").val(cantidad);
                                       $(this).find("td:eq(3) > input").val(presigv.toFixed(2));
                                  
                                      $(this).find("td:eq(5) > input").val(totalitem.toFixed(2));
                                      calculartotal();
                                      $("#buscarproducto").focus();
                                      return false;
                                  }
                              })

                                  if(cont == 0){
                                      var igvitem = data[0].propun -data[0].provun;

                                       $('#grdet').append("<tr><td><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm keyboard' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' onkeyup='Calcular(this);'   value='"+data[0].propun+"'  style='width:130px' ></td><td><input type='text' class='form-control' name='itemtotal[]'  value='"+data[0].propun+"' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

                                     
                                     }

                                }else{

                                    var igvitem = data[0].propun -data[0].provun;
                                        $('#grdet').append("<tr><td><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm keyboard' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' onkeyup='Calcular(this);'   value='"+data[0].propun+"' style='width:130px' ></td><td><input type='text' class='form-control' name='itemtotal[]'  value='"+data[0].propun+"' readonly='readonly' style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
                                }

                        

                            if ($('#grdet >tbody >tr').length > 0){
                                 calculartotal();
                                 $("#buscarproducto").val('');
                                 $("#buscarproducto").focus();
                            }

                        }

                          }

                    })
                  
        
                
                    }
    });

     $("#formfact").keypress(function(e) {
            if (e.which == 13) {
                return false;
            }
        })


          if ($('#grdet >tbody >tr').length == 0){
              $('#alertitem').show();
              event.preventDefault(); 
          }

      $( "#formfact" ).submit(function( event ) {
          var efectivo1=0,visa=0,mastercard=0,totventa=0,sumarTot=0,resta=0;
          efectivo1 = $("#efectivo1").val();
          visa = $("#visa").val();
          mastercard = $("#mastercard").val();
          totventa = $("#total").val();
          sumarTot =  parseFloat(efectivo1) +  parseFloat(visa) +  parseFloat(mastercard);
          resta = totventa - sumarTot;
      
          if(sumarTot < totventa){
            alert('Falta Pagar '+resta);
            event.preventDefault();
          }

     
          if ($('#grdet >tbody >tr').length > 0){
            $('#alertitem').hide();
          }
          
          var condet = 0,conpro=0,concant=0;
          $('#grdet >tbody >tr').each(function(){
              var det = $(this).find("td:eq(0) > input").val();
               // var pro = $(this).find("td:eq(0) > input").val();
              var cant = $(this).find("td:eq(1) > input").val();
              /*  if(pro==''){
                    conpro++;
                }else */if(det==''){
                    condet++
                }else if(cant<1){
                    concant++
                }
            })

            /*if(conpro>0){
                $('.alertpro').show(); 
                event.preventDefault();  
            }else{
                $('#alertpro').hide();
            }   */

            if(condet>0) {
                $('.alertdet').show();
                event.preventDefault();   
            }else{
                $('#alertdet').hide(); 
            }

            if(concant>0){
                $('.alertcant').show();
                event.preventDefault(); 
            }else{
                $('#alertcant').hide(); 
            }

          
            /*if($('#mondoc').val()!='1' && $('#camdoc').val()<=0 ){
              $('#error-camdoc').show();
        
              event.preventDefault(); 
            }*/
                       
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
           
            $("#tdicod").val('1');
             $('#tdocod').val('03');
      }


      if($('#factura').is(':checked')){
      
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
             
            $("#tdicod").val('6');
            $('#tdocod').val('01');
          }

      })

      $("#boleta").on('change', function (){
          if($('#boleta').is(':checked')){
           
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
          $("#imgloadcli").show();
  
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

          $("#imgloadcli").hide();
          //$(".botones").show();
          
          });

  

}


function imprimircuenta(pedido){

    var val = pedido;
    $.ajax({
      type: "GET",
      dataType: 'json',
      url: "/imprimircuenta/"+val,
    }).done(function(respuesta){
        
        if(respuesta.validar==false){

          var idpedido =respuesta.pedido;

          var url = '/imprimircuentaweb/'+idpedido;
          window.open(url, '_blank');
    

        }
               
    });
    
}


 

  function deleteRow(btn) {
      var row = btn.parentNode.parentNode;
      row.parentNode.removeChild(row);
      calculartotal();
    };

    function Calcular(ele) {

    var totigv = 0,totgrav=0 ,subtotal=0;
    var tr = ele.parentNode.parentNode;

    $(tr).each(function() {

        var  totitemgrav=0;

          totitemgrav = $(this).find("td:eq(1) > input").val() * $(this).find("td:eq(4) > input").val();
          $(this).find("td:eq(5) > input").val(totitemgrav.toFixed(2));

     });
    calculartotal();

  };

  function calculartotal(){

   var totigv = 0,totgrav=0 ,subtotal=0;

    $("#grdet tbody tr").each(function(){

        totgrav = totgrav + ($(this).find("td:eq(1) > input").val() *parseFloat($(this).find("td:eq(4)  > input").val()));

        subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

        totigv = totgrav - subtotal;

        $('#total').val(totgrav.toFixed(2));
    $('#efectivo1').val(totgrav.toFixed(2));
       $('#igv').val(totigv.toFixed(2));
       $('#subtotal').val(subtotal.toFixed(2));
    })


     if ($('#grdet >tbody >tr').length == 0){
        $('#total').val('0.00');
    $('#efectivo1').val('0.00');
       $('#igv').val('0.00');
       $('#subtotal').val('0.00');
     }

       var pago =  $('#pagar').val();
     var vuelto = pago - totgrav;
     if(pago=='0.00' || pago=='0' || pago==''){
           $('#vuelto').val(0.00);
     }else{
           $('#vuelto').val(vuelto.toFixed(2));
     }

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
 <div class="row">

  <div class="col-lg-6">
    <div class="box">
      <div class="box-header with-border">
        <h2 class="box-title">Pedido {{$mesas->mes_nom}}</h2><BR>
        <div class="col-lg-12">
          <div class="form-group form-group-sm">
            <label>MOZO</label>
            <select class="form-control" name="mozo">
              @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
                <option></option>
                @foreach($mozos as $mozo)
              		@if($pedido->mozo == $mozo->IdUsuario)
              		 <option selected="selected" value="{{$mozo->IdUsuario}}">{{$mozo->name}} {{$mozo->apeusu}}</option>
                	@else
    				        <option value="{{$mozo->IdUsuario}}">{{$mozo->name}} {{$mozo->apeusu}}</option>
    					    @endif
                @endforeach
              @else
                <option></option>
              @endif
            </select>
          </div>
        </div>

        <div class="box-body">
          {!!Form::open(array('url'=>'/restaurant','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
          {{Form::token()}}

          <input type="hidden" name="opcion" id="opcion" value="0">
          <input type="hidden" name="txtMesaId" value="{{$mesas->mes_id}}">
          <input type="hidden" name="txtPedId" id="txtPedId" value="{{$totales->ped_id}}">
          <a class="btnPrint" ><button type="button" hidden="hidden" id="imprimir" class="btnPrint" value="imprimir"></button></a>
          <input type="hidden" readonly="readonly" value="{{$id_ped}}" name="pedido" id="pedido">
      
          <div class="row">
                <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
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
                <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" style="display:none;" class="form-control">
                <input  type="hidden" readonly="readonly" id="moncod" name="moncod" class="form-control">
                <input type="date" name="fecVen" value="{{Carbon::now()->format('Y-m-d')}}" style="display:none;" class="form-control">

                <div class="col-lg-3 col-md-2 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label for="clinum">Num. Doc</label><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgloadcli" id="imgloadcli">
                        <input type="text" required="required" name="clinum" id="clinum" value="{{old('clinum')}}"  value="{{old('clinum')}}" onKeypress="if(event.keyCode == 13) buscarcliente();" placeholder="" class="form-control" >
                    </div>
                </div>

                <div class="col-lg-5 col-md-6 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                        <label>Raz&oacute;n Social</label>
                        <input type="text" name="clinom" id="clinom" required="required" value="{{old('clinom')}}" class="form-control">
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

                <div class="col-lg-3">
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                      <label  >
                        <input type="radio" name="tdocod" id="boleta" value="03" autocomplete="off"  checked="checked"> BO
                      </label>
                      <label  >
                        <input type="radio" name="tdocod" id="factura" value="01" autocomplete="off"> FA
                      </label>
                      <label>
                        <input type="radio" name="tdocod" id="nota" value="13" autocomplete="off"> NV
                      </label>
                  </div>
                </div>

                <div  style="display:none" class="col-lg-3">
                  <div class="form-group form-group-sm">
                    <select name="tipoventa" class="form-control">
                        <option value='0'>Venta</option>
                         <option value='1'>Venta Interna</option>
                    </select>
                  </div>
                </div>

                <div style="display:none" class="col-lg-3">
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn btn-primary">
                      <input type="radio" name="rdbmon" id="soles"  autocomplete="off" checked> SOL
                    </label>
                    <label class="btn btn-success">
                      <input type="radio" name="rdbmon" id="dolares" autocomplete="off"> DOL
                    </label>
                  </div>
                </div>
               
                <div style="display:none" class="col-lg-2">
                  <div class="form-group">
                      <input type="text" name="camdoc" class="form-control" id="key" placeholder="Tip. Cambio">
                  </div>
                </div>
          </div>

          <div class="row">
            <BR>
            <table class="table table-hover" id="grdet">
              <thead>
                <th>Producto</th>
                <th>Cantidad</th>
                <th hidden="hidden">Unidad</th>
                <th hidden="hidden">VU</th>
                <th>PU</th>
                <th>Total</th>
              </thead>
              <tbody>
              @foreach($pedidos as $pedido)
                <tr>
                  <td>
                    <input type='text' class='form-control input-sm' name='pronom[]' value='{{$pedido->pronom}}' readonly='readonly'>
                  </td>
                  <td>
                    <input type='text' value='{{$pedido->cantidad}}' name='cant[]' onChange='Calcular(this);' onkeyup="Calcular(this);"  class='form-control input-sm keyboard' id='font-size' style='width:60px'>
                  </td>
                  <td hidden="hidden">
                    <select style='width:100px' name='unid[]'  class='form-control input-sm'>
                      @foreach($unidades as $und)
                      @if($und->umecod == $pedido->unidad)
                      <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option>
                       @else
                      <option  value='{{$und->umecod}}'>{{$und->umenom}}</option>
                      @endif @endforeach
                    </select>
                  </td>
                  <td hidden='hidden'>
                      <input type='text' class='form-control input-sm' name='provun[]'  value='{{$pedido->provunitem}}' readonly='readonly' style='width:130px' >
                  </td>
                  <td>
                    <input type='text' class='form-control input-sm' name='propun[]' onChange='Calcular(this);' onkeyup="Calcular(this);"   value='{{$pedido->propunitem}}'  style='width:130px' >
                  </td>
                  <td>
                    <input type='text' class='form-control input-sm' name='itemtotal[]'  value='{{$pedido->totalitem}}' readonly='readonly' style='width:130px' >
                  </td>
                  <td hidden='hidden'>
                    <input type='text' class='form-control input-sm' name='proid[]'  value='{{$pedido->IdProducto}}' readonly='readonly' style='width:130px' >
                  </td>
                  <td>
                    <button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button>
                  </td>
                </tr>
              @endforeach
              </tbody>
            </table>

              <BR>
              <table class="table table-hover" >
                <tr>
                  <th hidden="hidden">Sub Total </th>
                  <th hidden="hidden">IGV </th>
                  <th>Total </th>
                  <th>Paga con: </th>
                  <th>Vuelto </th>
                </tr>
                <tr>
                  <th hidden="hidden"><input type="text" class="form-control"  id="subtotal" name="subtotal" value='@if(isset($totales->subtotal)){{$totales->subtotal}}@else 0.00 @endif' readonly="readonly"> </th>
                  <th hidden="hidden"><input type="text" class="form-control"  id="igv" name="igv" value='@if(isset($totales->igv)){{$totales->igv}}@else 0.00 @endif' readonly="readonly"> </th>
                  <th><input type="text" class="form-control"  id="total" name="total" value='@if(isset($totales->total)){{$totales->total}}@else 0.00 @endif' readonly="readonly"> </th>
                  <th><input type="number"  step="any" class="form-control"  id="pagar" name="pagar" value="0.00" onkeyup="calculartotal();"> </th>
                  <th><input type="text" class="form-control"  id="vuelto" name="vuelto" value="0.00" readonly="readonly"> </th>
                </tr>
                <tr>
                  <th>Efectivo </th>
                  <th>Visa </th>
                  <th>Mastercard </th>
                </tr>
                <tr>
                  <th><input type="number"  step="any" class="form-control"  value="{{$totales->total}}" id="efectivo1" name="efectivo1" placeholder="Efectivo"></th>
                  <th><input type="number"  step="any" class="form-control"  value="0.00" id="visa" name="visa"  placeholder="Visa"> </th>
                  <th><input type="number"  step="any" class="form-control"  value="0.00" id="mastercard" name="mastercard" placeholder="Mastercard"></th>
                </tr>
              </table>
             
              <BR>
              <table class="table ">
                <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
                <tr>
                    <td colspan="2"><button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-lg botones">COBRAR</button></td>
                    <td colspan="2"><button type="button" id="btnRegCompReg" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button></td>
                </tr>
                <tr>
                  <td colspan="2"><a href="/mesas"><button type="button" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a></td>
                  <td colspan="2"><a href="/imprimircuenta/{{$id_ped}}" target="_blank" ><button type="button"  id="precuenta" class=" btn btn-block btn-default btn-lg botones">PRE CUENTA</button></a></td>
                </tr>
                <tr hidden="hidden">
                  <td colspan="4"><a href="" data-target="#modal-delete-{{$totales->ped_id}}" data-toggle="modal"><button type="button" class=" btn btn-block btn-danger btn-lg botones" >ELIMINAR PEDIDO</button></a></td>
                </tr>
              </table>
            </div>
          {!!Form::close()!!}
    </div>
  </div>
</div>
</div>



    <div class="col-lg-6">
      <div class="box">
            <div class="box-header with-border form-group-sm">
              <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras o descripcion">
            </div>
            <div class="box-body" id="detmenu"  style="min-height:770px;min-width:500px">
              <?php $i=0; ?>
              @foreach($categorias as $categoria)
                <?php $i=$i+1; ?>
                <div class="col-lg-3 col-md-3 col-sm-2 col-xs-4">
                  <button id='cat<?php echo $i; ?>' value='{{$categoria->cat_id}}' onclick="mostrar(this)" style="background:{{$categoria->color}};width: 120px; height: 120px; border-radius:10px">
                  <p><strong><font color="white">{{$categoria->cat_nom}}</font></strong></p>
                  </button><br><br>
                </div>
              @endforeach
            </div>
        </div>
    </div>
  </div>
</div>

@endsection
