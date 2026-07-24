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
          $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
         
          $.ajax({
            type: "POST",
            dataType: 'json',
            url: '/registrarcotizacion',
            data: formulario,
          }).done(function(respuesta){


            window.location.href = "/indexcotizaciones";

          $("#imgload").hide();
        
          });

          
          
        });



      $("#btnAgregar").on('click',function(){
         var id = $('#buscarproducto option:selected').attr('data-producto');
          var precio = $('#buscarproducto option:selected').attr('data-precio');
          var unidad = $('#buscarproducto option:selected').attr('data-unidad');
          var detalle = $('#buscarproducto option:selected').attr('data-detalle');
                    
          $('#grdet').append("<tr><td hidden='hidden'><input type='text' class='form-control' name='IdProducto[]'  value='"+id+"' readonly='readonly' style='width:20px' ></td><td><input type='text' class='form-control input-sm' name='prod_nom[]' value='"+detalle+"' style='width:600px' readonly='readonly'></td><td width='60'> <input type='number' step='any' value='1' name='cantidad[]' onChange='Calcular(this);' onkeyup='Calcular(this);' class='form-control input-sm' id='font-size' style='width:60px'> </td><td width='60'><input type='text' class='form-control input-sm' name='unidad[]' value='"+unidad+"' style='width:60px' readonly='readonly'></td><td width='60'><input  type='number' step='any' class='form-control input-sm' name='precio[]' onkeyup='Calcular(this);'  value='"+precio+"'  style='width:130px' ></td><td width='60'><input type='text' class='form-control input-sm' name='totalitem[]'  value='"+precio+"' readonly='readonly' style='width:130px' ></td><td width='60'><input  type='number' step='any' class='form-control input-sm' name='descuento[]' onkeyup='Calcular(this);'  value='0.00'  style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
            
            calculartotal();

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


    if ($('#grdet >tbody >tr').length == 0){
      $('#alertitem').show();
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
   $("#btnPrint").printPage({

    url: "/imprimir/"+comprobante+"/"+documento,
    attr: "href",
    messageBox:false

  })

   $('#nota').attr('checked', 'checked');
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
    $('#clinum').val('00000000');
    $('#clinom').val('Varios');
    $("#tdicod").val('1');
    $('#tdocod').val('13');
  }

  if($('#boleta').is(':checked')){
    $('#clinum').val('00000000');
    $('#clinom').val('Varios');
    $("#tdicod").val('1');
    $('#tdocod').val('01');
  }


  if($('#factura').is(':checked')){
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
          $('#moncod').val('1');
          $('#key').val('0.00');

        }

        if($('#dolares').is(':checked')){
          $('#key').prop('disabled',false);
          $('#moncod').val('2');
        }

        $("#soles").on('change', function (){

         if($('#soles').is(':checked')){
          $('#key').prop('disabled',true);
          $('#moncod').val('1');
          $('#key').val('0.00');
        }

      })


        $("#dolares").on('change', function (){

         if($('#dolares').is(':checked')){
          $('#key').prop('disabled',false);
          $('#moncod').val('2');
        }

      })


        $("#factura").on('change', function (){

         if($('#factura').is(':checked')){
          $('#clinum').val('');
          $('#clinom').val('');
          $("#tdicod").val('6');
          $('#tdocod').val('01');
        }

      })

        $("#boleta").on('change', function (){
          if($('#boleta').is(':checked')){
            $('#clinum').val('00000000');
            $('#clinom').val('Varios');
            $("#tdicod").val('1');
            $('#tdocod').val('03');
          }
        })

        $("#nota").on('change', function (){
          if($('#nota').is(':checked')){
            $('#clinum').val('00000000');
            $('#clinom').val('Varios');
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
    url: "/consultarmenu/"+val,

  }).done(function(respuesta){
    $("#detmenu").html(respuesta.vista);
  });

}

$(function(){
  $('#key').keyboard();
});


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


    totitemgrav = $(this).find("td:eq(2) > input").val() * $(this).find("td:eq(4) > input").val() - (  ($(this).find("td:eq(2) > input").val() * $(this).find("td:eq(4) > input").val())*$(this).find("td:eq(6) > input").val()/100 );
    $(this).find("td:eq(5) > input").val(totitemgrav.toFixed(2));

  

  });

  calculartotal();

};

function  buscarcliente(){


  var formulario = $("#clinum").val();
  $("#imgload").show();
  $(".botones").hide();
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


function calculartotal(){

 var totigv = 0,totgrav=0 ,subtotal=0,totalgeneral=0;
 var descuento = $("#descuento").val();

 $("#grdet tbody tr").each(function(){


  totgrav = totgrav + parseFloat($(this).find("td:eq(5)  > input").val());

  subtotal = subtotal + parseFloat($(this).find("td:eq(5) > input").val()/(1.18));

  totigv = totgrav - subtotal;


})

 $('#total').val(totgrav.toFixed(2));
 

 if ($('#grdet >tbody >tr').length == 0){
   $('#descuento').val('0.00');
   $('#totalgeneral').val('0.00');
   $('#total').val('0.00');
   $('#efectivo1').val('0.00');
   $('#igv').val('0.00');
   $('#subtotal').val('0.00');
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
  
  {!!Form::open(array('url'=>'/registrarcotizacion','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
  {{Form::token()}}
  <div class="row">
    <div class="col-lg-12">

      <div class="box">
       <div class="box-header with-border" style="background:gray">
        <font size="2" color="white"><strong><center>COTIZACION</center></strong></font>
      </div>

      <div class="box-body">
        <div class="row">
       
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Fecha </label>
              <input  type="date" id="fecha" name="fecha" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
            </div>
          </div>
          <div class="col-lg-3">
           <label>Condici&oacute;n Pago</label>
           <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="condicionpago" >
           @foreach($condiciones as $condicion)
         
            <option value="{{$condicion->cre_dia_id}}">{{$condicion->cre_dia_nom}}</option>
         
            
            @endforeach
          </select>
       
      </div>
       <div class="col-lg-3">
           <label>Moneda</label>
           <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="moncod" >
           @foreach($monedas as $moneda)

          
              <option value="{{$moneda->moncod}}">{{$moneda->monnom}}</option>
            

            @endforeach
          </select>
       
      </div>
          
        </div>
     
      </div>
    </div>

      <div class="box">
       <div class="box-header with-border" style="background:gray">
        <font size="2" color="white"><strong><center>DATOS DEL VEHICULO</center></strong></font>
      </div>

      <div class="box-body">
        <div class="row">
       
          
        </div><br>
        <div class="row">
         
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Placa</label>
              <input  type="text" id="placa" name="placa" value="{{$vehiculos->placa}}" class="form-control">
            </div>
          </div>
            <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="marca">Marca</label>
                <select name="marca" class="form-control">
                  <option></option>
                    @foreach($marcas as $marca)
                    @if($marca->mar_id == $vehiculos->mar_id)
                       <option selected="selected" value="{{$marca->mar_id}}">{{$marca->mar_nom}}</option>
                    @else

                       <option value="{{$marca->mar_id}}">{{$marca->mar_nom}}</option>
                    @endif
                     
                    @endforeach
                </select>
                 
           </div>
        </div>

          <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="modelo">Modelo</label>
                <select name="modelo" class="form-control">
                  <option></option>
                     @foreach($modelos as $modelo)
                     @if($modelo->mod_id == $vehiculos->mod_id)
                       <option selected="selected" value="{{$modelo->mod_id}}">{{$modelo->mod_nom}}</option>
                     @else
                       <option value="{{$modelo->mod_id}}">{{$modelo->mod_nom}}</option>
                     @endif
                     
                    @endforeach
                </select>
                 
           </div>
        </div>
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Año</label>
              <input  type="text"  id="ano" name="ano" value="{{$vehiculos->ano}}" class="form-control">
            </div>
          </div>
         <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Color</label>
              <input  type="text"  id="color" name="color" value="{{$vehiculos->color}}" class="form-control">
            </div>
          </div>
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Kil&oacute;metros</label>
              <input  type="number" step="any" id="kilometros" name="kilometros" value="{{$vehiculos->kilometros}}" class="form-control">
            </div>
          </div>
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Nivel Combustible</label>
              <select name="combustible" class="form-control">
                <option></option>
                @foreach($combustible as $comb)
                 @if($comb->comb_id == $vehiculos->comb_id)
                   <option selected="selected" value="{{$comb->comb_id}}">{{$comb->comb_nom}}</option>
                  @else
                    <option value="{{$comb->comb_id}}">{{$comb->comb_nom}}</option>
                  @endif
                @endforeach
               
              </select>
            </div>
          </div>
         
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Cilindrada</label>
              <input  type="text" id="cilindrada" name="cilindrada" value="{{$vehiculos->cilindrada}}" class="form-control">
            </div>
          </div>
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>N° Bastidor</label>
              <input  type="text" id="bastidor" name="bastidor" value="{{$vehiculos->bastidor}}"  class="form-control">
            </div>
          </div>
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Entra con grua</label>
              <select name="grua" class="form-control">
                <option value="NO">NO</option>
                <option value="SI">SI</option>
              </select>
            </div>
          </div>
           <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>Recibido por</label>
              <select name="tecnico" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                <option></option>
                @foreach($tecnicos as $tecnico)
                  <option value="{{$tecnico->tec_id}}">{{$tecnico->tecnom}}</option>
                @endforeach
                
              </select>
            </div>
          </div>
          </div>
           <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecinspeccion">Inspecci&oacute;n T&eacute;cnica Vigente hasta:</label>
                <input type="date" name="fecinspeccion" value="{{$vehiculos->fecinspeccion}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecsoat">SOAT Vigente hasta:</label>
                <input type="date" name="fecsoat" value="{{$vehiculos->fecsoat}}" class="form-control" placeholder="">
                  
           </div>
        </div>
         <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="fecrevision">Pr&oacute;xima Revisi&oacute;n en taller:</label>
                <input type="date" name="fecrevision" value="{{$vehiculos->fecrevision}}" class="form-control" placeholder="">
                  
           </div>
        </div>
    
    
      
        </div>
         
          <div class="row">
            <div class="col-lg-12">
            <div class="form-group form-group-sm">
              <label>Observaciones / Fallas </label>
             <textarea type="text" name="Observaciones"  class="form-control" rows="4"></textarea> 
            </div>
          </div>
       
        </div>
        <div class="row">
            <div class="col-lg-6">
            <div class="form-group form-group-sm">
              <label>Persona que trae el vehículo</label>
             <input type="text" name="encargado"  class="form-control">
            </div>
          </div>
          <div class="col-lg-2">
            <div class="form-group form-group-sm">
              <label>T&eacutelefono</label>
             <input type="text" name="encargadotel" class="form-control">
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="box">
     <div class="box-header with-border" style="background:gray">
      <font size="2" color="white"><strong><center>DATOS DEL CLIENTE</center></strong></font>
    </div>

    <div class="box-body">
     
      <div class="row">
       <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
              <label>Tipo Documento</label>
              <select name="tdicod" id="tdicod" class="form-control">
                @foreach($tipodocumento as $doc)
                  @if($doc->tdicod == $vehiculos->tdicod)
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
          <label for="clinum">N&deg; Doc.</label><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload">
          <input type="text"  name="clinum" id="clinum" value="{{$vehiculos->clinum}}" onKeypress="if(event.keyCode == 13) buscarcliente();" placeholder="" class="form-control" >

        </div>
      </div>

      <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
          <label>Raz&oacute;n Social</label>
          <input type="text" name="clinom" id="clinom" value="{{$vehiculos->clinom}}" class="form-control">

        </div>
      </div>
      <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
          <label>Direcci&oacute;n</label>
          <input name="clidir" id="clidir" value="{{$vehiculos->clidir}}" class="form-control">

        </div>
      </div>
       <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
          <label>Tel&eacute;fono</label>
          <input name="telefono" id="telefono" value="{{$vehiculos->clitel}}" class="form-control">

        </div>
      </div>
      <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
          <label>Correo Electr&oacute;nico</label>
          <input name="clicor" id="clicor" value="{{$vehiculos->clicor}}" class="form-control">

        </div>
      </div>
       
      </div>
      <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicon">Contacto</label>
                 <input type="text" name="clicon" value="{{$vehiculos->clicon}}" class="form-control" placeholder="">
                  
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicontel">Tel&eacute;fono</label>
                <input type="text" name="clicontel" value="{{$vehiculos->clicontel}}" class="form-control" placeholder="">
                  
           </div>
        </div>
    </div>
    </div>
  </div>



      <div class="box">
        <div class="box-header with-border form-group-sm" style="background:gray">
          <font size="2" color="white">AGREGAR PRODUCTOS / SERVICIOS</font>
          
            <div class="box-tools pull-right">
        <span id="btnRegComp" type="button" class="btn btn-success btn-sm">REGISTRAR</span>
        <a href="/pos"><span class="btn btn-success btn-sm btn-danger">SALIR</span></a> 
      </div>
        </div>
        <div class="box-body">
          <div class="row">
           <!-- <div class="col-lg-3">
           <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="tarifa" placeholder="buscarproducto">
           
          </select>
        </div>-->
        
          <div class="col-lg-6">
           <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="buscarproducto" id="buscarproducto" placeholder="buscarproducto">
            @foreach($productos as $prod)
            <option  data-precio="{{$prod->propun}}" data-producto="{{$prod->IdProducto}}" data-unidad="{{$prod->umenom}}" data-detalle="{{$prod->pronom}}" value="{{$prod->IdProducto}}">{{$prod->pronom}}  | <font><strong>STOCK: {{$prod->stock}}</strong></font></option>
            @endforeach
          </select>
        </div>
        <div class="col-lg-3">
              <button id="btnAgregar" class="btn btn-sm btn-primary" type="button">Agregar</button>
            </div>
      </div>
        </div>
      </div>


  <div class="box" id="divdetalle">
   <div class="box-header with-border" style="background:gray">
    <font size="2" color="white"><strong><center>DETALLE</center></strong></font>
   
    </div>
 

  <div class="box-body">
    <div class="row">
    
   
        <div class="col-lg-12">
      <table class="table table-hover" id="grdet">
        <thead>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Unidad</th>
          <th>PU</th>
          <th>Total</th>
          <th>Descuento%</th>
        </thead>
        <tbody>
        </tbody>
      </table>
    </BR>
    <table class="table table-hover" >
      <tr>
       <!-- <th>Descuento%</th>-->
        <th>Total </th>
      </tr>
      <tr >
        <!--<th width="150px"><input type="number"  step="any"  class="form-control"  id="descuento" name="descuento" value='0.00'> </th>-->
        <th width="150px"><input type="number"  step="any"  class="form-control"  id="total" name="total" value='0.00' readonly="readonly"> </th>
        <th></th>
        <th></th>
      </tr>
    </table>
    </BR>
  </div>
    </div>
 
</div>
</div>
</div>
</div>
{!!Form::close()!!}

@endsection
