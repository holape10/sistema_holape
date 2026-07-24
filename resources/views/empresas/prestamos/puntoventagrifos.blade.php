@extends('layouts.puntoventa')
@section('contenido')

@include('empresas.puntosventas.modalimprimir')
@include('empresas.clientes.modalcrearcliente')
@include('empresas.puntosventas.modalpresentaciones')
  @if(isset($codfact))

         @php
          $pdf = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
        @endphp

      @endif
      
@include('empresas.puntosventas.modalpdf')
<style>
#b1
{
 /*sirve para los caracteres cuando es una palabra grande se salte a la otra linea */
 white-space: normal;
}

#modal-pdf{
   z-index: 99999 !important;
}

#modal-cliente{
	z-index: 10000;
}

#modal-datos-comprobantes{
	z-index: 9999;
}

#scroll
{
  height: 650px;
  width: 800px;
  overflow: scroll;
}
</style>

<body>


		@if(!empty($codfact))
    <script>

   $(document).ready(function()
   {

   $("#modal-pdf").modal("show");
 });
</script>
@endif

	

	<script>
	  
   $(document).ready(function()
   {

   
      $(".mediopago").val('0');

      $('#tdicod').val('1');

      if($('#tdicod').val() =='6' ){

            $('#factura').prop("checked",true);
      }


      if($('#tdicod').val() =='1'){
        $('#boleta').prop("checked",true);
      }

      
      $("#tdicod").on("change", function() {
          if($('#tdicod').val() =='6' ){

             $('#factura').prop("checked",true);
          }

          if($('#tdicod').val() =='1' ){
             $('#boleta').prop("checked",true);
          }

      });

       var metodo = $('#estadopago').find(':selected').attr('data-medio');
       var dias = $('#estadopago').find(':selected').attr('data-dias');
        
          if(metodo=='CREDITO'){
            $("#divmediopago").hide('true');
            $(".mediopago").val('0');
            $("#divfecVen").hide('true');
            $("#fecVen").val(nuevafecha);
          }

          if(metodo =='CONTADO'){
            $("#divmediopago").show('true');
            $("#divfecVen").hide('true');
            $("#fecVen").val($("#fecEmi").val());
            $('#predeterminado_1').val($('#total').val());
          }

          if(metodo =='PERSONALIZADO'){
            $("#divmediopago").hide('true');
            $(".mediopago").val('0');
            $("#divfecVen").show('true');
          }


          $("#estadopago").on("change", function() {
            var metodo = $(this).find(':selected').attr('data-medio');
            var dias = $(this).find(':selected').attr('data-dias');

            if(metodo=='CREDITO'){
              $("#divmediopago").hide('true');
              $(".mediopago").val('0');
              $("#divfecVen").hide('true');
              $("#fecVen").val(nuevafecha);
            }

            if(metodo =='CONTADO'){
              $("#divmediopago").show('true');
              $("#divfecVen").hide('true');
              $("#fecVen").val($("#fecEmi").val());
              $('#predeterminado_1').val($('#total').val());
            }

            if(metodo =='PERSONALIZADO'){
              $("#divmediopago").hide('true');
              $(".mediopago").val('0');
              $("#divfecVen").show('true');
            }
          });


    
    $("#buscarproducto").focus();

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
        url: '/restaurantpunto',
        data: formulario,
      }).done(function(respuesta){


        if(respuesta.estado =='error'){
           
            
            $("#imgload").hide();
            $(".botones").show();
        }else{
            window.location.href = "/pvgrifo/"+respuesta.codfact;
            $("#imgload").hide();
 
        }

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
            window.location.href = "/pvgrifo/"+respuesta.codfact;
            $("#imgload").hide();
 
        }

      });

    });



    $("#buscardescripcion").keyup(function(){
      var val = $(this).val();
      var contarcarateres = $(this).val().length;

      if(contarcarateres >4){
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

    $("#buscardescripcion").keypress(function(e){
      var val = $(this).val();
      var contarcarateres = $(this).val().length;

      //if(contarcarateres >4){
      var code = (e.keyCode ? e.keyCode : e.which);
      if(code==13){
        $("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/busquedaproducto/"+val,

        }).done(function(respuesta){
          $("#detmenu").html(respuesta.vista);

        });
      }
      //}


    });





   

      function mostrarobservacion(ele){

    
     alert($(this).closest("td").siblings().find("input[name=pronomobs[]]").val());


  }



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

            var valornuevo = data[0].proid;

     

           if(data[0].contar =='1'){


             $("#buscarproducto").val('');

             if ($('#grdet >tbody >tr').length > 0){

              $("#grdet tbody tr").each(function(){
               var codigo = $(this).find("td:eq(6) > input").val();

         

               if( valornuevo == codigo){
                cont = cont+1;
                cantidad = parseFloat($(this).find("td:eq(1) > input").val())+1;
                totalitem = parseFloat($(this).find("td:eq(4) > input").val())*cantidad;
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

                $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden' ><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' readonly='readonly'   value='"+data[0].propun+"'  style='width:130px' ></td><td><input type='text' class='form-control' name='itemtotal[]' onkeyup='CalcularItem(this);'  value='"+data[0].propun+"' readonly='readonly'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");


              }

            }else{

              var igvitem = data[0].propun -data[0].provun;
              $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'  ><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' readonly='readonly'   value='"+data[0].propun+"'  style='width:130px' ></td><td><input type='text' class='form-control' readonly='readonly'  name='itemtotal[]' onkeyup='CalcularItem(this);'  value='"+data[0].propun+"'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
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
            
               var cant = $(this).find("td:eq(1) > input").val();
               if(det==''){
                    condet++
                  }else if(cant<1){
                    concant++
                  }
                })

       
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



          })




   var comprobante = $("#comprobante").val();
  
   $("#btnPrint").printPage({

    url: "/voucher/"+comprobante,
    attr: "href",
    messageBox:false

  })



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

function Calcularcantidad(ele) {

  var totigv = 0,totgrav=0 ,subtotal=0;
  var tr = ele.parentNode.parentNode;

  $(tr).each(function() {

     var  totitemgrav=0,precio=0,cantidad;
      
        cantidad = $(this).find("td:eq(5) > input").val() / ($(this).find("td:eq(7) > input").val());

        precio = parseFloat($(this).find("td:eq(5) > input").val()) / cantidad;

        $(this).find("td:eq(4) > input").val(precio.toFixed(2));
        $(this).find("td:eq(1) > input").val(cantidad.toFixed(6));

   });
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


function calculartotal(){

 var totigv = 0,totgrav=0 ,subtotal=0;

 $("#grdet tbody tr").each(function(){

  totgrav = totgrav + parseFloat($(this).find("td:eq(5)  > input").val());

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


    function agregaritem(button){
     var id = button.id;
     var precio = button.value;
     var producto = $('#'+id+'nom').val();
     var proid = $('#'+id+'id').val();
    // var provun = $('#'+id+'vun').val();
     var imagen = $('#'+id+'imagen').val();

  $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"'></td><td> <input type='number' step='any' min='0' value='1' name='cant[]'  onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm' id='font-size' style='width:60px'> </td><td hidden='hidden'><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='' readonly='readonly' style='width:130px' ></td><td><input  type='number' step='any' min='0'  class='form-control input-sm preuni' name='propun[]' id='"+proid+"'  onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'  style='width:80px' ></td><td><input  type='number' step='any' class='form-control input-sm' name='itemtotal[]'  value='"+precio+"' onkeyup='Calcularcantidad(this);' style='width:80px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td><td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm ' name='preunifijo[]' readonly='readonly' value='"+precio+"'  style='width:80px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

      calculartotal();

     $(".preuni").focus();
      
    

      $("#modal-presentaciones").modal("hide");
      // $("#modal-datos-comprobantes").modal("show");

    //  $(function(){
    //     $('.keyboard').keyboard();
    //   });
  }

  function valor(button){

  var id =button.id;

  var valor = $("#"+id).val();

  $(".propun").focus(
    function() {
       alert(this.val());
    });

 

  alert( $(".propun").id);
}


</script>


</br>
 

<div class="container-fluid">
    {!!Form::open(array('url'=>'/restaurantpunto','autocomplete'=>'off','method'=>'POST','name'=>'formfact','id'=>'formfact','role'=>'form','files'=>'true'))!!}
            {{Form::token()}}


  <div class="row">
     <div class="col-lg-4">
   
              <button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-sm botones">COBRAR</button><br>

            </div>
             <div class="col-lg-4">
              <a href="/caja"><button type="button" class="btn btn-success btn-sm btn-block"><strong>MENU PRINCIPAL</strong></button></a>
            </div>
            
        

             <div class="col-lg-4">
              <a href="/pvgrifo"><button type="button" class=" btn btn-block btn-danger btn-sm botones">BORRAR</button></a><br>
            </div>
         
     
  </div>
  <div class="row">
      <div class="col-lg-4 col-md-6 col-sm-6">
        <div class="box" style="min-height:85vh;max-height:85vh;">
       
                <div class="box-body" id="detmenu" >


                   <?php $i=0; ?>
    @foreach($productos as $producto)
      <?php $i=$i+1; ?>

    @if($sucursal->ven_sin_sto =='0')

      @if($producto->stock>0)

            @if($producto->cont_pre > 1)
                  <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                <button type="button" id='pro<?php echo $i; ?>' onclick="presentaciones({{$producto->IdProducto}})" value='{{$producto->precio}}' style="background:{{$producto->color}}  ;width: 140px; height: 120px; border-radius:10px">
                  <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                  <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                
                  <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                  <p><font color="white" style="font-size:8pt;">{{$producto->pronom}}<br> Stock: {{$producto->stock}}</font></p>
                </button><br><br>
            </div>
            @else

                  <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                <button type="button" id='pro<?php echo $i; ?>'  onclick="agregaritem(this)" value='{{$producto->precio}}' style="background:{{$producto->color}}  ;width: 140px; height: 120px; border-radius:10px">
                  <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                  <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                
                  <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                  <p><font color="white" style="font-size:8pt;">{{$producto->pronom}}<br> Stock: {{$producto->stock}}</font></p>
                </button><br><br>
            </div>
            @endif

            @if($producto->precio2 > 0)
            
                @if($producto->cont_pre > 0 )
                      <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <button type="button" id='pro<?php echo $i; ?>' onclick="presentaciones({{$producto->IdProducto}})" value='{{$producto->precio}}' style="background:orange   ;width: 140px; height: 120px; border-radius:10px">
                      <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                      <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                    
                      <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                      <p><font color="white" style="font-size:8pt;">{{$producto->pronom}}<br>Stock: {{$producto->stock}}</font></p>
                    </button><br><br>
                </div>
                @else

                      <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <button type="button" id='pro<?php echo $i; ?>'  onclick="agregaritem(this)" value='{{$producto->precio}}' style="background:orange   ;width: 140px; height: 120px; border-radius:10px">
                      <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                      <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                    
                      <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                      <p><font color="white" style="font-size:8pt;">{{$producto->pronom}}<br>Stock: {{$producto->stock}}</font></p>
                    </button><br><br>
                </div>
                @endif
            @endif
          @endif

      @elseif($sucursal->ven_sin_sto =='1')

          @if($producto->cont_pre > 1)
                  <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                <button type="button" id='pro<?php echo $i; ?>' onclick="presentaciones({{$producto->IdProducto}})" value='{{$producto->precio}}' style="background:{{$producto->color}}  ;width: 120px; height: 100px; border-radius:10px">
                  <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                  <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                
                  <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                  <p><font color="white" style="font-size:8pt;"><strong>{{$producto->pronom}}</strong><br> </font></p>
                </button><br><br>
            </div>
            @else

                  <div class="col-lg-4 col-md-12 col-sm-12 col-xs-12">
                <button type="button" id='pro<?php echo $i; ?>'  onclick="agregaritem(this)" value='{{$producto->precio}}' style="background:{{$producto->color}}  ;width: 120px; height: 100px; border-radius:10px">
                  <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                  <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                
                  <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                  <p><font color="white" style="font-size:8pt;"><strong>{{$producto->pronom}}</strong><br> </font></p>
                </button><br><br>
            </div>
            @endif

            @if($producto->precio2 > 0)
            
                @if($producto->cont_pre > 1)
                      <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <button type="button" id='pro<?php echo $i; ?>' onclick="presentaciones({{$producto->IdProducto}})" value='{{$producto->precio}}' style="background:orange   ;width: 140px; height: 120px; border-radius:10px">
                      <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                      <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                    
                      <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                      <p><font color="white" style="font-size:8pt;">{{$producto->pronom}}<br>Stock: {{$producto->stock}}</font></p>
                    </button><br><br>
                </div>
                @else

                      <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12">
                    <button type="button" id='pro<?php echo $i; ?>'  onclick="agregaritem(this)" value='{{$producto->precio}}' style="background:orange   ;width: 140px; height: 120px; border-radius:10px">
                      <input type="hidden" name="pro_nom" id='pro<?php echo $i;?>nom' value='{{$producto->pronom}}'>
                      <input type="hidden" name="provun" id='pro<?php echo $i;?>id' value='{{$producto->IdProducto}}'>
                    
                      <input type="hidden" name="imagen" id='pro<?php echo $i;?>imagen' value='{{$producto->imagenproducto}}'>
                      <p><font color="white" style="font-size:8pt;">{{$producto->pronom}}<br>Stock: {{$producto->stock}}</font></p>
                    </button><br><br>
                </div>
                @endif
            @endif

      @endif
  
    @endforeach


                </div>

        </div>
      </div>

      <div class="col-lg-6">
        <div class="box" >
               <div class="box-header" style="background-color:blue;">

                   <font color="white"><strong><center>CLIENTE</center></strong></font>
               <div class="box-tools pull-left">
             <a  data-target="#modal-cliente" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">NUEVO CLIENTE</button></a>
          </div>
            </div>
                <div class="box-body"  style="height:15px;min-height:15vh;max-height:15vh;">
			           
                  <div hidden="hidden" class="col-lg-3">
                <div class="form-group form-group-sm">
                    <LABEL>Almacenes</LABEL>
                    <select name="id_almacen" id="id_almacen" class="form-control">
                      @foreach($almacenes as $alm)
                        <option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
                      @endforeach
                    </select>
                </div>
            </div>


                <div id="divcliente">
                  <div class="row form-group form-group-sm">
              <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
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
              
              <div class="col-lg-3" hidden="hidden">
                <div class="form-group form-group-sm">
                  <label for="clinum">Num. Doc</label>
                  <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}"  placeholder="" class="form-control" >

                </div>
              </div>

              
             <div class="col-lg-3" >
              <div class="form-group">
                <label class="control-label">Cliente</label>
                <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="clicod" id="clicod" onchange="seleccionarcliente();">
               
                  @foreach($clientes as $cliente)
                    <option value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}" data-direccion="{{$cliente->clidir}}" data-clinom="{{$cliente->clinom}}" data-correo="{{$cliente->clicor}}" data-telefono="{{$cliente->telefono}}">{{$cliente->clinum}} - {{$cliente->clinom}}</option>
                  @endforeach
                </select>
                <input type="hidden" readonly="readonly" name="clinom" id="clinom">
              </div>
            </div>

              <div class="col-lg-3">
                <div class="form-group form-group-sm">
                  <label>Direcci&oacute;n</label>
                  <input name="clidir" id="clidir" value="--" class="form-control">
                </div>
              </div>
              <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Correo</label>
                  <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
                </div>
              </div>
              
            <div class="col-lg-2">
               <div class="form-group form-group-sm">
                <label>GUIA R.</label>
                <input type="text" name="guia_remision" class="form-control">
             </div>
            </div>
            <div class="col-lg-2">
               <div class="form-group form-group-sm">
                <label>PLACA</label>
                <input type="text" name="placa_comp" class="form-control">
             </div>
            </div>
            
        
              <div hidden="hidden" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Tel&eacute;fono</label>
                  <input name="clitel" id="clitel" value="{{old('clitel')}}" class="form-control">
                </div>
              </div>



         </div>

</div>
            </div>
            <div class="box-body" style="height:65vh; max-height:65vh;max-height:65vh;">
          
                <div class="box-header" style="background-color:blue;">
                   <font color="white"><center><strong>DETALLE</strong></center></font>
                     <div class="box-tools pull-right">
            <!-- <a  data-target="#modal-datos-comprobantes" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">COMPROBANTE</button></a>-->
          </div>
                </div>
                 <div class="box-body">
                   <table  class="table table-hover" id="grdet">
                        <thead>

                      <th>Producto</th>
                      <th>Galon</th>
                      <th hidden="hidden">Unidad</th>
                      <th hidden="hidden">VU</th>
                      <th>PU</th>
                      <th>Total</th>

                    </thead>

                    <tbody>

                    </tbody>
                  </table>
                   <table style="display:none;" class="table table-hover">
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
            <!--<div class="box-body" style="height:25vh;" >
              <table hidden="hidden" style="width:100%;bottom:0px">
                <tr>
                   <td style="padding:5px;"><button type="button" id="1mt" class="btn btn-primary btn-block" onclick="valor(this);" value="1" style="height:80px">1</button></td>
                    <td style="padding:5px;" ><button type="button" id="2mt" class="btn btn-primary btn-block" onclick="valor(this);" value="2"  style="height:80px">2</button></td>
                    <td style="padding:5px;" ><button type="button" id="3mt" class="btn btn-primary btn-block" onclick="valor(this);" value="5" style="height:80px">5</button></td>
                    <td style="padding:5px;" ><button type="button" id="3mt"  class="btn btn-primary btn-block" onclick="valor(this);" value="10" style="height:80px">10</button></td>
                    <td style="padding:5px;" ><button type="button" id="20mt"  class="btn btn-primary btn-block" onclick="valor(this);" value="20" style="height:80px">20</button></td>
                </tr>
                <tr>
                    <td style="padding:5px;" ><button type="button" id="30mt"  class="btn btn-primary btn-block" onclick="valor(this);" value="30"  style="height:80px">30</button></td>
                    <td style="padding:5px;" ><button type="button" id="40mt"  class="btn btn-primary btn-block" onclick="valor(this);" value="40" style="height:80px">40</button></td>
                    <td style="padding:5px;" ><button type="button" id="50mt" class="btn btn-primary btn-block" onclick="valor(this);" value="50" style="height:80px">50</button></td>
                    <td style="padding:5px;" ><button type="button" id="60mt"  class="btn btn-primary btn-block" onclick="valor(this);" value="60" style="height:80px">60</button></td>
                    <td style="padding:5px;" ><button type="button" id="100mt"  class="btn btn-primary btn-block" onclick="valor(this);" value="70" style="height:80px">100</button></td>
                </tr>
              </table>
            </div>-->
        </div>
      </div>

      <div class="col-lg-2">


        <div class="box" style="min-height:85vh;max-height:85vh;" >
           
          <div class="box-header" style="background-color:blue;">
            <font color="white"><center><strong>MONTO A PAGAR</strong></center></font>
         </div>
            <div class="box-body" >
                
  
         <div class="row">
               <div class="col-lg-12">
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

              @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin'))
             <div class="row">
            <div class="col-lg-12">
                <div class="form-group form-group-sm">
                    <LABEL>Estado de Pago</LABEL>
                    <select name="estadopago" id="estadopago" class="form-control">
                      @foreach($creditos as $cre)
                        <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
                      @endforeach
                    </select>
                </div>
            </div>

               <div class="col-lg-12">
                <div class="form-group form-group-sm">
                   <label>F. Emisión</label>
                     <input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
                </div>
                   
               </div>

               <div class="col-lg-12"  id="divfecVen">
                   <div class="form-group form-group-sm">
                    <label>F. Vencim.</label>
                      <input type="date" name="fecVen" id="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
                  </div>
               </div>
             </div>
               @else
               <div class="row" hidden="hidden">
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
                    @endif
          

                   @if($empresa->ticket_pantalla=='1')
      <a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
    @endif
              @if(isset($codfact1))
              <input type="hidden" name="comprobante" id="comprobante" value="{{$codfact}}">
              @endif

              @if(isset($tdocod))
              <input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
              @endif
             

            <div hidden="hidden" class="col-lg-3">
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
          
          <div class="row">
            <div class="col-lg-12">
              <div class="form-group-sm">
                <label>Total</label>
                <input type="text" class="form-control"  id="total" name="total" value='0.00' readonly="readonly">
              </div>
            </div>
              <div  class="col-lg-12">
              <div class="form-group-sm">
                <label>Paga con:</label>
                <input type="number"  step="any" class="form-control"  id="pagar" name="pagar" value="0.00" onkeyup="calculartotal();">
              </div>
            </div>
            <div  class="col-lg-12">
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
              <div class="col-lg-12">
                <div class="form-group form-group-sm">
                  <label>{{$mp->nom_med_pag}}</label>
                  <input class="mediopago form-control" id="predeterminado_{{$mp->predeterminado}}" data-predeterminado="{{$mp->predeterminado}}" name="monto[]" type="number" step="any">
                  <input class="form-control" name="medio[]" type="hidden" value="{{$mp->id_med_pag}}">
                </div>
                  
              </div>
            @endforeach
          </div>
         
        </div>



        </div>
      </div>
   
</div>
     {!!Form::close()!!}
      </div>



@endsection
