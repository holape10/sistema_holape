@extends('layouts.empresas')
@section('contenido')
@include('empresas.clientes.modalcrearcliente')
@include('empresas.puntosventas.modalingresarcantidadprecio')
@include('empresas.taller_electrodomesticos.modalequipos')

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



    $("#can_producto").keypress(function(e){
     var code = (e.keyCode ? e.keyCode : e.which);
     if(code==13){

      $("#pre_producto").focus();
      $("#pre_producto").select();
    }



  });


    $("#pre_producto").keypress(function(e){
     var code = (e.keyCode ? e.keyCode : e.which);
     if(code==13){

      agregaritem();
      //  $("#modal-cantidad-precio").modal("hide");
    }

  });

    $("#btnAgregarLista").click(function(e){


      agregaritem();
       // $("#modal-cantidad-precio").modal("hide");

     });



    $("#btnRegComp").on("click", function() {
      var formulario = $("#formfact").serializeArray();
       $("#imgload").show();
      $(".botones").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrarordencompu',
        data: formulario,
      }).done(function(respuesta){


        window.location.href = "/ordenescompu/"+respuesta.codfact;

        $("#imgload").hide();
        $(".botones").show();
        
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

  $('#modal-cantidad-precio').on('shown.bs.modal', function() { $("#can_producto").focus(); })
  $('#modal-presentaciones').on('shown.bs.modal', function() { $("#table-presentaciones .btn:first").focus(); })

  $("#modal-cantidad-precio").on('hidden.bs.modal', function () {
   actualizarpro();
 });


  $("#modal-cantidad-precio").on('hidden.bs.modal', function () {
   actualizarpro();
 });

  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');



  
  $("#producto").select2( {


    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
      url: "{{route('Productos.consultarproductos')}}",
      dataType: 'json',
      type: "POST",
      quietMillis: 50,
      data: function (params) {

       var id_almacen = $("#id_almacen").val();

       return {
        _token : CSRF_TOKEN,
        search: params.term,
        almacen: id_almacen,
      };
    },
    processResults: function (response) {


      return {
        results: $.map(response, function(response){


          return {
            "text": response.text,
            "id": response.id,
            "pro_rel": response.pro_rel,
            "presentacion": response.contar,
            "propun": response.propun,
            "unidad": response.unidad,
            "producto": response.producto,
            "id_almacen_pro":response.id_almacen,
            "pro_cod":response.codigo
          }


        })


      };



    },
    cache:false
  }

});

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




function deleteRow(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
  calculartotal();
};


function Calcular(ele) {

  var totigv = 0,totgrav=0 ,subtotal=0;
  var tr = ele.parentNode.parentNode;
  var tipo_desc = $("#tipo_desc").val();

  $(tr).each(function() {

    var  totitemgrav=0;
    var descuento =0;
    var preuni =0;
    var val_desc = $(this).find("td:eq(5) > input").val();

    $(this).find("td:eq(7) > input").val( $(this).find("td:eq(4) > input").val() );
    //calcular descuento

    if(val_desc>0){

      if(tipo_desc =='1'){
        descuento = $(this).find("td:eq(5) > input").val();
      }


      if(tipo_desc =='2'){
        descuento = ($(this).find("td:eq(7) > input").val()*(val_desc/100));
      }

      preuni =  ($(this).find("td:eq(7) > input").val()-descuento);

      //$(this).find("td:eq(3) > input").val(preuni);

    }else{

     preuni =  $(this).find("td:eq(4) > input").val();
   }


   

    //total item
    totitemgrav = $(this).find("td:eq(2) > input").val() * preuni;
    

    $(this).find("td:eq(6) > input").val(totitemgrav.toFixed(2));



  });

  calculartotal();

  

};

function  buscarclientetaller(){


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




function calculartotal(){

 var totigv = 0,totgrav=0 ,subtotal=0;

 $("#grdet tbody tr").each(function(){

  totgrav = totgrav + parseFloat($(this).find("td:eq(6)  > input").val());

//  subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

//  totigv = totgrav - subtotal;

$('#total').val(totgrav.toFixed(2));
$('#predeterminado_1').val(totgrav.toFixed(2));
 // $('#igv').val(totigv.toFixed(2));
 // $('#subtotal').val(subtotal.toFixed(2));
})


 if ($('#grdet >tbody >tr').length == 0){
  $('#total').val('0.00');
  $('#igv').val('0.00');
  $('#subtotal').val('0.00');
  $('#vuelto').val('0.00');
  $('#total').val('0.00');
}



var pago =  $('#pagar').val();
var vuelto = pago - totgrav;
if(pago=='0.00' || pago=='0' || pago==''){
 $('#vuelto').val(0.00);
}else{
 $('#vuelto').val(vuelto.toFixed(2));
}

};




function ingresar_cantidad_precio_presentacion(button){


 var id = button.id;
 var precio = button.value;
 var producto = $('#'+id+'nom').val();
 var proid = $('#'+id+'id').val();
 var unidad= $('#'+id+'unidad').val();



        //$("#modal-cantidad-precio").modal("show");

        $('#modal-cantidad-precio').modal('show'); 
        $('#modal-cantidad-precio').on('shown', function(){ 
         $("#can_producto").focus();


       })



        $("#des_producto").val(producto);
        $("#id_producto").val(proid);
        $("#pre_producto").val(precio);
        $("#pre_producto_ref").val(precio);
        $("#uni_producto").val(unidad);

        $("#can_producto").select();


        $("#modal-presentaciones").modal("hide");






      }


      function ingresar_cantidad_precio(){

        var producto = $('#producto').select2('data')[0].producto;
        var precio =  $('#producto').select2('data')[0].propun;

        var proid =  $('#producto').select2('data')[0].id;
        var unidad =  $('#producto').select2('data')[0].unidad;
        var pro_rel = $('#producto').select2('data')[0].pro_rel;
        var contar = $('#producto').select2('data')[0].presentacion;
        var codigo = $('#producto').select2('data')[0].presentacion;
        var cod_producto = $('#producto').select2('data')[0].pro_cod;

        if(contar>0){

          presentaciones(proid);

          $("#modal-presentaciones").modal("show");

        }else{

        //$("#modal-cantidad-precio").modal("show");

        $('#modal-cantidad-precio').modal('show'); 
        $('#modal-cantidad-precio').on('shown', function(){ 
         $("#can_producto").focus();


       })


        $("#des_producto").val(producto);
        $("#id_producto").val(proid);
        $("#pre_producto").val(precio);
        $("#pre_producto_ref").val(precio);
        $("#uni_producto").val(unidad);
       // $("#id_almacen_pro").val(id_almacen_pro);
       $("#cod_producto").val(cod_producto);

       $("#can_producto").select(); 



     }


   }



   function agregaritem(){



   /* var producto = $('#producto').select2('data')[0].producto;
    var precio =  $('#producto').select2('data')[0].propun;
    var proid =  $('#producto').select2('data')[0].id;
    var  unidad =  $('#producto').select2('data')[0].unidad;
    var pro_rel = $('#producto').select2('data')[0].pro_rel;
    var contar = $('#producto').select2('data')[0].presentacion;*/

    var producto = $('#des_producto').val();
    var precio =  $('#pre_producto').val();
    var proid =  $('#id_producto').val();
    var  unidad =  $('#uni_producto').val();
    var cantidad = $('#can_producto').val();
    var id_almacen_pro = $('#id_almacen_pro').val();
    var codigo = $('#cod_producto').val();

    var total = cantidad*precio;

    var  precio_ref =  $('#pre_producto_ref').val();

    
  //  var pro_rel = $('#producto').select2('data')[0].pro_rel;
  //  var contar = $('#producto').select2('data')[0].presentacion;

  /*if(contar>0){
        presentaciones(proid);

        $("#modal-presentaciones").modal("show");
      }else{*/

        if(precio_ref===undefined){
          $('#grdet').append("<tr>"+
           "<td width='200px'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
           "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"'></td>"+
           "<td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
           "<td width='80px' ><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'></td>"+
           "<td><input  type='number' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'  style='width:80px' ></td>"+
           "<td  ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
           "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
           "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

          actualizarpro();

          calculartotal();
          $("#can_producto").val('1'); 
          $("#modal-cantidad-precio").modal("hide");


        }else{

         /* if( parseFloat(precio_ref)<= parseFloat(precio)){*/
          $('#grdet').append("<tr>"+
           "<td width=200px'><input type='text'  readonly='readonly'  class='form-control input-sm'  value='"+codigo+"'></td>"+
           "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"'></td>"+
           "<td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
           "<td width='80px'  ><input readonly='readonly'  value='"+unidad+"' name='unid[]'  class='form-control input-sm'></td>"+
           "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' readonly='readonly' style='width:80px' ></td>"+
           "<td ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
           "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly' value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
           "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

          actualizarpro();

          calculartotal();
          $("#modal-cantidad-precio").modal("hide");
          $("#can_producto").val('1'); 
 /*   }else{

       alert('No Tiene Autorizacion para Descuentos');
     }*/


   }


  //}

}

function actualizarpro(){



  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/actualizarpro/venta",

  }).done(function(respuesta){


    $("#divactpro").html(respuesta.vista);
    

    
  });


}

function seleccionarequipos(){

       id = $("#clicod").val();

       $("#modal-equipos").modal("show");

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/seleccionarequipos",

        }).done(function(respuesta){
          $("#listaequipos").html(respuesta.vista);
        });


}


function agregarequipo(id,descripcion,marca,modelo){


   $("#equi_des").val(descripcion+' '+marca+' '+modelo);
    $("#equi_id").val(id);

  $("#modal-equipos").modal("hide");
  
}

    $("#btnRegCliente").on("click", function() {


      var formulario = $("#frmcliente").serializeArray();
      $("#imgloadcliente").show();
      $(".botonescliente").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrarclientetaller',
        data: formulario,
      }).done(function(respuesta){

        $("#divcliente").html(respuesta.vista);
        $("#imgloadcliente").hide();
     
         $(".botonescliente").show();
          $('#modal-cliente').modal('toggle');
      });



    });


function  buscarcliente(){


  var formulario = $("#clinumn").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



  if(respuesta.error){
      
    
      

    alert(respuesta.error);
      $("#imgloadcliente").hide();


 
  }else{

   $('#clinomn').val(respuesta[0].nom);
   $('#clidirn').val(respuesta[0].dir);
   $('#cliteln').val(respuesta[0].telefono);
   $('#clicorn').val(respuesta[0].cor);
   $('#clicorn4').val(respuesta[0].cor4);
   $('#clicorn2').val(respuesta[0].cor2);
   $('#clicorn3').val(respuesta[0].cor3);
   $('#clicodn').val(respuesta[0].clicod);
   $("#tdicodn").val(respuesta[0].tdicod).attr('selected', 'selected');

   $("#imgloadcliente").hide();
    $(".botones").show(); 

  }
   
          
  });

  

}


 function agregarnota(button){


  $('#grdet').append("<tr>"+
     "<td hidden='hidden'><input type='text' name='id[]'  readonly='readonly'  class='form-control input-sm'  value=''></td>"+
     "<td><select   name='id_tip_equi[]'  class='form-control input-sm'>@foreach($tipo_equipo as $te)  <option  value='{{$te->id_tip_equi}}'>{{$te->nom_tip_equi}}</option> @endforeach</select></td>"+
    "<td><select   name='mar_id[]'  class='form-control input-sm'>@foreach($marcas as $mar)  <option  value='{{$mar->mar_id}}'>{{$mar->mar_nom}}</option> @endforeach</select></td>"+
      "<td><input  type='text' class='form-control input-sm' name='modelo[]'   value=''  ></td>"+
      "<td><input  type='text' class='form-control input-sm' name='serie[]'   value='' ></td>"+
      "<td><input  type='number' step='any' class='form-control input-sm' name='can_ord_equi[]'   value='1' ></td>"+
       "<td><textarea class='form-control' name='obs_ord_equi[]' rows='3'></textarea></td>"+
    "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

  

  $("#modal-presentaciones").modal("hide");

  
  }

</script>


</br>


<div class="container-fluid">
  <div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
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
        <div class="box-header" style="background-color:blue;">
         <font color="white"><strong><center>GENERAR ORDEN DE TRABAJO</center></strong></font>
       </div>

       <div class="box-body">
        <div class="row">
         <div hidden="hidden" class="col-lg-2">
          <div class="form-group form-group-sm">
            <LABEL>Almacenes</LABEL>
            <select name="id_almacen" id="id_almacen" class="form-control">
              @foreach($almacenes as $alm)
              <option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
              @endforeach
            </select>
          </div>
        </div>
    <div hidden="hidden" class="col-lg-4 col-md-2 col-sm-6 col-xs-12">
      <div class="form-group form-group-sm">
        <label for="equipo">Equipos</label>
          <div class="input-group input-group-sm">
               
            <input type="text" readonly="readonly" name="txt_equipo" id="equi_des" value=""  class="form-control">
            <span class="input-group-btn">
              <button type="button" class="btn btn-primary btn-flat" onclick="seleccionarequipos();">BUSCAR EQUIPO</button>
            </span>
          </div>
        <input type="hidden" readonly="readonly" class="form-control" name="equi_id" id="equi_id">
      </div>
    </div>


        <div hidden="hidden" class="col-lg-2">
          <div class="form-group form-group-sm">
            <label>Serie del Equipo</label>
            <input  type="text" id="equi_ser" name="equi_ser" value="" class="form-control">
          </div>
        </div>


        <div class="col-lg-2">
          <div class="form-group form-group-sm">
            <label>Fecha Registro </label>
            <input  type="date" id="fecha" name="fecha" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
          </div>
        </div>

        <div hidden="hidden" class="col-lg-2">
          <div class="form-group form-group-sm">
            <label>Fecha Compra </label>
            <input  type="date" id="fec_doc_gar" name="fec_doc_gar" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
          </div>
        </div>

         <div hidden="hidden" class="col-lg-3">
         <label>Documento Garantía</label>
         <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="tdocod_gar" >
           @foreach($comprobantes as $comprobante)

           <option value="{{$comprobante->tdocod}}">{{$comprobante->tdodes}}</option>


           @endforeach
         </select>

       </div>


        <div hidden="hidden" class="col-lg-2">
          <div class="form-group form-group-sm">
            <label>Serie-Número </label>
            <input  type="text" id="doc_gar" name="doc_gar" value="" class="form-control">
          </div>
        </div>


        <div hidden="hidden" class="col-lg-3">
         <label>Condici&oacute;n Pago</label>
         <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="condicionpago" >
           @foreach($condiciones as $condicion)

           <option value="{{$condicion->cre_dia_id}}">{{$condicion->cre_dia_nom}}</option>


           @endforeach
         </select>

       </div>
       <div hidden="hidden" class="col-lg-3">
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
  <div class="box-header" style="background-color:blue;">
   <font color="white"><strong>DATOS DEL CLIENTE</strong></font>
    <div class="box-tools pull-right">
             <a  data-target="#modal-cliente" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">NUEVO CLIENTE</button></a>
          </div>


 </div>
 <div class="box-body" id="divcliente">

  <div class="row">
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
  <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
      <label for="clinum">N&deg; Doc.</label>
      <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}" onKeypress="if(event.keyCode == 13) buscarclientetaller();" placeholder="" class="form-control" >

    </div>
  </div>

  <div class="col-lg-3 col-md-6 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
      <label>Cliente</label>
      <input type="text" name="clinom" id="clinom" value="{{old('clinom')}}" class="form-control">

    </div>
  </div>
  <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
      <label>Direcci&oacute;n</label>
      <input name="clidir" id="clidir" value="--" class="form-control">

    </div>
  </div>
  <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
      <label>Tel&eacute;fono</label>
      <input name="telefono" id="telefono" value="--" class="form-control">

    </div>
  </div>
  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
      <label>Correo Electr&oacute;nico</label>
      <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">

    </div>
  </div>

</div>
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
    <div class="form-group form-group-sm">
      <label for="clicon">Contacto</label>
      <input type="text" name="clicon" value="{{old('clicon')}}" class="form-control" placeholder="">

    </div>
  </div>
  <div class="col-lg-2 col-md-2 col-sm-6 col-xs-12">
    <div class="form-group form-group-sm">
      <label for="clicontel">Tel&eacute;fono</label>
      <input type="text" name="clicontel" value="{{old('clicontel')}}" class="form-control" placeholder="">

    </div>
  </div>
</div>
   
</div>
</div>



 <div class="box">
   <div class="box-header" style="background-color:blue;">
     <font color="white"><strong>DATOS DEL EQUIPO</strong></font>
   </div>

   <div class="box-body">
   
    <div class="row">
    

  
    <div hidden="hidden" class="col-lg-2">
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
    <div class="col-lg-12">
      <div class="form-group form-group-sm">
        <label>S&iacute;ntoma </label>
        <textarea type="text" name="observaciones"  class="form-control" rows="4"></textarea> 
      </div>
    </div>

  </div>

    <div class="row">
    <div class="col-lg-12">
      <div class="form-group form-group-sm">
        <label>Estado F&iacute;sico </label>
        <textarea type="text" name="est_fis"  class="form-control" rows="4"></textarea> 
      </div>
    </div>

  </div>

    <div class="row">
    <div class="col-lg-12">
      <div class="form-group form-group-sm">
        <label>Observaci&oacute;n Actividad </label>
        <textarea type="text" name="obs_act"  class="form-control" rows="4"></textarea> 
      </div>
    </div>

  </div>

  <div class="row">
     
        <div class="col-lg-12 table-responsive">

      <table class="table table-hover" id="grdet">
        <thead>
          <tr>
          <th hidden="hidden">Id</th>
          <th>Tipo Equipo</th>
          <th>Marca</th>
          <th>Modelo</th>
          <th>Serie</th>
          <th>Cantidad</th>
          <th>Observaci&oacute;n</th>
          <th><button type="button" onClick="agregarnota();" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
      </tr>
        </thead>

        <tbody>
          
         </tbody>
        </table>
      
 
  </div>
  </div>

  <div class="row">
       
             <div class="col-lg-2">
              <button type="button" id="btnRegComp" class=" btn btn-block btn-primary btn-sm botones">REGISTRAR</button><br>
            </div>
              <div class="col-lg-2">
              <a href="/ordenescompu"><button type="button" class=" btn btn-block btn-danger btn-sm botones">SALIR</button></a><br>
            </div>
          </div>

</div>
</div>













</div>
</div>
{!!Form::close()!!}

@endsection
