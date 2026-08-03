 @extends('layouts.empresas')
@section('contenido')

@include('empresas.clientes.modalcrearcliente')
@include('empresas.puntosventas.modalpresentaciones')
@include('empresas.puntosventas.modalingresarcantidadprecio')
@include('empresas.puntosventas.modalautorizarmodificarprecio')
@include('empresas.puntosventas.modaldirecciones')
@include('empresas.puntosventas.modalclientes')
@include('empresas.puntosventas.modal_catalogo')

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

#modal-autorizar{

  z-index: 999999 !important;
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

   function Calculargasto(){
      var total=0;
      var $svalor=0;
      var item=0;
   
      $("#detgasto tbody tr").each(function(){
          
          item = parseFloat($(this).find("td:eq(2)  > input").val());

    
          total = total + parseFloat(item);

      }) 
       $('#total_gasto').val(total.toFixed(2));
         


  }

  function agregarlineagasto(){
            var iCnt = 0;
            iCnt = iCnt + 1;
          
            $('#detgasto').append('<tr><td><select class="form-control input-sm" name="tip_gas[]">@foreach($gastos as $gas) <option value="{{$gas->tip_gas_id}}">{{$gas->tip_gas_nom}}</option>  @endforeach</select></td><td><input onkeypress="if (event.keyCode == 13) enviar_formulario(); if(event.keyCode == 45) deleteRow(this);" class="detpro form-control input-sm" name="detpro[]" id="detpro" size="100" ></td><td ><input type="number" step="any" class="form-control input-sm preuni" size="20px" id="preuni" name="preuni[]" onChange="Calculargasto();"   OnKeyUp="Calculargasto();" onKeypress="if(event.keyCode == 45) deleteRow(this);"  style="text-align:right;" name="preuni[]"/></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

            
            
  }

  function agregarcuota(){
     
          
            $('#detcuotas').append('<tr><td><input type="date" name="fec_cuo[]" class="form-control input-sm" value="{{Carbon::now()->format("Y-m-d")}}"></td><td><input name="mon_cuo[]" type="number" step="any" class="form-control input-sm"></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

            
            
  }


   $(document).ready(function()
   {


      $("#id_empresa_negocio").change(function() {
         
      var id_empresa_negocio = $("#id_empresa_negocio").val();
      $("#div_almacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/almacenpredeterminado/"+id_empresa_negocio,
        }).done(function(respuesta){
          $("#div_almacen").html(respuesta.vista); 
        });

      });
      
       $('#pre_producto').attr('readonly', true);

   $('#modal-cantidad-precio').on('shown.bs.modal', function() { $("#can_producto").focus(); })
    $('#modal-presentaciones').on('shown.bs.modal', function() { $("#table-presentaciones .btn:first").focus(); })

        $("#modal-cantidad-precio").on('hidden.bs.modal', function () {
           actualizarpro();
    });

   $("#producto").focus();

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
                    "pro_cod":response.codigo,
                    "icbper":response.icbper,
                    "mon_icbper":response.mon_icbper
                  }
                  

                })

               
            };

               

        },
            cache:false
        }
    
});

     $("#producto").select2('open');

     $(".selectpicker").selectpicker({


     });

                 $("#desubigeopartida").autocomplete({
                  source: '{!!URL::route('buscarubigeo')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  
                  select: function(event,ui) {
                     $('#ubigeopartida').val(ui.item.codubigeo);
                   
                  }
                })


                 $("#desubigeollegada").autocomplete({
                  source: '{!!URL::route('buscarubigeo')!!}',
                  dataType: "json",
                  minLength: 3,
                  autoFocus:true,
                  
                  select: function(event,ui) {
                     $('#ubigeollegada').val(ui.item.codubigeo);
                    
                  }
                })



      $("#btnguia").on("click", function() {


    
      var formulario = $("#formfact").serializeArray();
      $("#imgloadguia").show();
      $(".botonesguia").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/registrarguia',
    data: formulario,
      }).done(function(respuesta){


        if(respuesta.estado =='error'){
          alert(respuesta.mensaje);

          $("#imgloadguia").hide();
          $(".botonesguia").show();
        }else{
    
          $("#imgloadguia").hide();
       $(".botonesguia").show();
       alert('GUIA REGISTRADA');
      
   $("#modal-guia").modal("hide");

    window.location.href = "/guiasremision";

        }

      });

    });
  
  

     $("#btncancelar").on("click", function(){

        $("#emit_gui").val('0');

        
    })


    $("#btngasto").on("click", function(){

      var formulario = $("#formgasto").serializeArray();
      $("#imgloadgasto").show();
      $("#botonesgasto").hide();
      $.ajax({
        type: "POST",
        dataType: 'json',
        url: '/gastos',
        data: formulario,
      }).done(function(respuesta){

          if(respuesta.estado =='error'){
            alert(respuesta.mensaje);
            
            $("#imgloadgasto").hide();
            $("#botonesgasto").show();

          }else{

              alert(respuesta.mensaje)
              $("#modal-gasto").modal("hide");
              $("#imgloadgasto").hide();
              $("#botonesgasto").show();

   
          }

      });

    });


  

      $(".mediopago").val('0');

//      $('#tdicod').val('1');

     /* if($('#tdicod').val() =='6' ){

            $('#factura').prop("checked",true);
      }


      if($('#tdicod').val() =='1'){
        $('#boleta').prop("checked",true);
      }*/

      
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
       var nuevafecha = $('#fecEmi').val();

          if(metodo=='CREDITO'){

            $("#divmediopago").hide('true');
            $(".mediopago").val('0');
            $("#divfecVen").hide('true');
            $("#fecVen").val(nuevafecha);
            $("#divcuotas").show('true');

          }

          if(metodo =='CONTADO'){
            $("#divmediopago").show('true');
            $("#divfecVen").hide('true');
            $("#fecVen").val($("#fecEmi").val());
            $('#predeterminado_1').val($('#total').val());
            $("#divcuotas").hide('true');
          }

          if(metodo =='PERSONALIZADO'){
            $("#divmediopago").hide('true');
            $(".mediopago").val('0');
            $("#divfecVen").show('true');
            $("#divcuotas").show('true');
          }


          $("#estadopago").on("change", function() {
            var metodo = $(this).find(':selected').attr('data-medio');
            var dias = $(this).find(':selected').attr('data-dias');
            var nuevafecha = $('#fecEmi').val();

            if(metodo=='CREDITO'){
              $("#divmediopago").hide('true');
              $(".mediopago").val('0');
              $("#divfecVen").hide('true');
              $("#fecVen").val(nuevafecha);
              $("#divcuotas").show('true');
            }

            if(metodo =='CONTADO'){
              $("#divmediopago").show('true');
              $("#divfecVen").hide('true');
              $("#divcuotas").hide('true');
              $("#fecVen").val($("#fecEmi").val());
              $('#predeterminado_1').val($('#total').val());
            }

            if(metodo =='PERSONALIZADO'){
              $("#divmediopago").hide('true');
              $(".mediopago").val('0');
              $("#divfecVen").show('true');
                 $("#divcuotas").show('true');
            }
          });


    
    $("#num_ped").focus();

   // $('#clinum').val('00000000');
 //  $('#clinom').val('Varios');






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

            alert(respuesta.mensaje);

            $("#imgload").hide();
            $(".botones").show();

        }else{

            if(respuesta.tdocod=='15'){
                alert('NUMERO DE PROFORMA: '+respuesta.numero)
                window.location.href = "/pos";
            }else{
                window.location.href = "/pos/"+respuesta.codfact;
            }
          
           
 
        }

         

      });

    });



    $("#btnRegImp").on("click", function() {

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

            if(respuesta.tdocod=='15'){
                alert('NUMERO DE PROFORMA: '+respuesta.numero)
                window.location.href = "/pos";
            }else{
                window.location.href = "/pos/"+respuesta.codfact;
            }
          
           
 
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

                $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden' ><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' readonly='readonly'   value='"+data[0].propun+"'  style='width:130px' ></td><td><input type='text' class='form-control' name='itemtotal[]' onkeyup='CalcularItem(this);'  value='"+data[0].propun+"'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");


              }

            }else{

              var igvitem = data[0].propun -data[0].provun;
              $('#grdet').append("<tr><td width='900px'><input type='text' class='form-control' name='pronom[]' value='"+data[0].pronom+"' readonly='readonly'></td><td> <input type='text' value='1' name='cant[]' onChange='Calcular(this);' onkeyup='Calcular(this);' onChange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'> </td><td hidden='hidden'  ><select style='width:100px' name='unid[]'  class='form-control input-sm'> @foreach($unidades as $und) @if($und->umecod == 'UNI') <option  selected='selected' value='{{$und->umecod}}'>{{$und->umenom}}</option> @else <option  value='{{$und->umecod}}'>{{$und->umenom}}</option> @endif @endforeach </select></td><td hidden='hidden'><input type='text' class='form-control' name='provun[]'  value='"+data[0].provun+"' readonly='readonly' style='width:130px' ></td><td><input type='text' class='form-control' name='propun[]' readonly='readonly'   value='"+data[0].propun+"'  style='width:130px' ></td><td><input type='text' class='form-control'   name='itemtotal[]' onkeyup='CalcularItem(this);'  value='"+data[0].propun+"'  style='width:130px' ></td><td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+data[0].proid+"' readonly='readonly' style='width:130px' ></td><td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
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

   $("#pre_producto").dblclick(function(){
        
       

        $("#modal-autorizar").modal("show");

   
    });





  $('#btnAutorizar').click(function(e) {
          
        
          var codigo = $("#codigo_aut").val();
          var usuario = $("#usuario_aut").val();

        $.ajax({
            type: "GET",
            url: "/autorizarmodificarprecio/"+codigo+"/"+usuario,
            success: function(response) { 

                if(response.AUTORIZADO=='true'){

                  $('#pre_producto').attr('readonly', false);
                  $("#codigo_aut").val("");
                   $("#modal-autorizar").modal("hide");

                }else{

                  alert(response.mensaje);
                 
                }
            }
          });



         

        });






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
   var documento = $("#documento").val();
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

    $(this).find("td:eq(3) > input").val(totitemgrav.toFixed(2));

  });
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

  var ped_cli_num = $("#ped_num_doc").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+ped_cli_num,

  }).done(function(respuesta){

    if(respuesta.error){   
      alert(respuesta.error);
      $("#imgloadcliente").hide();
    }else{

      $('#ped_cli_nom').val(respuesta[0].nom);
      $('#ped_dir').val(respuesta[0].dir);
      $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');
      $("#imgloadcliente").hide();
      $(".botones").show(); 
    }

  });

}


function  buscarclienteruc(){

  var formulario = $("#clinum").val();
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

      $('#clinom').val(respuesta[0].nom);
      $('#clidir').val(respuesta[0].dir);
      //  $('#cliteln').val(respuesta[0].telefono);
      $('#clicor').val(respuesta[0].cor);
        
      $('#clicod').val(respuesta[0].clicod);
      $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

      if($('#tdicod').val() =='6' ){
        $("#tdocod").val('01').attr('selected', 'selected');
      }

      if($('#tdicod').val() =='1' ){
        $("#tdocod").val('03').attr('selected', 'selected');
      }

      $("#imgloadcliente").hide();
        // $(".botones").show(); 

       }


    });



}


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



function buscarclientenombre(){

  id = $("#clinom").val();

  $("#modal-lista-clientes").modal("show");

  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/buscarclientenombre/"+id,

  }).done(function(respuesta){
    $("#clientes").html(respuesta.vista);
  });


}


function seleccionardireccion(){

  id = $("#clicod").val();

  $("#modal-direcciones").modal("show");

  $.ajax({
    type: "GET",
    dataType: 'json',
    url: "/seleccionardireccion/"+id,

  }).done(function(respuesta){
    $("#direcciones").html(respuesta.vista);
  });


}


function agregardireccion(direccion){


  $("#clidir").val(direccion);

  $("#modal-direcciones").modal("hide");

}


function agregarcliente(clicod,clinum,clinom,clidir,tdicod,clicor){


  $('#clinom').val(clinom);
  $('#clinum').val(clinum);
  $('#clidir').val(clidir);
  $('#clicor').val(clicor); 
  $('#clicod').val(clicod);
  $("#tdicod").val(tdicod).attr('selected', 'selected');

  if($('#tdicod').val() =='6' ){
    $("#tdocod").val('01').attr('selected', 'selected');
  }

  if($('#tdicod').val() =='1' ){
    $("#tdocod").val('03').attr('selected', 'selected');
  }

  $("#modal-lista-clientes").modal("hide");

}



function  buscartransportista(){


  var formulario = $("#transportistanum").val();


  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#transportistanom').val(respuesta[0].nom);
   $("#transportistatdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

  // $("#imgloadcliente").hide();
   // $(".botones").show();
          
  });

  

}

function  buscarconductor(){


  var formulario = $("#conductornum").val();
  $("#imgloadcliente").show();

  $.ajax({
    type: "get",
    dataType: 'json',
    url: '/autocomplete/'+formulario,

  }).done(function(respuesta){



   $('#conductornom').val(respuesta[0].nom);

   $("#conductortdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

          
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

     var totigv = 0,totgrav=0 ,subtotal=0, tot_icbper;

     $("#grdet tbody tr").each(function(){

      totgrav = totgrav + parseFloat($(this).find("td:eq(6)  > input").val()) + parseFloat($(this).find("td:eq(2)  > input").val()*$(this).find("td:eq(11)  > input").val());

      tot_icbper = parseFloat($(this).find("td:eq(2)  > input").val()*$(this).find("td:eq(11)  > input").val());
//  subtotal = subtotal + ($(this).find("td:eq(1) > input").val() *parseFloat(($(this).find("td:eq(4) > input").val()))/(1.18));

//  totigv = totgrav - subtotal;

$('#total').val(totgrav.toFixed(2));
$('#tot_icbper').val(tot_icbper.toFixed(2));
$('#predeterminado_1').val(totgrav.toFixed(2));
 // $('#igv').val(totigv.toFixed(2));
 // $('#subtotal').val(subtotal.toFixed(2));
})


     if ($('#grdet >tbody >tr').length == 0){
      $('#total').val('0.00');
      $('#tot_icbper').val('0.00');
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
    '<td><input type="number" step="any" min="0" value="1" name="cant[]" onkeyup="Calcular(this);" onchange="Calcular(this);" class="form-control input-sm cant" id="font-size" style="width:60px"> </td><td ><input readonly="readonly"  value="'+unidad+'" style="width:100px" name="unid[]"  class="form-control input-sm"></td><td><input  type="number" step="any" min="0" class="form-control input-sm" name="propun[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="'+precio+'" style="width:80px" ></td><td hidden="hidden" ><input  type="number" step="any" min="0" class="form-control input-sm" name="desc[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="0.00" style="width:80px" ></td><td><input  type="text" class="form-control" name="itemtotal[]"  value="'+total+'" onkeyup="CalcularItem(this);" style="width:80px"></td><td hidden="hidden"><input  type="number" readonly="readonly" step="any" min="0" class="form-control input-sm" name="precio[]"  value="'+precio+'" style="width:80px" ></td><td hidden="hidden"><input type="text" class="form-control" name="proid[]"  value="'+proid+'" readonly="readonly" ></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

 
    calculartotal();
 
    $("#modal-presentaciones").modal("hide");
   



  }

 function agregarnota(button){


  $('#grdet').append("<tr>"+
    "<td width='1300px'><input type='text' class='form-control input-sm btn-block' name='pronom[]' value='' ></td>"+
    "<td> <input type='number' step='any' min='0' value='1'  name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='cant form-control input-sm ' id='font-size' style='width:60px'> </td>"+
    "<td><select  style='width:100px' name='unid[]'  class='form-control input-sm'>@foreach($unidades as $uni) @if($uni->umecod =='NIU') <option selected='selected' value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @else <option  value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @endif @endforeach</select></td>"+
    "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
    "<td hidden='hidden' ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
    "<td><input  type='text' class='form-control input-sm' name='itemtotal[]'  value='0.00' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
     '<td hidden="hidden"><input  type="number" readonly="readonly" step="any" min="0" class="form-control input-sm" name="precio[]"  value="0" style="width:80px" ></td>'+
    "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='0' readonly='readonly' ></td>"+
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
    var icbper = $('#icbper').val();
    var mon_icbper = $('#mon_icbper').val();
    var codigo = $('#cod_producto').val();

    var total = cantidad*precio;

    var  precio_ref =  $('#pre_producto_ref').val();

    
  
        if(precio_ref===undefined){
          $('#grdet').append("<tr>"+
           "<td width='200px' ><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
           "<td width='900px'><input type='text' class='form-control input-sm ' name='pronom[]' value='"+producto+"'></td>"+
           "<td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
           "<td width='80px' ><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'></td>"+
           "<td><input  type='number' step='any' min='0' class='form-control input-sm'  name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'  style='width:80px' ></td>"+
           "<td hidden='hidden'  ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
           "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
           "<td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='"+icbper+"' readonly='readonly' ></td>"+
           "<td hidden='hidden'  ><input type='text' class='form-control' name='mon_icbper[]'  value='"+mon_icbper+"' readonly='readonly' ></td>"+
           "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='propunref[]'  value='"+precio+"' style='width:80px' ></td>"+
           "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
          
          actualizarpro();

          calculartotal();
          $("#can_producto").val('1'); 
          $("#modal-cantidad-precio").modal("hide");


        }else{

          if( parseFloat(precio_ref)<= parseFloat(precio)){
            $('#grdet').append("<tr>"+
             "<td width=200px'><input type='text'  readonly='readonly'  class='form-control input-sm'  value='"+codigo+"'></td>"+
             "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"'></td>"+
             "<td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
             "<td width='80px'  ><input readonly='readonly'  value='"+unidad+"' name='unid[]'  class='form-control input-sm'></td>"+
             "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'  style='width:80px' ></td>"+
             "<td hidden='hidden' ><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
             "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly' value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
             "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
             "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
             "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
             "<td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='"+icbper+"' readonly='readonly' ></td>"+
             "<td hidden='hidden' ><input type='text' class='form-control' name='mon_icbper[]'  value='"+mon_icbper+"' readonly='readonly' ></td>"+
             "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='propunref[]'  value='"+precio+"' style='width:80px' ></td>"+
             "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
            
            actualizarpro();

            calculartotal();
            $("#modal-cantidad-precio").modal("hide");
            $("#can_producto").val('1'); 
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
        var icbper = $('#producto').select2('data')[0].icbper;
        var mon_icbper = $('#producto').select2('data')[0].mon_icbper;

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
        $("#id_almacen_pro").val(id_almacen_pro);
        $("#cod_producto").val(cod_producto);
        $("#icbper").val(icbper);
        $("#mon_icbper").val(mon_icbper);
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
  


   function buscarpedido(){

    
    var pedido = $("#num_ped").val();
    var tipo = $("#tipo_comprobante").val();

    window.location.href = "/buscarpedido/"+pedido+"/"+tipo;

   


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
      <input type="hidden" name="tipo_desc" id="tipo_desc" value="{{$datos->tipo_desc}}">
    <div class="row" hidden="hidden">
      <div class="col-lg-12">
         <div class="box">
           <div class="box-header with-border  form-group form-group-sm" style="background-color:#337ab7">
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

    <div class="col-lg-12" style="display:none;"> 
        <div class="box">
          <div class="box-body">
            <div class="col-lg-3" hidden="hidden">
            
                  <select class="form-control" name="tipo_comprobante" id="tipo_comprobante">
             <option value="16">PEDIDO</option>
             @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin') || Auth::User()->IdUsuario=='41')
                 <option value="15">PROFORMA</option>
              
           @endif
              </select>

           
            </div>
            <div class="col-lg-12">
               <input type="number" onkeypress="if(event.keyCode == 13) buscarpedido();"  class="form-control" value="" placeholder="INGRESAR NUMERO DE PEDIDO" >
            
               
            </div>
            
          </div>
        </div>
    </div>

  
         @if($empresa->ticket_pantalla==1)
      <a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
    @endif
              @if(isset($codfact))
              <input type="hidden" name="comprobante" id="comprobante" value="{{$codfact}}">
              @endif

              @if(isset($tdocod))
              <input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
              @endif
             
 <div class="col-lg-7">
    <div class="row">
     
            <div class="col-lg-12">
              <div class="box">
                 <div class="box-header" style="background-color:#337ab7;">
                    <font color="white"><strong><center>{{$datos->tipo_negocio}}</center></strong></font>

                    <div class="box-tools pull-right">
                      <a  data-target="#modal-guia" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">GUIA</button></a>
                        <a  data-target="#modal-gasto" data-toggle="modal"><button type="button" class="btn btn-warning btn-sm">GASTO / INGRESO</button></a>

                       
                    </div>

                 </div>
                <div class="box-header with-border form-group-sm">
                  
                  <div  class="col-lg-2">
                    <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
                  </div>
                  <div  class="col-lg-10">
                    <div class="form-group form-group-sm" id="divactpro">
                        <select style=" font-weight: bold;" class="form-control input-sm" onkeypress="if(event.keyCode == 13) ingresar_cantidad_precio();" onchange="ingresar_cantidad_precio();" name="producto" id="producto">
                         
                     </select>
                    </div>
                   
                  </div>
                
                </div>
               
              </div>
            </div>

     
 
      <div class="col-lg-12">
              <div class="box">
                <div class="box-header" style="background-color:#337ab7;">
                   <font color="white"><center><strong>DETALLE</strong></center></font>
                </div>
                 <div class="box-body">
                   <table class="table table-hover" id="grdet">
                        <thead>

                      <th>Código</th>
                      <th>Producto</th>
                      <th>Cantidad</th>
                      <th >Unidad</th>
                      <th>PU</th>
                      <th hidden="hidden" >Desc.</th>
                      <th>Total</th>
                         
                      <th><button type="button" onClick="agregarnota();" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
                    </thead>

                    <tbody>
               

                      @foreach($detalle as $det)
                        <tr>
                            <td width='200px'><input type='text' readonly="readonly"  class='form-control input-sm' name='procod[]' value='{{$det->procod}}'></td>
                          <td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='{{$det->cdedes}}'></td>
                          <td> <input type='number' step='any' min='0' value='{{$det->cdecan}}' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm' id='font-size' style='width:60px'> </td>
                          <td ><input readonly="readonly"  value="{{$det->umecod}}" style="width:100px" name="unid[]"  class="form-control input-sm"></td>
                          <td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='{{$det->cdepuni}}' style='width:80px' ></td> 
                          <td hidden="hidden"> @if($datos->tipo_desc=='1') <input  type="number" step="any" min="0" class="form-control input-sm" name="desc[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="{{$det->desc_mon}}" style="width:80px" > @else <input  type="number" step="any" min="0" class="form-control input-sm" name="desc[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="{{$det->por_des}}" style="width:80px"> @endif</td>
                          <td><input  type='text' readonly="readonly" class='form-control input-sm' name='itemtotal[]'  value='{{$det->cdevve}}' onkeyup='CalcularItem(this);' style='width:80px' ></td>
                          <td hidden="hidden"><input  type="number" readonly="readonly" step="any" min="0" class="form-control input-sm" name="precio[]"  value="{{$det->cdepuni}}" style="width:80px" ></td>
                          <td hidden='hidden'><input type='text' class='form-control input-sm' name='proid[]'  value='@if(empty($det->IdProducto))  0 @else {{$det->IdProducto}} @endif' readonly='readonly' ></td>
                           <td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='{{$det->id_almacen_pro}}' readonly='readonly' ></td>
                          <td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='{{$det->icbper}}' readonly='readonly' ></td>
                           <td hidden='hidden' ><input type='text' class='form-control' name='mon_icbper[]'  value='{{$det->mon_icbper_det}}' readonly='readonly' ></td>
                    <td hidden="hidden" ><input  type='number' step='any'  class='form-control input-sm' name='propunref[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' min='0' value='{{$det->precio_ref}}' style='width:80px' ></td>
                          <td ><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td>
                          <td hidden="hidden"><input type="hidden" readonly="readonly" name="IdCpe_detalle[]" value="{{$det->IdCpe_detalle}}"></td>
                          
                        </tr>
                      @endforeach
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

<div class="col-lg-5">
  <div class="col-lg-12">
        <div class="box">
    
         <div class="box-header" style="background-color:#337ab7;">
              <font color="white"><center><strong>DATOS DEL COMPROBANTE</strong></center></font>
         </div>
         <div class="box-body">
             

        <input type="hidden" readonly="readonly" name="ped_id" value="{{$cabecera->IdCpe_cabecera}}">
             <div class="row">
                 <div  class="col-lg-3" hidden="hidden">
                       <div class="form-group form-group-sm">
                        <LABEL>N° PEDIDO</LABEL>
                     <input type="number" name="num_ped" id="num_ped" onkeypress="if(event.keyCode == 13) buscarpedido();"  class="form-control" value="" placeholder="INGRESAR NUMERO DE PEDIDO" >
                     </div>
                     
                   </div>
                   

        <div class="col-lg-3" hidden="hidden">
          <div class="form-group form-group-sm">
            <LABEL>Sucursales</LABEL>
            <select name="id_empresa_negocio" id="id_empresa_negocio" class="form-control">
              @foreach($sucursal as $suc)
                <option value="{{$suc->id_empresa_negocio}}">{{$suc->tipo_negocio}}</option>
              @endforeach
            </select>
          </div>
        </div>


      <div class="col-lg-3" id="div_almacen" hidden="hidden">
        <div class="form-group form-group-sm">
          <LABEL>Almacenes</LABEL>
          <select name="id_almacen" id="id_almacen" class="form-control">
            @foreach($almacen as $alm)
            <option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
            @endforeach
          </select>
        </div>
      </div>

              <div class="col-lg-3" hidden="hidden">
                <div class="form-group form-group-sm">
                    <LABEL>Vendedor</LABEL>
                    <select name="vendedor" id="vendedor" class="form-control">
                      @foreach($vendedores as $ven)
                       @if($ven->IdUsuario == $cabecera->IdUsuario_ven)
                        <option selected="selected" value="{{$ven->IdUsuario}}">{{$ven->name}} {{$ven->apeusu}}</option>
                       @else
                        <option value="{{$ven->IdUsuario}}">{{$ven->name}} {{$ven->apeusu}}</option>
                       @endif
                      @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="form-group form-group-sm">
                    <LABEL>Estado de Pago</LABEL>
                    <select name="estadopago" id="estadopago" class="form-control">
                      @foreach($creditos as $cre)
                       @if($cre->cre_dia_id == $cabecera->cre_dia_id)
                            <option selected="selected" value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
                       @else
                            <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>

                       @endif
                    
                      @endforeach
                    </select>
                </div>
            </div>


                          <div class="col-lg-5">
                     <div class="form-group form-group-sm">
                    <label>Comprobante</label>
                    <select class="form-control" name="tdocod" id="tdocod">
                      @foreach($comprobante as $comp)

                        @if($comp->tdocod == $cabecera->tdocod_fac)
                          <option selected="selected" value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
                        @else
                           <option value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
                        @endif
                      @endforeach
                    </select>
              
                </div>
                </div>


               <div class="col-lg-4">
                <div class="form-group form-group-sm">
                   <label>F. Emisión</label>
                     <input  type="date" id="fecEmi" name="fecEmi" value="{{$cabecera->ccafem}}" class="form-control">
                </div>
                   
               </div>

               <div class="col-lg-3"  id="divfecVen">
                   <div class="form-group form-group-sm">
                    <label>F. Vencim.</label>
                      <input type="date" name="fecVen" id="fecVen" value="{{$cabecera->ccafve}}"  class="form-control">
                  </div>
               </div>


              <div  class="col-lg-4" id="divcuotas">
                <div class="form-group form-group-sm">
                  
                   <br><button type="button" class="btn btn-sm btn-primary"  data-target="#modal-cuotas" data-toggle="modal">CUOTAS</button>
                </div>
              

              </div>

             </div>
          
       
             
                   <div class="row">
            
               
                   
                  

              <div style="display:none;" class="col-lg-4">
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
         <div class="box-header" style="background-color:#337ab7;">
            <font color="white"><center><strong>DATOS DEL CLIENTE</strong></center></font>
          
         </div>
         <div class="box-body" id="divcliente">
             <div class="row form-group form-group-sm">
             
             <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
                  <div class="form-group form-group-sm">
                    <label>Documento</label>
                    <select name="tdicod" id="tdicod" class="form-control">
                      @foreach($documentos as $doc)
                          @if($doc->tdicod == $cabecera->tdicod)
                          <option selected="selected" value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
                          @else

                          <option value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
                          @endif
                      @endforeach
                    </select>
                  </div>
                </div>
                <div class="col-lg-3">
                  <div class="form-group">
                    <label>N° Documento</label>
                    <div class="input-group input-group-sm">
                      <input name="clinum" id="clinum" value="{{$cabecera->ccandi}}" class=" form-control" onkeypress="if(event.keyCode == 13) buscarclienteruc();">
                      <input type="hidden" name="clicod" id="clicod"  class="form-control">
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-primary btn-flat" onclick="buscarclienteruc();"><span class="fa fa-search"></span></button>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group">
                    <label>Cliente</label><center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadcliente" id="imgloadcliente"></center>
                    <div class="input-group input-group-sm">
                      <input name="clinom" id="clinom" value="{{$cabecera->ccanom}}" class=" form-control input-xs" onkeypress="if(event.keyCode == 13) buscarclientenombre();">
                      <span class="input-group-btn">
                        <button type="button" class="btn btn-primary btn-flat" onclick="buscarclientenombre();"><span class="fa fa-search"></span></button>
                      </span>
                    </div>
                  </div>
                </div>
                <div class="col-lg-6">
                  <div class="form-group form-group-sm">
                    <label>Direcci&oacute;n</label>
                    <div class="input-group input-group-sm">
                      <input name="clidir" id="clidir" value="{{$cabecera->direccion}}" class=" form-control">
                      <span class="input-group-btn">
                        <button type="button" class="input btn btn-primary btn-flat" id="clidiradic" onclick="seleccionardireccion();"><span class="fa fa-search"></span></button>
                      </span>
                    </div>
                  </div>
                </div>


              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label>Correo</label>
                  <input name="clicor" id="clicor" value="{{$cabecera->clicorcli}}" class="form-control">
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

        <div class="box-header" style="background-color:#337ab7;">
            <font color="white"><center><strong>MONTO A PAGAR</strong></center></font>
         </div>
          <div class="box-body">
      
          <br>
          <div class="row">
              <div class="col-lg-3">
              <div class="form-group-sm">
                <label>ICBPER</label>
                <input type="text" class="form-control" style="font-size:16pt;font-weight:bold;" id="tot_icbper" name="tot_icbper" value='0.00' readonly="readonly">
              </div>
            </div>
            <div class="col-lg-3">
              <div class="form-group-sm">
                <label>Total</label>
                <input type="text" class="form-control"  style="font-size:16pt;font-weight:bold;"   id="total" name="total" value='{{$cabecera->ccaitv}}' readonly="readonly">
              </div>
            </div>
              <div  class="col-lg-3">
              <div class="form-group-sm">
                <label>Paga con:</label>
                <input type="number"  step="any"  style="font-size:16pt;font-weight:bold;"  class="form-control"  id="pagar" name="pagar" value="0.00" onkeyup="calculartotal();">
              </div>
            </div>
            <div  class="col-lg-3">
              <div class="form-group-sm">
                <label>Vuelto</label>
                <input type="text" class="form-control"   style="font-size:16pt;font-weight:bold;"   id="vuelto" name="vuelto" value="0.00" readonly="readonly">
              </div>
            </div>
         
            <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
            </div>
        
          <br>

          <div class="row" id="divmediopago">
            @foreach($mediospagos as $mp)
              <div class="col-lg-3">
                <div class="form-group form-group-sm">
                  <label>{{$mp->nom_med_pag}}</label>
                  <input class="mediopago form-control"  style="font-size:16pt;font-weight:bold;"  id="predeterminado_{{$mp->predeterminado}}" data-predeterminado="{{$mp->predeterminado}}" name="monto[]" value="@if($mp->predeterminado =='1'){{$cabecera->ccaitv}}@else 0.00 @endif" type="number" step="any">
                  <input class="form-control" name="medio[]" type="hidden" value="{{$mp->id_med_pag}}">
                </div>
                  
              </div>
            @endforeach
          </div>
          <div class="row">
              @if($cabecera->tdocod =='15' || $cabecera->tdocod =='16')
            <div class="col-lg-4">
           
            
               <button type="button" id="btnRegImp" class=" btn btn-block btn-success btn-lg botones">IMPRIMIR</button>
           
          
              
       
       <br>

            </div>
             <div class="col-lg-4">
           
            
               <button type="button" id="btnRegComp" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button>
           
          
              
       
       <br>

            </div>
         @endif

             <div class="col-lg-4">
                   
             @if($cabecera->tdocod =='01' || $cabecera->tdocod =='03'  || $cabecera->tdocod =='13' || $cabecera->tdocod =='15')

                 <button type="button" id="btnRegCompAct" class=" btn btn-block btn-warning btn-lg botones">ACTUALIZAR</button>
         
             @endif
            </div>
            <!---  <div class="col-lg-4">
                 @if($cabecera->tdocod !='15')
               <button type="button" id="btnRegCompRegAct" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button>
             
             @endif
             <br>
            </div>-->
            
         
   

             <div class="col-lg-4">
              <a href="/pos"><button type="button" class=" btn btn-block btn-danger btn-lg botones">SALIR</button></a><br>
            </div>
          </div>
        </div>

        <input type="hidden" readonly="readonly" name="emit_gui" id="emit_gui" value="0">

   
     </div>
</div>
</div>
@include('empresas.cuentascobrar.modalcuotas')
</div>

@include('empresas.puntosventas.modalguia')


{!!Form::close()!!}
</div>

</div>
@include('empresas.puntosventas.modalgasto')

@endsection
