@extends('layouts.empresas')
@section('contenido')
@include('empresas.puntosventas.modalingresarcantidadprecio')
@include('empresas.puntosventas.modalingresarcantidadprecioservicio')
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


    calculartotal();
    calculartotalservicio();

    $("#can_producto").keypress(function(e){
     var code = (e.keyCode ? e.keyCode : e.which);
     if(code==13){

      $("#pre_producto").focus();
      $("#pre_producto").select();
    }



  });


    $("#can_productoserv").keypress(function(e){
     var code = (e.keyCode ? e.keyCode : e.which);
     if(code==13){

      $("#pre_productoserv").focus();
      $("#pre_productoserv").select();
    }



  });


    $("#pre_producto").keypress(function(e){
     var code = (e.keyCode ? e.keyCode : e.which);
     if(code==13){

      agregaritem();
      //  $("#modal-cantidad-precio").modal("hide");
    }

  });



    $("#pre_productoserv").keypress(function(e){
     var code = (e.keyCode ? e.keyCode : e.which);
     if(code==13){

      agregaritemservicio();
      //  $("#modal-cantidad-precio").modal("hide");
    }

  });


    $("#btnAgregarLista").click(function(e){


      agregaritem();
       // $("#modal-cantidad-precio").modal("hide");

     });



    $("#btnAgregarListaServicio").click(function(e){


      agregaritemservicio();
       // $("#modal-cantidad-precio").modal("hide");

     });


    $("#btnRegComp").on("click", function() {
      var formulario = $("#formfact").serializeArray();
      $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/actualizarotalm',
        data: formulario,
      }).done(function(respuesta){


        window.location.href = "/solicitudesot";

        $("#imgload").hide();
        
      });



    });


    $("#btnRegFin").on("click", function() {
      var formulario = $("#formfact").serializeArray();
      $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/actualizarottecfin',
        data: formulario,
      }).done(function(respuesta){


        window.location.href = "/atencionot";

        $("#imgload").hide();
        
      });



    });





    $("#btnRegRech").on("click", function() {
      var formulario = $("#formfact").serializeArray();
      $("#divdetalle").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/observarcc',
        data: formulario,
      }).done(function(respuesta){


        window.location.href = "/atencionot";

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

  $('#modal-cantidad-precio').on('shown.bs.modal', function() { $("#can_producto").focus(); })

  $('#modal-presentaciones').on('shown.bs.modal', function() { $("#table-presentaciones .btn:first").focus(); })

  $("#modal-cantidad-precio").on('hidden.bs.modal', function () {
   actualizarpro();
 });






  $('#modal-cantidad-precio-servicio').on('shown.bs.modal', function() { $("#can_productoserv").focus(); })
  

  $("#modal-cantidad-precio-servicio").on('hidden.bs.modal', function () {
   actualizarproserv();
 });



  var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');



  
  $("#producto").select2( {


    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
      url: "{{route('Productos.consultarrepuestos')}}",
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


  
  $("#servicio").select2( {


    minimumInputLength: 2,
    tags: "true",
    allowClear: true,
    ajax: {
      url: "{{route('Productos.consultarservicios')}}",
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
    calculartotalservicio();
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




function calculartotal(){

 var totigv = 0,totgrav=0 ,subtotal=0;

 $("#grdet tbody tr").each(function(){

  totgrav = totgrav + parseFloat($(this).find("td:eq(6)  > input").val());

//  subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

//  totigv = totgrav - subtotal;

$('#totalrep').val(totgrav.toFixed(2));
$('#predeterminado_1').val(totgrav.toFixed(2));
 // $('#igv').val(totigv.toFixed(2));
 // $('#subtotal').val(subtotal.toFixed(2));
})


 if ($('#grdet >tbody >tr').length == 0){
  $('#totalrep').val('0.00');
  $('#igv').val('0.00');
  $('#subtotal').val('0.00');
  $('#vuelto').val('0.00');
  $('#totalrep').val('0.00');
}



var pago =  $('#pagar').val();
var vuelto = pago - totgrav;
if(pago=='0.00' || pago=='0' || pago==''){
 $('#vuelto').val(0.00);
}else{
 $('#vuelto').val(vuelto.toFixed(2));
}


 var totalrep = $("#totalrep").val();
 var totalserv = $("#totalserv").val();
 var cal_tot = parseFloat(totalrep)+parseFloat(totalserv);

  $("#total").val(cal_tot.toFixed(2));

};




function calculartotalservicio(){

 var totigv = 0,totgrav=0 ,subtotal=0;

 $("#grdetserv tbody tr").each(function(){

  totgrav = totgrav + parseFloat($(this).find("td:eq(6)  > input").val());

//  subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

//  totigv = totgrav - subtotal;

$('#totalserv').val(totgrav.toFixed(2));
$('#predeterminado_1').val(totgrav.toFixed(2));
 // $('#igv').val(totigv.toFixed(2));
 // $('#subtotal').val(subtotal.toFixed(2));
})


 if ($('#grdetserv >tbody >tr').length == 0){
  $('#totalserv').val('0.00');
  $('#igv').val('0.00');
  $('#subtotal').val('0.00');
  $('#vuelto').val('0.00');
  $('#totalserv').val('0.00');
}



var pago =  $('#pagar').val();
var vuelto = pago - totgrav;
if(pago=='0.00' || pago=='0' || pago==''){
 $('#vuelto').val(0.00);
}else{
 $('#vuelto').val(vuelto.toFixed(2));
}

 var totalrep = $("#totalrep").val();
 var totalserv = $("#totalserv").val();
 var cal_tot = parseFloat(totalrep)+parseFloat(totalserv);

  $("#total").val(cal_tot.toFixed(2));


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


         function ingresar_cantidad_precio_servicio(){

        var producto = $('#servicio').select2('data')[0].producto;
        var precio =  $('#servicio').select2('data')[0].propun;

        var proid =  $('#servicio').select2('data')[0].id;
        var unidad =  $('#servicio').select2('data')[0].unidad;
        var pro_rel = $('#servicio').select2('data')[0].pro_rel;
        var contar = $('#servicio').select2('data')[0].presentacion;
        var codigo = $('#servicio').select2('data')[0].presentacion;
        var cod_producto = $('#servicio').select2('data')[0].pro_cod;

        if(contar>0){

          presentaciones(proid);

          $("#modal-presentaciones").modal("show");

        }else{

        //$("#modal-cantidad-precio").modal("show");

        $('#modal-cantidad-precio-servicio').modal('show'); 
        $('#modal-cantidad-precio-servicio').on('shown', function(){ 
         $("#can_productoserv").focus();


       })


        $("#des_productoserv").val(producto);
        $("#id_productoserv").val(proid);
        $("#pre_productoserv").val(precio);
        $("#pre_producto_refserv").val(precio);
        $("#uni_productoserv").val(unidad);
       // $("#id_almacen_pro").val(id_almacen_pro);
       $("#cod_productoserv").val(cod_producto);

       $("#can_productoserv").select(); 



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
           "<td width='200px' hidden='hidden'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
           "<td width='900px'><input type='hidden' class='form-control input-sm' name='pronom[]' value='"+producto+"'>"+producto+"</td>"+
           "<td> <input type='hidden' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>"+cantidad+"</td>"+
           "<td  hidden='hidden' width='80px' ><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'>"+unidad+"</td>"+
           "<td><input  type='hidden' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'  style='width:80px' >"+precio+"</td>"+
           "<td hidden='hidden'  ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
           "<td><input  type='hidden' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' >"+total+"</td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
            "<td> <input type='number' step='any' min='0' value='0' name='cant_dev[]'  class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
           "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

          actualizarpro();

          calculartotal();
          $("#can_producto").val('1'); 
          $("#modal-cantidad-precio").modal("hide");


        }else{

         /* if( parseFloat(precio_ref)<= parseFloat(precio)){*/
           $('#grdet').append("<tr>"+
           "<td width='200px' hidden='hidden'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
           "<td width='900px'><input type='hidden' class='form-control input-sm' name='pronom[]' value='"+producto+"'>"+producto+"</td>"+
           "<td> <input type='hidden' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>"+cantidad+"</td>"+
           "<td  hidden='hidden' width='80px' ><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'>"+unidad+"</td>"+
           "<td><input  type='hidden' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' readonly='readonly' value='"+precio+"'  style='width:80px' >"+precio+"</td>"+
           "<td hidden='hidden'  ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
           "<td><input  type='hidden' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' >"+total+"</td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
           "<td> <input type='number' step='any' min='0' value='0' name='cant_dev[]'  class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
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

   function agregaritemservicio(){



   /* var producto = $('#producto').select2('data')[0].producto;
    var precio =  $('#producto').select2('data')[0].propun;
    var proid =  $('#producto').select2('data')[0].id;
    var  unidad =  $('#producto').select2('data')[0].unidad;
    var pro_rel = $('#producto').select2('data')[0].pro_rel;
    var contar = $('#producto').select2('data')[0].presentacion;*/

    var producto = $('#des_productoserv').val();
    var precio =  $('#pre_productoserv').val();
    var proid =  $('#id_productoserv').val();
    var  unidad =  $('#uni_productoserv').val();
    var cantidad = $('#can_productoserv').val();
    var id_almacen_pro = $('#id_almacen_proserv').val();
    var codigo = $('#cod_productoserv').val();

    var total = cantidad*precio;

    var  precio_ref =  $('#pre_producto_refserv').val();

    
  //  var pro_rel = $('#producto').select2('data')[0].pro_rel;
  //  var contar = $('#producto').select2('data')[0].presentacion;

  /*if(contar>0){
        presentaciones(proid);

        $("#modal-presentaciones").modal("show");
      }else{*/

        if(precio_ref===undefined){
          $('#grdetserv').append("<tr>"+
           "<td width='200px' hidden='hidden'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
           "<td width='900px'><input type='hidden' class='form-control input-sm' name='pronom[]' value='"+producto+"'>"+producto+"</td>"+
           "<td> <input type='hidden' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>"+cantidad+"</td>"+
           "<td  hidden='hidden' width='80px' ><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'>"+unidad+"</td>"+
           "<td><input  type='hidden' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'  style='width:80px' >"+precio+"</td>"+
           "<td hidden='hidden'  ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
           "<td><input  type='hidden' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' >"+total+"</td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
           "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

          actualizarproserv();

          calculartotalservicio();
          $("#can_productoserv").val('1'); 
          $("#modal-cantidad-precio-servicio").modal("hide");


        }else{

         /* if( parseFloat(precio_ref)<= parseFloat(precio)){*/
           $('#grdetserv').append("<tr>"+
           "<td width='200px' hidden='hidden'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
           "<td width='900px'><input type='hidden' class='form-control input-sm' name='pronom[]' value='"+producto+"'>"+producto+"</td>"+
           "<td> <input type='hidden' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>"+cantidad+"</td>"+
           "<td  hidden='hidden' width='80px' ><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'>"+unidad+"</td>"+
           "<td><input  type='hidden' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' readonly='readonly' value='"+precio+"'  style='width:80px' >"+precio+"</td>"+
           "<td hidden='hidden'  ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
           "<td><input  type='hidden' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' >"+total+"</td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
           "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

          actualizarproserv();

          calculartotalservicio();
          $("#modal-cantidad-precio-servicio").modal("hide");
          $("#can_productoserv").val('1'); 
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


function actualizarproserv(){



  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/actualizarpro/servicio",

  }).done(function(respuesta){


    $("#divactserv").html(respuesta.vista);
    

    
  });


}


</script>


</br>


<div class="container-fluid" style="font-size:10pt">
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
       <div class="box-header with-border" style="background:blue">
        <font size="2" color="white"><strong><center>Editar Orden de Trabajo</center></strong></font>
      </div>


      <div class="box-body">
      	<div class="row">
      		<div class="col-lg-12">    			
      		<table class="table table-hover table-striped">
      			 <tr>
      			 	<td style="width:200px;"><strong>Taller:</strong></td>
      			 	<td style="width:500px;">{{$sucursal->tipo_negocio}}</td>
      			 	<td style="width:200px;"><strong>N° Orden:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->serdoc}}-{{$cotizacion->numdoc}}</td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>Situación:</strong></td>
      			 	<td style="width:400px;"></td>
      			 	<td style="width:200px;"><strong>Estado:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->est_ord_nom}}</td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>Fecha Ingreso:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->fechacot}}</td>
      			 	<td style="width:200px;"><strong>Fecha Estado:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->fec_ult_est}}</td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>Marca:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->mar_nom}}</td>
      			 	<td style="width:200px;"><strong>Línea:</strong></td>
      			 	<td style="width:500px;"></td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>Modelo:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->mod_nom}}</td>
      			 	<td style="width:200px;"><strong>Serie:</strong></td>
      			 	<td style="width:400px;">{{$cotizacion->equi_ser}}</td>
      			 </tr>
      		
      			 <tr>
      			 	<td style="width:200px;"><strong>Cliente:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->ccanom}}</td>
      			 	<td style="width:200px;"></td>
      			 	<td style="width:500px;"></td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>Tipo Documento:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->tdides}}</td>
      			 	<td style="width:200px;"><strong>Documento Identidad:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->ccandi}}</td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>Dirección:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->clidir}}</td>
      			 	<td style="width:200px;"><strong></strong></td>
      			 	<td style="width:500px;"></td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>Contacto:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->clicon}}</td>
      			 	<td style="width:200px;"><strong>Teléfono:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->telefono}}</td>
      			 </tr>
      			  <tr>
      			 	<td style="width:200px;"><strong>E-mail:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->clicor}}</td>
      			 	<td style="width:200px;"></td>
      			 	<td style="width:500px;"></td>
      			 </tr>
      			   <tr>
      			 	<td style="width:200px;"><strong>Documento Garantía:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->doc_gar}}</td>
      			 	<td style="width:200px;"><strong>Fecha Garantía:</strong></td>
      			 	<td style="width:500px;">{{$cotizacion->fec_doc_gar}}</td>
      			 </tr>
      		</table>

      	</div>
      	</div>
      </div>
       

       <div class="box-body">
    <div class="row">
       <div hidden="hidden" class="col-lg-2">
          <div class="form-group form-group-sm">
            <LABEL>Almacenes</LABEL>
            <select name="id_almacen" id="id_almacen" class="form-control">
              @foreach($almacenes as $alm)
              @if($cotizacion->id_almacen == $alm->id_almacen)
                 <option selected="selected" value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
              @else
                 <option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
              @endif
             
              @endforeach
            </select>
          </div>
        </div>

      
      


      <div hidden="hidden" class="col-lg-3">
           <label>Condici&oacute;n Pago</label>
           <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="condicionpago" >
           @foreach($condiciones as $condicion)
           @if($condicion->cre_dia_id == $cotizacion->cre_dia_id)
            <option selected="selected" value="{{$condicion->cre_dia_id}}">{{$condicion->cre_dia_nom}}</option>
           @else

            <option value="{{$condicion->cre_dia_id}}">{{$condicion->cre_dia_nom}}</option>
           @endif
            
            @endforeach
          </select>
       
      </div>
       <div hidden="hidden" class="col-lg-3">
           <label>Moneda</label>
           <select class="form-control selectpicker" data-show-subtext="true" data-live-search="true" name="moncod" >
           @foreach($monedas as $moneda)

            @if($moneda->moncod == $cotizacion->moncod)
              <option selected="selected" value="{{$moneda->moncod}}">{{$moneda->monnom}}</option>
            @else
              <option value="{{$moneda->moncod}}">{{$moneda->monnom}}</option>
            @endif

            @endforeach
          </select>
       
      </div>
    </div>
    </div>
  </div>
      <div class="box">
       <div class="box-header with-border" style="background:blue">
        <font size="2" color="white"><strong><center>Historial</center></strong></font>
      </div>

      <div class="box-body">
        <div class="row">
       	 <div class="col-lg-12">
       	 	
      		<table class="table table-hover table-striped">
      			 <tr>
      			 	<td><strong>Técnico:</strong></td>
      			 	<td>{{$cotizacion->name}} {{$cotizacion->apeusu}}</td>
      			 	
      			 </tr>
      			  <tr>
      			 	<td><strong>Síntoma:</strong></td>
      			 	<td>{{$cotizacion->observaciones}}</td>
      			 	
      			 </tr>
      			  <tr>
      			 	<td><strong>Estado Físico:</strong></td>
      			 	<td>{{$cotizacion->est_fis}}</td>
      			 	
      			 </tr>
      			  <tr>
      			 	<td><strong>Evaluación:</strong></td>
      			 	<td><select disabled="disabled" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
		                <option></option>
		             		@foreach($evaluaciones as $evaluacion)
			                    @if($evaluacion->eval_id == $cotizacion->eval_id)
			                     <option selected="selected" value="{{$evaluacion->eval_id}}">{{$evaluacion->eval_nom}}</option>
			                  	@else
			                     <option value="{{$evaluacion->eval_id}}">{{$evaluacion->eval_nom}}</option>

			                  	@endif
		                  @endforeach
              			</select>
              	</td>
      			 
      			 </tr>
      			  <tr>
      			 	
      			 	<td><strong>Falla:</strong></td>
      			 	<td><select disabled="disabled" class="form-control selectpicker" data-show-subtext="true" data-live-search="true">
                <option></option>
             	@foreach($fallas as $falla)
                    @if($falla->fall_id == $cotizacion->fall_id)
                     <option selected="selected" value="{{$falla->fall_id}}">{{$falla->fall_nom}}</option>
                  @else
                     <option value="{{$falla->fall_id}}">{{$falla->fall_nom}}</option>

                  @endif
                 
                @endforeach
              </select></td>
      			 </tr>
      			  <tr>
      			 	<td><strong>Diagnóstico:</strong></td>
      			 	<td> <textarea type="text" readonly="readonly"  class="form-control" rows="4">{{$cotizacion->obs_diag}}</textarea> </td>
      			 	
      			 </tr>
      			   <tr>
      			 	<td><strong>Observacion Actividad:</strong></td>
      			 	<td> <textarea type="text" readonly="readonly" class="form-control" rows="4">{{$cotizacion->obs_act}}</textarea> </td>
      			 	
      			 </tr>
      		</table>
       	 </div>
          
        </div>
    



      </div>
    </div>


<div class="box">
 <div class="box-header" style="background-color:blue;">
   <font color="white"><strong>REPUESTOS</strong></font>



 </div>
 <div style="display:none;" class="box-header with-border form-group-sm">

  <div hidden="hidden" class="col-lg-2">
    <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
  </div>
  <div  class="col-lg-10">
    <div class="form-group form-group-sm" id="divactpro">
      <select data-tags='true' style=" font-weight: bold;" autocomplete="false" class="form-control" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio();" onchange="ingresar_cantidad_precio();"  name="producto" id="producto">

      </select>
    </div>

  </div>

</div>


  <div class="box-body">
    <div class="row">
     
        <div class="col-lg-12 table-responsive">

              <table class="table table-hover" id="grdet">
        <thead>
          <tr style="background:gray;color:white;vertical-align:middle;">
          <th hidden="hidden">COD.</th>
          <th style="vertical-align:middle;">Producto</th>
          <th style="vertical-align:middle;">Cantidad</th>
          <th hidden="hidden">U.M.</th>
          <th style="vertical-align:middle;">PU</th>
          <th hidden="hidden" >Desc.</th>
          <th style="vertical-align:middle;">Total</th>
          <th hidden="hidden">P.U</th>
          <th style="vertical-align:middle;" >Devoluci&oacute;n</th>
          <th style="vertical-align:middle;" >Devuelto a <br> Almac&eacute;n</th>
          <th style="vertical-align:middle;"> Pendiente de <br> Devoluci&oacute;n</th>
          <th ></th>
      </tr>
        </thead>

        <tbody>
           @foreach($repuestos as $repuesto)
            <tr>
           <td style="vertical-align:middle;" width='200px' hidden="hidden"><input  type='hidden' readonly='readonly' class='form-control input-sm'  value='{{$repuesto->procod}}'>{{$repuesto->procod}}</td>
           <td style="vertical-align:middle;" width='900px'><input hidden="hidden"  type='hidden' class='form-control input-sm' name='pronom[]' value='{{$repuesto->cdedes}}'>{{$repuesto->cdedes}}</td>
           <td style="vertical-align:middle;"> <input   type='hidden' step='any' min='0' value='{{$repuesto->cdecan}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>{{$repuesto->cdecan}}</td>
           <td style="vertical-align:middle;" width='80px' hidden="hidden"><input readonly='readonly'  value='{{$repuesto->umecod}}'  name='unid[]'  class='form-control input-sm'></td>
           <td style="vertical-align:middle;"><input  type='hidden' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='{{$repuesto->cdepuni}}'  style='width:80px' >{{$repuesto->cdepuni}}</td>
           <td style="vertical-align:middle;" hidden="hidden" ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>
           <td style="vertical-align:middle;"><input  type='hidden' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='{{$repuesto->cdevve}}' onkeyup='CalcularItem(this);' style='width:80px' >{{$repuesto->cdevve}}</td>
           <td style="vertical-align:middle;" hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='{{$repuesto->valor_unitario}}' style='width:80px' ></td>
           <td style="vertical-align:middle;" hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='{{$repuesto->IdProducto}}' readonly='readonly' ></td>
           <td style="vertical-align:middle;" hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='{{$repuesto->id_almacen_pro}}' readonly='readonly' ></td>
           <td style="vertical-align:middle;"> <input type='number' step='any' min='0' name='cant_dev[]' value="{{$repuesto->cant_dev}}" class='form-control input-sm ' readonly="readonly" id='font-size' style='width:60px'></td>
           <td style="vertical-align:middle;"> <input type='number' step='any' min='0' name='cant_dev_alm[]' value="{{$repuesto->cant_dev_alm}}" class='form-control input-sm '  id='font-size' style='width:60px'></td>
            <td style="vertical-align:middle;">{{$repuesto->cant_dev-$repuesto->cant_dev_alm}}</td>
           <td style="vertical-align:middle;" hidden="hidden"><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>
           @endforeach
         </tbody>
        </table>
      
 
  </div>
  </div>

     <div hidden="hidden" class="row">
      <div class="col-lg-3">
        <div class="form-group form-group-sm">
        <label>TOTAL</label>
         <input type="number"  step="any"  class="form-control"  id="totalrep" name="totalrep" value='' readonly="readonly">
        </div>
        
      </div>
    </div>

  
</div>


</div>


<div class="box">
 <div class="box-header" style="background-color:blue;">
   <font color="white"><strong>SERVICIOS</strong></font>



 </div>
 <div style="display:none;" class="box-header with-border form-group-sm">

  <div hidden="hidden" class="col-lg-2">
    <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
  </div>
  <div  class="col-lg-10">
    <div class="form-group form-group-sm" id="divactserv">
      <select data-tags='true' style=" font-weight: bold;" autocomplete="false" class="form-control" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio_servicio();" onchange="ingresar_cantidad_precio_servicio();"  name="servicio" id="servicio">

      </select>
    </div>

  </div>

</div>


  <div class="box-body">
    <div class="row">
     
        <div class="col-lg-12 table-responsive">

              <table class="table table-hover" id="grdetserv">
        <thead>
          <tr style="background:gray;color:white;">
          <th hidden="hidden">COD.</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th hidden="hidden">U.M.</th>
          <th>PU</th>
          <th hidden="hidden" >Desc.</th>
          <th>Total</th>
          <th hidden="hidden">P.U</th>
          <th ></th>
      </tr>
        </thead>

        <tbody>
           @foreach($servicios as $servicio)
            <tr>
           <td width='200px' hidden="hidden"><input  type='hidden' readonly='readonly' class='form-control input-sm'  value='{{$servicio->procod}}'>{{$servicio->procod}}</td>
           <td width='900px'><input hidden="hidden"  type='hidden' class='form-control input-sm' name='pronom[]' value='{{$servicio->cdedes}}'>{{$servicio->cdedes}}</td>
           <td> <input   type='hidden' step='any' min='0' value='{{$servicio->cdecan}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'>{{$servicio->cdecan}}</td>
           <td width='80px' hidden="hidden"><input readonly='readonly'  value='{{$servicio->umecod}}'  name='unid[]'  class='form-control input-sm'></td>
           <td><input  type='hidden' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='{{$servicio->cdepuni}}'  style='width:80px' >{{$servicio->cdepuni}}</td>
           <td hidden="hidden" ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>
           <td><input  type='hidden' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='{{$servicio->cdevve}}' onkeyup='CalcularItem(this);' style='width:80px' >{{$servicio->cdevve}}</td>
           <td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='{{$servicio->valor_unitario}}' style='width:80px' ></td>
           <td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='{{$servicio->IdProducto}}' readonly='readonly' ></td>
           <td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='{{$servicio->id_almacen_pro}}' readonly='readonly' ></td>
           <td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>
           @endforeach
         </tbody>
        </table>
      
 
  </div>
  </div>

     <div hidden="hidden" class="row">
      <div class="col-lg-3">
        <div class="form-group form-group-sm">
        <label>TOTAL</label>
         <input type="number"  step="any"  class="form-control"  id="totalserv" name="totalserv" value='' readonly="readonly">
        </div>
        
      </div>
    </div>

     <div  class="row">
      <div class="col-lg-2">
        <div class="form-group form-group-sm">
        <label>TOTAL</label>
         <input type="number"  step="any"  class="form-control"  id="total" name="total" value='' readonly="readonly">
        </div>
        
      </div>
    </div>

        <div class="row">
          
        @if(Auth::user()->hasRole('calidad') || Auth::user()->hasRole('administrador'))
          <div class="col-lg-3">
              <button type="button" id="btnRegComp" class=" btn btn-block btn-primary btn-sm botones">ACEPTAR</button><br>
            </div>
            <div class="col-lg-3">
              <button type="button" id="btnRegRech" class=" btn btn-block btn-warning btn-sm botones">RECHAZAR</button><br>
            </div>
              <div class="col-lg-3">
              <a href="/indexcotizaciones"><button type="button" class=" btn btn-block btn-danger btn-sm botones">SALIR</button></a><br>
            </div>
        @else
            <div class="col-lg-3">
              <button type="button" id="btnRegComp" class=" btn btn-block btn-primary btn-sm botones">REGISTRAR</button><br>
            </div>
              
             <div class="col-lg-3">
              <a href="/indexcotizaciones"><button type="button" class=" btn btn-block btn-danger btn-sm botones">SALIR</button></a><br>
            </div>
        @endif
            
          </div>

</div>


</div>




</div>
</div>
<input type="hidden" name="cotid" value="{{$id}}">
{!!Form::close()!!}

@endsection
