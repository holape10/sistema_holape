@extends('layouts.empresas')
@section('contenido')

@include('empresas.clientes.modalcrearcliente')
@include('empresas.puntosventas.modalpresentaciones')
@include('empresas.puntosventas.modal_catalogo')
@include('empresas.puntosventas.modalingresarcantidadprecio')

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

#modal-cantidad-precio{
  z-index: 99999 !important;
}

#table-presentaciones .btn:hover
{
    color: #fff;
    background-color:red;
    border-color: red;
}

#table-presentaciones .btn:focus
{
    color: #fff;
    background-color:red;
    border-color: red;
}


.green
{
    color: #fff;
    background-color: #398439;
    border-color: #255625;
}

</style>

<body>


  <script>




   $(document).ready(function()
   {

   


     $("#buscarproducto").focus();

          $("#modal-cantidad-precio").on('hidden.bs.modal', function () {
           actualizarpro();
    });


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
       // $("#modal-cantidad-precio").modal("hide");
      }
     
    });

     $("#btnAgregarLista").click(function(e){
     
        
        agregaritem();
       // $("#modal-cantidad-precio").modal("hide");
   
    });



     $('#modal-cantidad-precio').on('shown.bs.modal', function() { $("#can_producto").focus(); })
    $('#modal-presentaciones').on('shown.bs.modal', function() { $("#table-presentaciones .btn:first").focus(); })



  // $("#producto").focus();

   

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

    // $("#producto").select2('open');


     $(".selectpicker").selectpicker({


     });

                


      $('#tdicod').val('1');

   



    $('#clinum').val('00000000');
    $('#clinom').val('Varios');

  
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
        url: '/registrarpedido',
        data: formulario,
      }).done(function(respuesta){


        if(respuesta.estado =='error'){

            alert(respuesta.mensaje);

        }else{

            window.location.href = "/pedidos";
           
           alert("PEDIDO NUMERO: "+respuesta.pedido);
 
        }

          $("#imgload").hide();
          $(".botones").show();

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
        url: '/restaurantpunto',
        data: formulario,
      }).done(function(respuesta){

           if(respuesta.estado =='error'){
            alert(respuesta.mensaje);
            
            $("#imgload").hide();
            $(".botones").show();
        }else{
            window.location.href = "/pos";
            $("#imgload").hide();
 
        }

      });

    });


          $("#buscarproducto").keypress(function(e) {

      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){



        var valor = $(this).val();
        var cont = 0, cantidad=0,total=0;
        $.ajax({
          type: 'get',
          url: '/consultarproductosbarra',
          dataType: 'json',
          data: {'value' : $(this).val() },
          success : function(data) {

          $('#grdet').append("<tr>"+
             "<td width='200px'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+data[0].codigo+"'></td>"+
            "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+data[0].producto+"'></td>"+
            "<td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
            "<td width='80px' hidden='hidden' ><input readonly='readonly'  value='"+data[0].unidad+"'  name='unid[]'  class='form-control input-sm'></td>"+
            "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+data[0].propun+"'  style='width:80px' ></td>"+
            "<td hidden='hidden'  ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
            "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+data[0].propun+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
            "<td hidden='hidden'><input  type='number'  step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+data[0].propun+"' style='width:80px' ></td>"+
            "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].id+"' readonly='readonly' ></td>"+
            "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+data[0].id_almacen+"' readonly='readonly' ></td>"+
            "<td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='"+data[0].icbper+"' readonly='readonly' ></td>"+
            "<td hidden='hidden' ><input type='text' class='form-control' name='mon_icbper[]'  value='"+data[0].mon_icbper+"' readonly='readonly' ></td>"+
            "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

             calculartotal();

              $("#buscarproducto").val('');
          }

         

     })



}


       

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

      $("#frmCatalogo").keypress(function(e) {
    if (e.which == 13) {
      return false;
    }
  })


  });




function consultar_catalogo(){
    
     var formulario = $("#frmCatalogo").serializeArray();
    //  $("#imgloadcliente").show();
   //   $(".botonescliente").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/consultarcatalogo',
        data: formulario,
      }).done(function(respuesta){

        $("#detalle_catalogo").html(respuesta.vista);
       // $("#imgloadcliente").hide();
     
       //  $(".botonescliente").show();
         
      });





}


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

function CalcularItem(ele) {

  var totigv = 0,totgrav=0 ,subtotal=0;
  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {

    var  totitemgrav=0;

    totitemgrav = $(this).find("td:eq(5) > input").val() / $(this).find("td:eq(1) > input").val();

    $(this).find("td:eq(4) > input").val(totitemgrav.toFixed(2));

  });
  calculartotal();

};

function Calcular(ele) {

  var totigv = 0,totgrav=0 ,subtotal=0;
  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {

    var  totitemgrav=0;

    totitemgrav = $(this).find("td:eq(2) > input").val() * $(this).find("td:eq(4) > input").val();
    $(this).find("td:eq(6) > input").val(totitemgrav.toFixed(2));

  });
  calculartotal();

};




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
  //  $("#vendedor").val($('#clicod').find(':selected').attr('data-vendedor'));
   
   
    $("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));
    
     /* if($('#tdicod').val() =='6' ){
             $('#factura').prop("checked",true);
      }

      if($('#tdicod').val() =='1' ){
         $('#boleta').prop("checked",true);
      }*/

       if($('#tdicod').val() =='6' ){
        
          if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

           $('#factura').prop("checked",true);
       
          }
            


             
      }

      if($('#tdicod').val() =='1' ){
           if($("input[name=tdocod]:checked:checked ").val()!='13' && $("input[name=tdocod]:checked:checked ").val()!='15'){

         $('#boleta').prop("checked",true);
       
          }
            
        
            
      

        
      }
      



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




};


 function presentaciones(id){
     var id = id;
   //  var suc = $('#sucursal').val();

 
       $("#modal-presentaciones").modal("show");

       $("#presentaciones").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');

        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/presentacionesproducto/"+id,

        }).done(function(respuesta){
          $("#presentaciones").html(respuesta.vista);
        });

   
  }

   function agregaritem_pre(button){
     var id = button.id;
     var precio = button.value;
     var productonom = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
     var unidad= $('#'+id+'unidad').val();
     var imagen = $('#'+id+'imagen').val();
        var cantidad = $('#can_producto').val();
         var total = cantidad*precio;

    $('#grdet').append('<tr><td width="900px"><input type="text" class="form-control" name="pronom[]" value="'+productonom+'"></td>'+
    '<td><input type="number" step="any" min="0" value="1" name="cant[]" onkeyup="Calcular(this);" onchange="Calcular(this);" class="form-control input-sm cant" id="font-size" style="width:60px"> </td><td hidden="hidden" ><input readonly="readonly"  value="'+unidad+'" style="width:100px" name="unid[]"  class="form-control input-sm"></td><td><input  type="number" step="any" min="0" class="form-control input-sm" name="propun[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="'+precio+'" style="width:80px" ></td><td hidden="hidden" ><input  type="number" step="any" min="0" class="form-control input-sm" name="desc[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="0.00" style="width:80px" ></td><td><input  type="text" class="form-control" name="itemtotal[]"  value="'+total+'" onkeyup="CalcularItem(this);" style="width:80px"></td><td hidden="hidden"><input  type="number" readonly="readonly" step="any" min="0" class="form-control input-sm" name="precio[]"  value="'+precio+'" style="width:80px" ></td><td hidden="hidden"><input type="text" class="form-control" name="proid[]"  value="'+proid+'" readonly="readonly" ></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

 
    calculartotal();
 
    $("#modal-presentaciones").modal("hide");
   



  }

/* function agregarnota(button){


  $('#grdet').append("<tr>"+
    "<td width='1300px'><input type='text' class='form-control input-sm btn-block' name='pronom[]' value='' ></td>"+
    "<td> <input type='number' step='any' min='0' value='1'  name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='cant form-control input-sm ' id='font-size' style='width:60px'> </td>"+
    "<td hidden='hidden'><select  style='width:100px' name='unid[]'  class='form-control input-sm'>@foreach($unidades as $uni) @if($uni->umecod =='NIU') <option selected='selected' value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @else <option  value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @endif @endforeach</select></td>"+
    "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'   onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
    "<td hidden='hidden' ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
    "<td><input  type='text' class='form-control input-sm' name='itemtotal[]'  value='0.00' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
     '<td hidden="hidden"><input  type="number" readonly="readonly" step="any" min="0" class="form-control input-sm" name="precio[]"  value="0" style="width:80px" ></td>'+
    "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='0' readonly='readonly' ></td>"+
    "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

  calculartotal();

  $("#modal-presentaciones").modal("hide");

  
  }*/

  function agregarnota(button){


   $('#grdet').append("<tr>"+
         "<td width='200px'><input type='text' readonly='readonly' class='form-control input-sm'  value=''></td>"+
        "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value=''></td>"+
        "<td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
        "<td hidden='hidden'><select  style='width:100px' name='unid[]'  class='form-control input-sm'>@foreach($unidades as $uni) @if($uni->umecod =='NIU') <option selected='selected' value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @else <option  value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @endif @endforeach</select></td>"+
        "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0' style='width:80px' ></td>"+
        "<td hidden='hidden' ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
        "<td><input  type='text' class='form-control input-sm'  name='itemtotal[]'  value='0' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
        "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='0' style='width:80px' ></td>"+
        "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='0' readonly='readonly' ></td>"+
        "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value=' readonly='readonly' ></td>"+
        "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

  calculartotal();

  $("#modal-presentaciones").modal("hide");

  
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
        "<td width='80px' hidden='hidden' ><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'></td>"+
        "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);'  value='"+precio+"' style='width:80px' ></td>"+
        "<td hidden='hidden' ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
        "<td><input  type='text' class='form-control input-sm' readonly='readonly' name='itemtotal[]'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
        "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
        "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
        "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
        "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
        
        actualizarpro();

        calculartotal();

        $("#modal-cantidad-precio").modal("hide");

  }else{

    if( parseFloat(precio_ref)<= parseFloat(precio)){
      $('#grdet').append("<tr>"+
         "<td width='200px'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
        "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"'></td>"+
        "<td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
        "<td width='80px'  hidden='hidden'><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'></td>"+
        "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);'  value='"+precio+"'  style='width:80px' ></td>"+
        "<td hidden='hidden' ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
        "<td><input  type='text' class='form-control input-sm' name='itemtotal[]'  value='"+total+"' readonly='readonly' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
        "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
        "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
        "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
        "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
        
        actualizarpro();

        calculartotal();
        $("#modal-cantidad-precio").modal("hide");
    }else{

       alert('No Tiene Autorizacion para Descuentos');
    }

      
  }


  //}

  }



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
        $("#id_almacen_pro").val(id_almacen_pro);
         $("#cod_producto").val(cod_producto);

        $("#can_producto").select()
          
        $("#modal-presentaciones").modal("hide");
           
     

      
       

  }


  function ingresar_cantidad_precio(){
      
      var producto = $('#producto').select2('data')[0].producto;
      var precio =  $('#producto').select2('data')[0].propun;
      var proid =  $('#producto').select2('data')[0].id;
      var unidad =  $('#producto').select2('data')[0].unidad;
      var pro_rel = $('#producto').select2('data')[0].pro_rel;
      var contar = $('#producto').select2('data')[0].presentacion;
      var id_almacen_pro = $('#producto').select2('data')[0].id_almacen_pro;
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
        $("#uni_producto").val(unidad);
             $("#pre_producto_ref").val(precio);
        $("#id_almacen_pro").val(id_almacen_pro);
          $("#cod_producto").val(cod_producto);
        $("#can_producto").select(); 
  
      

      }
       

  }



  function cambiarcolor(producto){
         
         
      
      
       $("#"+producto).addClass('red');
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



   function modificarpedido(){
    
    var pedido = $("#num_ped").val();
    window.location.href = "/modificarpedidos/"+pedido;

  }


function buscar_pedido(){

    
    var pedido = $("#bus_pedido").val();
   
    window.location.href = "/buscar_pedido/"+pedido;

   


  }


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

</script>


</br>
 

<div class="container-fluid" id="general">
  
  
  @if($errors->any())
  <div class="row">
     <div class="col-lg-12">
        <div class="alert alert-danger">
          <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
        <strong>Alerta!</strong> {{$errors->first()}}
      </div>
     </div>
  </div>
 
@endif


   {!!Form::open(array('url'=>'/restaurantpunto','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
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
 
     <div class="col-lg-12"> 
        <div class="box">
          <div class="box-body">
            <div class="col-lg-6">
               <input type="number" name="num_ped" id="num_ped" onkeypress="if(event.keyCode == 13) modificarpedido();"  class="form-control" value="" placeholder="INGRESAR NUMERO DE PEDIDO" >
            </div>
            <div class="col-lg-4">
               <input type="text" name="bus_pedido" id="bus_pedido" onkeypress="if(event.keyCode == 13) buscar_pedido();"  class="form-control" value="" placeholder="CONSULTAR PEDIDO" >
            </div>
            <div class="col-lg-1">
               <a href="/pedidos"><button type="button" class="btn-success btn btn-block btn-sm"><strong>NUEVO PEDIDO</strong></button></a>
            </div>
           <div class="col-lg-1">
               <a href="" data-target="#modal-catalogo" data-toggle="modal"><button type="button" class="btn-warning btn btn-block btn-sm"><strong>CATALOGO</strong></button></a>
            </div>
            
             
          </div>
        </div>
    </div>
  <div hidden="hidden" class="divcargar">
      
    </div>
  <div id="divpedido">
     <div class="col-lg-6">
    <div class="row">
     
            <div class="col-lg-12">
              <div class="box">
                 <div class="box-header" style="background-color:blue;">
                    <font color="white"><strong><center>{{$datos->tipo_negocio}}</center></strong></font>


                 </div>
                <div class="box-header with-border form-group-sm">
                  
                 <div  class="col-lg-5">
                    <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
                  </div>
                  <div  class="col-lg-7">                    <div class="form-group form-group-sm" id="divactpro">
                       <select data-tags='true' style=" font-weight: bold;" autocomplete="false" class="form-control" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio();" onchange="ingresar_cantidad_precio();"  name="producto" id="producto">
                         
                     </select>
                    </div>
                   
                  </div>
                
                </div>
               
              </div>
            </div>

     
 
      <div class="col-lg-12">
              <div class="box">
                <div class="box-header" style="background-color:blue;">
                   <font color="white"><center><strong>DETALLE</strong></center></font>
                </div>
                 <div class="box-body">
                   <table class="table table-hover" id="grdet">
                        <thead>
                      <th>COD.</th>
                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th hidden="hidden">Unidad</th>
                      <th hidden="hidden">VU</th>
                      <th>PU</th>
                      <th>Total</th>
                       <th><button type="button" onClick="agregarnota();" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                    </thead>

                    <tbody>

                    </tbody>
                  </table>
                   <table class="table table-hover">
              <thead>

                <th>OBSERVACIONES</th>
                
              </thead>

              <tbody>

                <tr>
                  <td>
                      <textarea class="form-control" rows="3" maxlength="250" name="observaciones"></textarea>
                  </td>
                </tr>
              </tbody>
            </table>
                </div>
            </div>
      </div>
</div>
      
</div>

<div class="col-lg-6">
  <div class="col-lg-12">
        <div class="box">
    
         <div class="box-header" style="background-color:blue;">
              <font color="white"><center><strong>DATOS DEL PEDIDO</strong></center></font>
         </div>
         <div class="box-body">
             

             <div class="row">

              <div class="col-lg-3" hidden="hidden">
                <div class="form-group form-group-sm">
                    <LABEL>Almacenes</LABEL>
                    <select name="id_almacen" id="id_almacen" class="form-control">
                      @foreach($almacenes as $alm)
                        <option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
                      @endforeach
                    </select>
                </div>
            </div>


              <div class="col-lg-4">
                <div class="form-group form-group-sm">
                    <LABEL>Vendedor</LABEL>
                    <select name="vendedor" id="vendedor" class="form-control">
                      @foreach($vendedores as $ven)
                      @if(Auth::user()->IdUsuario == $ven->IdUsuario)
                        <option  selected="selected" value="{{$ven->IdUsuario}}">{{$ven->name}} {{$ven->apeusu}}</option>
                        @else
                         <option value="{{$ven->IdUsuario}}">{{$ven->name}} {{$ven->apeusu}}</option>
                        @endif
                      @endforeach
                    </select>
                </div>
            </div>
             <div class="col-lg-3">
              <div class="form-group-sm">
                <label>TOTAL</label>
                <input type="text" class="form-control" style="font-weight:bold;font-size:12pt;"  id="total" name="total" value='0.00' readonly="readonly">
              </div>
            </div>
            <div hidden="hidden" class="col-lg-3">
                <div class="form-group form-group-sm">
                    <LABEL>Estado de Pago</LABEL>
                    <select name="estadopago" id="estadopago" class="form-control">
                      @foreach($creditos as $cre)
                        <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
                      @endforeach
                    </select>
                </div>
            </div>

               <div hidden="hidden" class="col-lg-3">
                <div class="form-group form-group-sm">
                   <label>F. Emisión</label>
                     <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                </div>
                   
               </div>

               <div hidden="hidden" class="col-lg-3"  id="divfecVen">
                   <div class="form-group form-group-sm">
                    <label>F. Vencim.</label>
                      <input type="date" name="fecVen" id="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
                  </div>
               </div>
             </div>
              
          

             <div hidden="hidden" class="row">
              <div class="col-lg-4">
                <div class="form-group form-group-sm">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
                
                 
                  <label >
                    <input type="radio" name="tdocod" id="pedido" value="16" checked="checked" > NP
                  </label>
                  
      
                </div>
                </div>
                   <div  class="col-lg-3">
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
      
      
             <div class="row">
              <div class="col-lg-4">
                <div class="form-group form-group-sm">
                    <div class="btn-group btn-group-toggle" data-toggle="buttons">
          
                  
           <label  >
                    <input type="radio" name="tdocod_1" id="boleta" value="03"   > BO
                  </label>
                  <label  >
                    <input type="radio" name="tdocod_1" id="factura" value="01" > FA
                  </label>
                  <label >
                    <input type="radio" name="tdocod_1" id="nota" value="13" checked="checked" > NV
                  </label>
          
                </div>
                </div>
            
              </div>

         
          </div>
      
         </div>
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

              
             <div class="col-lg-9" >
              <div class="form-group">
                <label class="control-label">Cliente</label>
                <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="clicod" id="clicod" onchange="seleccionarcliente();">
                  <option>VENTA AL PORTADOR</option>
                  @foreach($clientes as $cliente)
                    <option value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}" data-direccion="{{$cliente->clidir}}" data-clinom="{{$cliente->clinom}}" data-correo="{{$cliente->clicor}}" data-telefono="{{$cliente->telefono}}" >{{$cliente->clinum}} - {{$cliente->clinom}}</option>
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
                  <label>Correo</label>
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

       <!-- <div class="box-header" style="background-color:blue;">
            <font color="white"><center><strong>MONTO A PAGAR</strong></center></font>
         </div>-->
          <div class="box-body">
     
          <div class="row">
           
       
        
         
            <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
            </div>
        
        

      
          <div class="row">
             <div class="col-lg-6">
              <button type="button" id="btnRegComp" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button><br>
            </div>
            
       

             <div class="col-lg-6">
              <a href="/pedidos"><button type="button" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a><br>
            </div>
          </div>
        </div>

        <input type="hidden" readonly="readonly" name="emit_gui" id="emit_gui" value="0">

   
     </div>
</div>
</div>
</div>

{!!Form::close()!!}
</div>
</div>

@endsection
