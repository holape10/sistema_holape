@extends('layouts.empresas')
@section('contenido')

@include('empresas.clientes.modalcrearcliente')
@include('empresas.puntosventas.modalpresentaciones')
@include('empresas.puntosventas.modaldirecciones')
@include('empresas.puntosventas.modalingresarcantidadprecio')


@if(!empty($codfact) && $datos->ticket_pantalla=='1' && $datos->formato=='A4')

@php
$pdf = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
@endphp

@endif

@include('empresas.puntosventas.modalpdf')

<link rel="stylesheet" href="{{ asset('css/sweetalert2/sweetalert2.min.css') }}">
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<style>
  #modal-pdf{
   z-index: 99999 !important;
 }


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

.select2-container--default .select2-results > .select2-results__options {
    max-height: 700px;
    max-width: 1500px;
    overflow-y: auto;
    font-weight: bold;

    }


    .select2-results > .select2-results__options > li {
    /*margin-bottom: 2px;*/
    margin-bottom: 2px !important;

    }

    .select2-container .select2-selection--single {
        height: 60px !important; 
        display: flex;
        align-items: center;
    }

    .select2-container--open .select2-dropdown {
    /* 1. Definimos que ocupe el 90% del ancho de la pantalla */
    width: 90vw !important; 
    
    /* 2. TRUCO PARA CENTRAR: */
    /* Lo mandamos a la mitad y luego lo regresamos su propia mitad */
    position: fixed !important; 
    left: 50% !important;
    transform: translateX(-50%) !important;
    margin-left: 0 !important; /* Quitamos el anterior que nos malogró la vista */
    
    /* 3. Ajustamos la altura para que no tape el buscador */
    /* Si ves que tapa el cuadro donde escribes, aumenta este número */
    top: 120px !important; 

    /* Diseño 'Hola P' */
    border: 3px solid #3c8dbc !important; 
    box-shadow: 0px 20px 60px rgba(0,0,0,0.6) !important;
    background-color: white !important;
    z-index: 9999999 !important;
}

    .select2-results__options {
        max-height: 500px !important; 
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        font-size: 28px !important; /* <--- AQUÍ AGRANDAS MÁS */
        line-height: 55px !important;
        font-weight: bold;
    }

    /* Agranda la letra de la lista de resultados al escribir */
    .select2-results__option {
    font-size: 26px !important; 
    /* El primer número (8px) es arriba/abajo, el segundo (20px) es a los lados */
    padding: 8px 20px !important; 
    border-bottom: 1px solid #f4f4f4;
}

  .select2-results__option--highlighted {
    background-color: #3c8dbc !important;
    color: white !important;
}

    /* Agranda el cuadro de texto donde escribes dentro del buscador */
    .select2-search--dropdown .select2-search__field {
        font-size: 22px !important;
        height: 45px !important;
    }

/* ESTILOS ADICIONALES PARA EL NUEVO SELECT DE PAGOS */
.select2-container .select2-selection--single {
  height: 34px !important;
  border: 1px solid #ccc;
  border-radius: 4px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
  line-height: 32px !important;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
  height: 32px !important;
}

</style>

<body>
  @if(!empty($codfact) && $datos->ticket_pantalla=='1' && $datos->formato=='A4')
  <script>

   $(document).ready(function()
   {

     $("#modal-pdf").modal("show");
   });
 </script>
 @endif


 <script>

  function validarMediosPago() {
    var metodo = $('#estadopago').find(':selected').attr('data-medio');

    var metodosCredito = ['CREDITO', 'credito', 'Credito', 'CRÉDITO', 'crédito'];
    if (metodosCredito.includes(metodo) || $('#divmediopago').is(':hidden')) {
        return true;
    }

    // AHORA SÍ LEEMOS EL TOTAL FINAL DEL INPUT (Ej. 208)
    var totalACobrarFinal = parseFloat($('#total').val()) || 0;

    var sumaPagado = 0;
    $('.fila-medio-pago').each(function() {
        var $input = $(this).find('.input-monto-medio');
        var monto = parseFloat($input.val()) || 0;
        var comisionPorcentaje = parseFloat($input.data('comision')) || 0;
        sumaPagado += monto + (monto * (comisionPorcentaje / 100));
    });

    var diferencia = Math.abs(totalACobrarFinal - sumaPagado);

    if (diferencia > 0.01) {
        var mensaje = 'Hay una diferencia entre el total y los medios de pago.\n\n';
        mensaje += 'Total a cobrar (incl. comisiones): S/ ' + totalACobrarFinal.toFixed(2) + '\n';
        mensaje += 'Suma ingresada: S/ ' + sumaPagado.toFixed(2) + '\n';
        mensaje += 'Diferencia: S/ ' + diferencia.toFixed(2);
        
        Swal.fire({
            icon: 'error',
            title: 'Error en medios de pago',
            text: mensaje,
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    return true;
}
  //NUEVO
  /*function validarMediosPago() {
    var metodo = $('#estadopago').find(':selected').attr('data-medio');

    var metodosCredito = ['CREDITO', 'credito', 'Credito', 'CRÉDITO', 'crédito'];
    if (metodosCredito.includes(metodo) || $('#divmediopago').is(':hidden')) {
        return true;
    }

    var total = parseFloat($('#total').val()) || 0;
    var descuentoGlobal = parseFloat($('#descuento_global').val()) || 0;
    var montoDescuento = total * (descuentoGlobal / 100);
    var totalConDescuento = total - montoDescuento;

    // SUMAR LA COMISIÓN AL TOTAL DE LA VENTA
    var comisionTotal = parseFloat($('#comision_total').val()) || 0;
    var totalACobrarFinal = totalConDescuento + comisionTotal;

    // SUMAR LO QUE HAN PAGADO EN TOTAL (Base + Comisión)
    var sumaPagado = 0;
    $('.fila-medio-pago').each(function() {
        var $input = $(this).find('.input-monto-medio');
        var monto = parseFloat($input.val()) || 0;
        var comisionPorcentaje = parseFloat($input.data('comision')) || 0;
        sumaPagado += monto + (monto * (comisionPorcentaje / 100));
    });

    var diferencia = Math.abs(totalACobrarFinal - sumaPagado);

    if (diferencia > 0.01) {
        var mensaje = 'Hay una diferencia entre el total y los medios de pago.\n\n';
        mensaje += 'Total a cobrar (incl. comisiones): S/ ' + totalACobrarFinal.toFixed(2) + '\n';
        mensaje += 'Suma ingresada: S/ ' + sumaPagado.toFixed(2) + '\n';
        mensaje += 'Diferencia: S/ ' + diferencia.toFixed(2);
        
        Swal.fire({
            icon: 'error',
            title: 'Error en medios de pago',
            text: mensaje,
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    return true;
}*/

  //ORIGINAL
  /*function validarMediosPago() {
    // Obtener el tipo de forma de pago seleccionada
    var metodo = $('#estadopago').find(':selected').attr('data-medio');
    
    // Debug: mostrar el valor del método en la consola
    console.log('Método de pago detectado:', metodo);
    
    // Verificar múltiples variaciones posibles de CREDITO
    var metodosCredito = ['CREDITO', 'credito', 'Credito', 'CRÉDITO', 'crédito'];
    
    if (metodosCredito.includes(metodo)) {
        console.log('Es crédito, saltando validación');
        return true;
    }
    
    // También verificar si los medios de pago están ocultos (indicativo de crédito)
    if ($('#divmediopago').is(':hidden')) {
        console.log('Medios de pago ocultos, asumiendo crédito');
        return true;
    }
    
    var total = parseFloat($('#total').val()) || 0;
    var sumaMediosPago = calcularTotalMediosPago();
    var descuentoGlobal = parseFloat($('#descuento_global').val()) || 0;
    
    // Calcular el total con descuento
    var montoDescuento = total * (descuentoGlobal / 100);
    var totalConDescuento = total - montoDescuento;
    
    var diferencia = Math.abs(totalConDescuento - sumaMediosPago);
    
    // Permitir una diferencia mínima de 0.01 por redondeos
    if (diferencia > 0.01) {
        var mensaje = 'Hay una diferencia entre el total y los medios de pago.\n\n';
        mensaje += 'Total a cobrar: S/ ' + totalConDescuento.toFixed(2) + '\n';
        mensaje += 'Suma medios de pago: S/ ' + sumaMediosPago.toFixed(2) + '\n';
        mensaje += 'Diferencia: S/ ' + diferencia.toFixed(2);
        
        Swal.fire({
            icon: 'error',
            title: 'Error en medios de pago',
            text: mensaje,
            confirmButtonText: 'Entendido'
        });
        return false;
    }
    
    return true;
}*/

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

    $('.ubigeo').select2({
      placeholder: 'Select an option'
    });

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

   // $("#producto").select2('open');

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

        $("#guia_remision").val(respuesta.guia);
        $("#IdCpe_guia").val(respuesta.id);

        alert('GUIA REGISTRADA');

        $("#modal-guia").modal("hide");

   // window.location.href = "/guiasremision";

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




   //$(".mediopago").val('0');

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
  var nuevafecha = $('#fecEmi').val();

  if(metodo=='CREDITO'){

    $("#divmediopago").hide('true');
    //$(".mediopago").val('0');
    $("#divfecVen").hide('true');
    $("#fecVen").val(nuevafecha);
    $("#divcuotas").show('true');

  }

  if(metodo =='CONTADO'){
    $("#divmediopago").show('true');
    $("#divfecVen").hide('true');
    $("#fecVen").val($("#fecEmi").val());
    //$('#predeterminado_1').val($('#total').val());
    $("#divcuotas").hide('true');
  }

  if(metodo =='PERSONALIZADO'){
    $("#divmediopago").hide('true');
    //$(".mediopago").val('0');
    $("#divfecVen").show('true');
    $("#divcuotas").show('true');
  }


  $("#estadopago").on("change", function() {
    var metodo = $(this).find(':selected').attr('data-medio');
    var dias = $(this).find(':selected').attr('data-dias');
    var nuevafecha = $('#fecEmi').val();

    if(metodo=='CREDITO'){
      $("#divmediopago").hide('true');
      //$(".mediopago").val('0');
      limpiarMediosPago();
      $("#divfecVen").hide('true');
      $("#fecVen").val(nuevafecha);
      $("#divcuotas").show('true');
    }

    if(metodo =='CONTADO'){
      $("#divmediopago").show('true');
      $("#divfecVen").hide('true');
      $("#divcuotas").hide('true');
      $("#fecVen").val($("#fecEmi").val());
      //$('#predeterminado_1').val($('#total').val());
    }

    if(metodo =='PERSONALIZADO'){
      $("#divmediopago").hide('true');
      //$(".mediopago").val('0');
      limpiarMediosPago();
      $("#divfecVen").show('true');
      $("#divcuotas").show('true');
    }
  });



  $("#num_ped").focus();

  $('#clinum').val('00000000');
  $('#clinom').val('VENTA AL PORTADOR');





  $("#btnRegComp").on("click", function() {
    
    // Validar medios de pago antes de proceder
    if (!validarMediosPago()) {
        return false;
    }

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
    // Validar medios de pago antes de proceder
    if (!validarMediosPago()) {
        return false;
    }

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
          // --- NUEVO: Descargar PDF automáticamente ---
          if (respuesta.pdf_url) {
            window.open(respuesta.pdf_url, '_blank');
          }
          window.location.href = "/pos/";
        }
      }
    });
});




  $("#buscarproducto").keypress(function(e) {
        var code = (e.keyCode ? e.keyCode : e.which);
        if (code == 13) { // Si se presiona Enter
            var valorBusqueda = $(this).val(); // El código de barras ingresado

            $.ajax({
                type: 'get',
                url: '/consultarproductosbarra',
                dataType: 'json',
                data: {
                    'value': valorBusqueda
                },
                success: function(data) {
                    if (data.length > 0) {
                        var productoEncontrado = data[0];
                        var proidEncontrado = productoEncontrado.id;
                        var codigoEncontrado = productoEncontrado.codigo;

                        let productoYaExiste = false;
                        $('#grdet tbody tr').each(function() {
                            var currentProId = $(this).find('input[name="proid[]"]').val();
                            var currentCodigo = $(this).find('td:eq(0) input').val();

                            if (currentProId == proidEncontrado || currentCodigo == codigoEncontrado) {
                                productoYaExiste = true;
                                var cantidadInput = $(this).find('input[name="cant[]"]');
                                var cantidadActual = parseFloat(cantidadInput.val());
                                cantidadInput.val(cantidadActual + 1);

                                Calcular(cantidadInput[0]);
                                return false;
                            }
                        });

                        if (!productoYaExiste) {
                            $('#grdet').append("<tr>" +
                                "<td width='200px' hidden='hidden'><input type='text' readonly='readonly' class='form-control input-sm'  value='" + productoEncontrado.codigo + "'></td>" +
                                "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='" + productoEncontrado.producto + "'></td>" +
                                "<td> <input type='number' step='any' min='0' value='1' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>" +
                                "<td width='80px' hidden='hidden'><input readonly='readonly'  value='" + productoEncontrado.unidad + "'  name='unid[]'  class='form-control input-sm'></td>" +
                                "<td><input  type='number' step='any' min='0' class='form-control input-sm' readonly='readonly' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='" + productoEncontrado.propun + "'  style='width:80px' ></td>" +
                                "<td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>" +
                                "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='" + productoEncontrado.propun + "' onkeyup='CalcularItem(this);' style='width:80px' ></td>" +
                                "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='" + productoEncontrado.propun + "' style='width:80px' ></td>" +
                                "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='" + productoEncontrado.id + "' readonly='readonly' ></td>" +
                                "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='" + productoEncontrado.id_almacen + "' readonly='readonly' ></td>" +
                                "<td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='" + productoEncontrado.icbper + "' readonly='readonly' ></td>" +
                                "<td hidden='hidden' ><input type='text' class='form-control' name='mon_icbper[]'  value='" + productoEncontrado.mon_icbper + "' readonly='readonly' ></td>" +
                                "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");
                            calculartotal();
                        }
                    } else {
                        Swal.fire('Producto no encontrado', 'El código de barras no coincide con ningún producto.', 'warning');
                    }

                    $("#buscarproducto").val('');
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error("Error al buscar producto por barra: " + textStatus, errorThrown);
                    Swal.fire('Error', 'Hubo un problema al buscar el producto. Inténtalo de nuevo.', 'error');
                    $("#buscarproducto").val('');
                }
            });
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
      //  $("#modal-cantidad-precio").modal("hide");
    }

  });

   $("#btnAgregarLista").click(function(e){


    agregaritem();
       // $("#modal-cantidad-precio").modal("hide");

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


      preuni =  $(this).find("td:eq(7) > input").val();

      //$(this).find("td:eq(3) > input").val(preuni);

    }else{

     preuni =  $(this).find("td:eq(4) > input").val();
   }

    //total item
   totitemgrav = parseFloat(($(this).find("td:eq(2) > input").val() * preuni)-descuento);

   $(this).find("td:eq(6) > input").val(totitemgrav.toFixed(2));

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
  $('#clicor2').val($('#clicod').find(':selected').attr('data-correo2'));
  $('#clicor3').val($('#clicod').find(':selected').attr('data-correo3'));
  $('#clicor4').val($('#clicod').find(':selected').attr('data-correo4'));
  $("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));
  $('#ubi_cod_env option[value="'+$('#clicod').find(':selected').attr('data-ubicod')+'"]').prop('selected', true);

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

  // ✅ AQUÍ LO CONECTAS: Ejecuta la revisión de fidelización en vivo
  verificar_puntos($('#clicod').val());
}

function calculartotal(){
  var totigv = 0, totgrav = 0, subtotal = 0, tot_icbper = 0;

  $("#grdet tbody tr").each(function(){
    totgrav = totgrav + parseFloat($(this).find("td:eq(6)  > input").val()) + parseFloat($(this).find("td:eq(2)  > input").val()*$(this).find("td:eq(11)  > input").val());
    tot_icbper = parseFloat($(this).find("td:eq(2)  > input").val()*$(this).find("td:eq(11)  > input").val());
  });

  if ($('#grdet >tbody >tr').length == 0){
    totgrav = 0;
    tot_icbper = 0;
    $('#igv').val('0.00');
    $('#subtotal').val('0.00');
    $('#vuelto').val('0.00');
  }

  $('#total').attr('data-base', totgrav.toFixed(2));
  $('#tot_icbper').val(tot_icbper.toFixed(2));

  actualizarEfectivoPredeterminado();
  actualizarSumaTotal();
  
  // ✅ AQUÍ LO CONECTAS: Si cambian los productos o cantidades, se actualizan los puntos proyectados
  if(typeof actualizar_caja_puntos === "function") { 
      actualizar_caja_puntos(); 
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
  '<td><input type="number" step="any" min="0" value="1" name="cant[]" onkeyup="Calcular(this);" onchange="Calcular(this);" class="form-control input-sm cant" id="font-size" style="width:60px"> </td><td ><input readonly="readonly"  value="'+unidad+'" style="width:100px" name="unid[]"  class="form-control input-sm"></td><td><input  type="number" step="any" min="0" class="form-control input-sm" name="propun[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="'+precio+'" style="width:80px" ></td><td ><input  type="number" step="any" min="0" class="form-control input-sm" name="desc[]" onChange="Calcular(this);"  onkeyup="Calcular(this);" value="0.00" style="width:80px" ></td><td><input  type="text" class="form-control" name="itemtotal[]"  value="'+total+'" onkeyup="CalcularItem(this);" style="width:80px"></td><td hidden="hidden"><input  type="number" readonly="readonly" step="any" min="0" class="form-control input-sm" name="precio[]"  value="'+precio+'" style="width:80px" ></td><td hidden="hidden"><input type="text" class="form-control" name="proid[]"  value="'+proid+'" readonly="readonly" ></td><td><button type="button" onClick="deleteRow(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');

 
 calculartotal();
 
 $("#modal-presentaciones").modal("hide");




}

function agregarnota(button){


  $('#grdet').append("<tr>"+
   "<td hidden='hidden'width=200px'><input type='text'  readonly='readonly'  class='form-control input-sm'  value=''></td>"+
   "<td width='1300px'><input type='text' class='form-control input-sm btn-block' name='pronom[]' value='' ></td>"+
   "<td> <input type='number' step='any' min='0' value='1'  name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='cant form-control input-sm ' id='font-size' style='width:60px'> </td>"+
   "<td hidden='hidden'><select  style='width:100px' name='unid[]'  class='form-control input-sm'>@foreach($unidades as $uni) @if($uni->umecod =='NIU') <option selected='selected' value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @else <option  value='{{$uni->umecod}}'>{{$uni->umenom}}</option> @endif @endforeach</select></td>"+
   "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
   "<td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
   "<td><input  type='text' class='form-control input-sm' name='itemtotal[]'  value='0.00' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
   '<td hidden="hidden"><input  type="number" readonly="readonly" step="any" min="0" class="form-control input-sm" name="precio[]"  value="0" style="width:80px" ></td>'+
   "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='0' readonly='readonly' ></td>"+  
   "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='' readonly='readonly' ></td>"+
   "<td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='0' readonly='readonly' ></td>"+
   "<td hidden='hidden' ><input type='text' class='form-control' name='mon_icbper[]'  value='0' readonly='readonly' ></td>"+
   "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

  calculartotal();

  $("#modal-presentaciones").modal("hide");

  
}

function agregaritem(){


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
     "<td hidden='hidden' width='200px'><input type='text' readonly='readonly' class='form-control input-sm'  value='"+codigo+"'></td>"+
     "<td width='900px'><input type='text' class='form-control input-sm ' name='pronom[]' value='"+producto+"'></td>"+
     "<td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
     "<td width='80px' hidden='hidden'><input readonly='readonly'  value='"+unidad+"'  name='unid[]'  class='form-control input-sm'></td>"+
     "<td><input  type='number' step='any' min='0' class='form-control input-sm'  name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"'  style='width:80px' ></td>"+
     "<td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
     "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly'  value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
     "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
     "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
     "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
     "<td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='"+icbper+"' readonly='readonly' ></td>"+
     "<td hidden='hidden' ><input type='text' class='form-control' name='mon_icbper[]'  value='"+mon_icbper+"' readonly='readonly' ></td>"+
     "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

    actualizarpro();

    calculartotal();
    $("#can_producto").val('1');
    $("#modal-cantidad-precio").modal("hide");


  }else{

   /* if( parseFloat(precio_ref)<= parseFloat(precio)){*/
    $('#grdet').append("<tr>"+
     "<td width=200px' hidden='hidden'><input type='text'  readonly='readonly'  class='form-control input-sm'  value='"+codigo+"'></td>"+
     "<td width='900px'><input type='text' class='form-control input-sm' name='pronom[]' value='"+producto+"'></td>"+
     "<td> <input type='number' step='any' min='0' value='"+cantidad+"' name='cant[]' onkeyup='Calcular(this);' onchange='Calcular(this);' class='form-control input-sm ' id='font-size' style='width:60px'></td>"+
     "<td width='80px'  hidden='hidden'><input readonly='readonly'  value='"+unidad+"' name='unid[]'  class='form-control input-sm'></td>"+
     "<td><input  type='number' step='any' min='0' class='form-control input-sm' name='propun[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='"+precio+"' readonly='readonly' style='width:80px' ></td>"+
     "<td hidden='hidden'><input  type='number' step='any' min='0' class='form-control input-sm' name='desc[]' onChange='Calcular(this);'  onkeyup='Calcular(this);' value='0.00' style='width:80px' ></td>"+
     "<td><input  type='text' class='form-control input-sm' name='itemtotal[]' readonly='readonly' value='"+total+"' onkeyup='CalcularItem(this);' style='width:80px' ></td>"+
     "<td hidden='hidden'><input  type='number' readonly='readonly' step='any' min='0' class='form-control input-sm' name='precio[]'  value='"+precio+"' style='width:80px' ></td>"+
     "<td hidden='hidden'><input type='text' class='form-control' name='proid[]'  value='"+proid+"' readonly='readonly' ></td>"+
     "<td hidden='hidden' ><input type='text' class='form-control' name='id_almacen_pro[]'  value='"+id_almacen_pro+"' readonly='readonly' ></td>"+
     "<td hidden='hidden' ><input type='text' class='form-control' name='icbper[]'  value='"+icbper+"' readonly='readonly' ></td>"+
     "<td hidden='hidden' ><input type='text' class='form-control' name='mon_icbper[]'  value='"+mon_icbper+"' readonly='readonly' ></td>"+
     "<td><button type='button' onClick='deleteRow(this);' class='btn btn-danger btn-sm remove'><span class='glyphicon glyphicon-minus'></span></button></td></tr>");

    actualizarpro();

    calculartotal();
    $("#modal-cantidad-precio").modal("hide");
    $("#can_producto").val('1');
 /* }else{

       alert('No Tiene Autorizacion para Descuentos');
    }*/


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

function buscarcomprobante(){

  var pedido = $("#bus_comprobante").val();

  window.location.href = "/buscar_comprobante/"+pedido;

}

// ========================================
// FUNCIONES PARA MEDIOS DE PAGO DINÁMICOS
// ========================================

// Datos de medios de pago (se cargan desde el backend)
var mediosPagoData = [];
var medioEfectivoId = null;

$(document).ready(function() {
    // Cargar datos de medios de pago desde el backend
    @foreach($mediospagos as $mp)
        mediosPagoData.push({
            id: '{{$mp->id_med_pag}}',
            nombre: '{{$mp->nom_med_pag}}',
            comision: {{$mp->comision ?? 0}},
            predeterminado: '{{$mp->predeterminado}}'
        });
        
        // Identificar el ID del medio de pago EFECTIVO
        @if(strtoupper($mp->nom_med_pag) == 'EFECTIVO' || strtoupper($mp->nom_med_pag) == 'EFECTIVO 1')
            medioEfectivoId = '{{$mp->id_med_pag}}';
        @endif
    @endforeach

    // Event listener para el botón de agregar medio de pago
    $('#btnAgregarMedioPago').on('click', function() {
        agregarMedioPago();
    });

    // Event listener para cuando cambia el total
    $('#total').on('change keyup', function() {
        actualizarEfectivoPredeterminado();
    });

    // Agregar efectivo inicial cuando se carga la página
    setTimeout(function() {
        agregarEfectivoInicial();
    }, 500);

    // Permitir agregar con Enter en el campo de monto
    $('#montoMedioPago').on('keypress', function(e) {
        if (e.which == 13) {
            e.preventDefault();
            $('#btnAgregarMedioPago').click();
        }
    });
});

function agregarEfectivoInicial() {
    if (!medioEfectivoId) return;

    // LEER DESDE EL ATRIBUTO BASE
    var totalVenta = parseFloat($('#total').attr('data-base')) || 0;
    
    if (totalVenta <= 0) return;

    var filaEfectivo = $('.fila-medio-pago[data-medio-id="' + medioEfectivoId + '"]');
    
    if (filaEfectivo.length > 0) {
        var inputMonto = filaEfectivo.find('.input-monto-medio');
        inputMonto.val(totalVenta.toFixed(2));
        actualizarFilaMedioPago(inputMonto[0]);
    } else {
        var medioPago = mediosPagoData.find(m => m.id === medioEfectivoId);
        if (!medioPago) return;

        var comision = totalVenta * (medioPago.comision / 100);
        var totalConComision = totalVenta + comision;

        var nuevaFila = `
            <tr class="fila-medio-pago" data-medio-id="${medioPago.id}" data-es-efectivo="true">
                <td><strong>${medioPago.nombre}</strong> <small class="text-muted"></small></td>
                <td>
                    <input type="number" step="any" min="0" 
                           class="form-control input-monto-medio" 
                           value="${totalVenta.toFixed(2)}" 
                           onchange="actualizarFilaMedioPago(this)"
                           data-comision="${medioPago.comision}">
                    <input type="hidden" name="medio[]" value="${medioPago.id}">
                    <input type="hidden" name="monto[]" class="input-monto-hidden" value="${totalVenta.toFixed(2)}">
                </td>
                <td class="text-center">
                    <span class="badge badge-info">${medioPago.comision}%</span>
                </td>
                <td class="text-right comision-monto">
                    S/ ${comision.toFixed(2)}
                </td>
                <td class="text-right total-con-comision">
                    S/ ${totalConComision.toFixed(2)}
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-medio" onclick="eliminarMedioPago(this)">
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#tbody-medios-pago').append(nuevaFila);
    }
    actualizarSumaTotal();
}

function actualizarEfectivoPredeterminado() {
    if (!medioEfectivoId) return;

    // LEER DESDE EL ATRIBUTO BASE
    var totalVenta = parseFloat($('#total').attr('data-base')) || 0;
    
    var filaEfectivo = $('.fila-medio-pago[data-medio-id="' + medioEfectivoId + '"]');
    
    if (filaEfectivo.length > 0 && filaEfectivo.attr('data-es-efectivo') === 'true') {
        if ($('.fila-medio-pago').length === 1) {
            var inputMonto = filaEfectivo.find('.input-monto-medio');
            inputMonto.val(totalVenta.toFixed(2));
            actualizarFilaMedioPago(inputMonto[0]);
        }
    } else if (totalVenta > 0 && $('.fila-medio-pago').length === 0) {
        agregarEfectivoInicial();
    }
    
    actualizarSumaTotal();
}

// Función para agregar efectivo inicial o actualizar su monto
/*function agregarEfectivoInicial() {
    if (!medioEfectivoId) {
        console.log('No se encontró el medio de pago EFECTIVO');
        return;
    }
    var totalVenta = parseFloat($('#total').val()) || 0;    
    if (totalVenta <= 0) {
        return;
    }
    var filaEfectivo = $('.fila-medio-pago[data-medio-id="' + medioEfectivoId + '"]');    
    if (filaEfectivo.length > 0) {
        var inputMonto = filaEfectivo.find('.input-monto-medio');
        inputMonto.val(totalVenta.toFixed(2));
        actualizarFilaMedioPago(inputMonto[0]);
    } else {
        var medioPago = mediosPagoData.find(m => m.id === medioEfectivoId);        
        if (!medioPago) {
            return;
        }
        var comision = totalVenta * (medioPago.comision / 100);
        var totalConComision = totalVenta + comision;
        var nuevaFila = `
            <tr class="fila-medio-pago" data-medio-id="${medioPago.id}" data-es-efectivo="true">
                <td><strong>${medioPago.nombre}</strong> <small class="text-muted"></small></td>
                <td>
                    <input type="number" step="any" min="0" 
                           class="form-control input-monto-medio" 
                           value="${totalVenta.toFixed(2)}" 
                           onchange="actualizarFilaMedioPago(this)"
                           data-comision="${medioPago.comision}">
                    <input type="hidden" name="medio[]" value="${medioPago.id}">
                    <input type="hidden" name="monto[]" class="input-monto-hidden" value="${totalVenta.toFixed(2)}">
                </td>
                <td class="text-center" hidden="hidden">
                    <span class="badge badge-info">${medioPago.comision}%</span>
                </td>
                <td class="text-right comision-monto" hidden="hidden">
                    S/ ${comision.toFixed(2)}
                </td>
                <td class="text-right total-con-comision" hidden="hidden">
                    S/ ${totalConComision.toFixed(2)}
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-medio" onclick="eliminarMedioPago(this)">
                        <i class="glyphicon glyphicon-trash"></i>
                    </button>
                </td>
            </tr>
        `;

        $('#tbody-medios-pago').append(nuevaFila);
    }

    actualizarSumaTotal();
}


function actualizarEfectivoPredeterminado() {
    if (!medioEfectivoId) {
        return;
    }
    var totalVenta = parseFloat($('#total').val()) || 0;
    var filaEfectivo = $('.fila-medio-pago[data-medio-id="' + medioEfectivoId + '"]');    
    if (filaEfectivo.length > 0 && filaEfectivo.attr('data-es-efectivo') === 'true') {
        if ($('.fila-medio-pago').length === 1) {
            var inputMonto = filaEfectivo.find('.input-monto-medio');
            inputMonto.val(totalVenta.toFixed(2));
            actualizarFilaMedioPago(inputMonto[0]);
        }
    } else if (totalVenta > 0 && $('.fila-medio-pago').length === 0) {
        agregarEfectivoInicial();
    }
    
    actualizarSumaTotal();
}*/

function agregarMedioPago() {
    var medioSeleccionado = $('#selectMedioPago').val();
    var montoIngresado = parseFloat($('#montoMedioPago').val()) || 0;

    if (!medioSeleccionado) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Debe seleccionar un medio de pago',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    if (montoIngresado <= 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'El monto debe ser mayor a 0',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Verificar si el medio de pago ya existe
    var medioYaExiste = $('.fila-medio-pago[data-medio-id="' + medioSeleccionado + '"]');
    if (medioYaExiste.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Advertencia',
            text: 'Este medio de pago ya está agregado. Puede editar su monto directamente en la tabla.',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Buscar datos del medio de pago
    var medioPago = mediosPagoData.find(m => m.id === medioSeleccionado);
    
    if (!medioPago) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se encontró el medio de pago seleccionado',
            confirmButtonText: 'Entendido'
        });
        return;
    }

    // Calcular comisión
    var comision = montoIngresado * (medioPago.comision / 100);
    var totalConComision = montoIngresado + comision;

    // Agregar fila a la tabla
    var nuevaFila = `
        <tr class="fila-medio-pago" data-medio-id="${medioPago.id}">
            <td>${medioPago.nombre}</td>
            <td>
                <input type="number" step="any" min="0" 
                       class="form-control input-monto-medio" 
                       value="${montoIngresado.toFixed(2)}" 
                       onchange="actualizarFilaMedioPago(this)"
                       data-comision="${medioPago.comision}">
                <input type="hidden" name="medio[]" value="${medioPago.id}">
                <input type="hidden" name="monto[]" class="input-monto-hidden" value="${montoIngresado.toFixed(2)}">
            </td>
            <td class="text-center">
                <span class="badge badge-info">${medioPago.comision}%</span>
            </td>
            <td class="text-right comision-monto">
                S/ ${comision.toFixed(2)}
            </td>
            <td class="text-right total-con-comision">
                S/ ${totalConComision.toFixed(2)}
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-eliminar-medio" onclick="eliminarMedioPago(this)">
                    <i class="glyphicon glyphicon-trash"></i>
                </button>
            </td>
        </tr>
    `;

    $('#tbody-medios-pago').append(nuevaFila);

    // Limpiar campos
    $('#selectMedioPago').val('').trigger('change');
    $('#montoMedioPago').val('');

    // Actualizar totales
    actualizarSumaTotal();
}

function actualizarFilaMedioPago(elemento) {
    var $input = $(elemento);
    var monto = parseFloat($input.val()) || 0;
    var comision = parseFloat($input.data('comision')) || 0;

    var montoComision = monto * (comision / 100);
    var totalConComision = monto + montoComision;

    var $fila = $input.closest('tr');
    $fila.find('.comision-monto').text('S/ ' + montoComision.toFixed(2));
    $fila.find('.total-con-comision').text('S/ ' + totalConComision.toFixed(2));
    $fila.find('.input-monto-hidden').val(monto.toFixed(2));

    actualizarSumaTotal();
}

function eliminarMedioPago(boton) {
    Swal.fire({
        title: '¿Está seguro?',
        text: "Se eliminará este medio de pago",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $(boton).closest('tr').remove();
            actualizarSumaTotal();
        }
    });
}

function actualizarSumaTotal() {
    var sumaTotalBase = 0;
    var sumaComisionSoles = 0;
    var sumaTotalPagado = 0;

    $('.fila-medio-pago').each(function() {
        var $input = $(this).find('.input-monto-medio');
        var monto = parseFloat($input.val()) || 0;
        var comisionPorcentaje = parseFloat($input.data('comision')) || 0;

        var comisionFila = monto * (comisionPorcentaje / 100);
        var totalFila = monto + comisionFila;

        sumaTotalBase += monto;
        sumaComisionSoles += comisionFila;
        sumaTotalPagado += totalFila;
    });

    // Asignar la comisión en soles al campo que va a BD
    $('#comision_total').val(sumaComisionSoles.toFixed(2));

    // Mostrar el total pagado abajo en la tabla
    $('#totalMediosPago').text('S/ ' + sumaTotalPagado.toFixed(2));

    // RECUPERAR EL TOTAL BASE (Sin comisiones)
    var totalVentaBase = parseFloat($('#total').attr('data-base')) || 0;
    var descuentoGlobal = parseFloat($('#descuento_global').val()) || 0;
    var montoDescuento = totalVentaBase * (descuentoGlobal / 100);
    var totalConDescuento = totalVentaBase - montoDescuento;

    // Actualizar descuento (por si acaso)
    $('#monto_descuento').val(montoDescuento.toFixed(2));

    // CALCULAR EL TOTAL FINAL (Base - Descuento + Comisión)
    var totalDeberiaSer = totalConDescuento + sumaComisionSoles;

    // LA MAGIA: Sobrescribimos el valor visible del Total para que diga 208 en vez de 200
    $('#total').val(totalDeberiaSer.toFixed(2));

    // Actualizar Vuelto
    var pago = parseFloat($('#pagar').val()) || 0;
    if(pago > 0) {
        var vuelto = pago - totalDeberiaSer;
        $('#vuelto').val(vuelto.toFixed(2));
    }

    // Validar Diferencias
    var diferencia = totalDeberiaSer - sumaTotalPagado;

    if (Math.abs(diferencia) > 0.01) {
        $('#alertaDiferencia').removeClass('hide').show();
        if (diferencia > 0) {
            $('#textoFalta').show();
            $('#textoExceso').hide();
            $('#diferenciaMonto').text('S/ ' + diferencia.toFixed(2));
        } else {
            $('#textoFalta').hide();
            $('#textoExceso').show();
            $('#diferenciaMonto').text('S/ ' + Math.abs(diferencia).toFixed(2));
        }
    } else {
        $('#alertaDiferencia').hide();
    }
}


//NUEVO
/*function actualizarSumaTotal() { 
    var sumaTotalBase = 0;
    var sumaComisionSoles = 0;
    var sumaTotalPagado = 0;

    // Recorrer cada medio de pago agregado en la tabla
    $('.fila-medio-pago').each(function() {
        var $input = $(this).find('.input-monto-medio');
        var monto = parseFloat($input.val()) || 0;
        var comisionPorcentaje = parseFloat($input.data('comision')) || 0;

        // Calcular la comisión en soles por fila
        var comisionFila = monto * (comisionPorcentaje / 100);
        var totalFila = monto + comisionFila;

        sumaTotalBase += monto;
        sumaComisionSoles += comisionFila;
        sumaTotalPagado += totalFila;
    });

    // 1. ASIGNAR LA COMISIÓN EN SOLES AL CAMPO QUE VA A LA BASE DE DATOS
    $('#comision_total').val(sumaComisionSoles.toFixed(2));

    // 2. MOSTRAR EL TOTAL CON COMISIONES EN LA TABLA
    $('#totalMediosPago').text('S/ ' + sumaTotalPagado.toFixed(2));

    // Calcular totales base de la venta
    var totalVenta = parseFloat($('#total').val()) || 0;
    var descuentoGlobal = parseFloat($('#descuento_global').val()) || 0;
    var montoDescuento = totalVenta * (descuentoGlobal / 100);
    var totalConDescuento = totalVenta - montoDescuento;

    // 3. ACTUALIZAR TOTAL FINAL (Lo que el cliente realmente debe cubrir)
    var totalDeberiaSer = totalConDescuento + sumaComisionSoles;

    // 4. ACTUALIZAR EL VUELTO
    var pago = parseFloat($('#pagar').val()) || 0;
    if(pago > 0) {
        var vuelto = pago - totalDeberiaSer;
        $('#vuelto').val(vuelto.toFixed(2));
    }

    // Validar si hay diferencias
    var diferencia = totalDeberiaSer - sumaTotalPagado;

    if (Math.abs(diferencia) > 0.01) {
        $('#alertaDiferencia').removeClass('hide').show();
        if (diferencia > 0) {
            $('#textoFalta').show();
            $('#textoExceso').hide();
            $('#diferenciaMonto').text('S/ ' + diferencia.toFixed(2));
        } else {
            $('#textoFalta').hide();
            $('#textoExceso').show();
            $('#diferenciaMonto').text('S/ ' + Math.abs(diferencia).toFixed(2));
        }
    } else {
        $('#alertaDiferencia').hide();
    }
}*/

//ORIGINAL
/*function actualizarSumaTotal() { 
    var sumaTotal = 0;

    $('.input-monto-medio').each(function() {
        var monto = parseFloat($(this).val()) || 0;
        sumaTotal += monto;
    });

    $('#totalMediosPago').text('S/ ' + sumaTotal.toFixed(2));

    // Validar con el total de la venta
    var totalVenta = parseFloat($('#total').val()) || 0;
    var descuentoGlobal = parseFloat($('#descuento_global').val()) || 0;
    var montoDescuento = totalVenta * (descuentoGlobal / 100);
    var totalConDescuento = totalVenta - montoDescuento;

    var diferencia = totalConDescuento - sumaTotal;

    if (Math.abs(diferencia) > 0.01) {
        $('#alertaDiferencia').removeClass('hide').show();
        $('#diferenciaMonto').text('S/ ' + diferencia.toFixed(2));
        
        if (diferencia > 0) {
            $('#textoFalta').show();
            $('#textoExceso').hide();
        } else {
            $('#textoFalta').hide();
            $('#textoExceso').show();
            $('#diferenciaMonto').text('S/ ' + Math.abs(diferencia).toFixed(2));
        }
    } else {
        $('#alertaDiferencia').hide();
    }
}*/

function calcularTotalMediosPago() {
    var total = 0;
    $('.input-monto-medio').each(function() {
        total += parseFloat($(this).val()) || 0;
    });
    return total;
}

function limpiarMediosPago() {
    $('#tbody-medios-pago').empty();
    actualizarSumaTotal();
    
    // Volver a agregar efectivo predeterminado
    setTimeout(function() {
        agregarEfectivoInicial();
    }, 100);
}

// Al cargar la página, inicializar select2 para medios de pago
$(document).ready(function() {
    $('#selectMedioPago').select2({
        placeholder: 'Seleccione un medio de pago',
        allowClear: true,
        width: '100%'

        
    });
});

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



<div  class="divcargar" id="divcargar">

</div>

@if(isset($codfact) && $datos->ticket_pantalla=='1' && $datos->formato=='TICKET')
<a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
@endif
@if(isset($codfact))
<input type="hidden" name="comprobante" id="comprobante" value="{{$codfact}}">
@endif

@if(isset($tdocod))
<input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
@endif

<div id="divpedido">
 <div class="col-lg-7">
  <div class="row">

    <div class="col-lg-12">
      <div class="box">
       <div style="display:none;" class="box-header" style="background-color:blue;">
         <font color="white"><strong>{{$datos->tipo_negocio}}</strong></font>

         <div class="box-tools pull-right" hidden='hidden'>
          <a  data-target="#modal-guia" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">GUIA</button></a>
          <a  data-target="#modal-gasto" data-toggle="modal"><button type="button" class="btn btn-warning btn-sm">GASTO / INGRESO</button></a>

          <a hidden="hidden" data-target="#modal-vehiculo" data-toggle="modal"><button type="button" class="btn btn-primary btn-sm">DATOS VEHICULO</button></a>

        </div>

      </div>
      <div class="box-header with-border form-group-sm">

        <div  class="col-lg-4" hidden='hidden'>
        <input class="form-control" name="buscarproducto" id="buscarproducto" placeholder="Código Barras">
      </div>


        <div  class="col-lg-12">
          <div class="form-group form-group-sm" id="divactpro">
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
     <div class="box-body table-responsive"  >

       <table class="table table-hover" id="grdet">
        <thead>
          <th hidden='hidden'>COD.</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th hidden='hidden'>U.M.</th>
          <th>PU</th>
          <th hidden='hidden'>Desc.</th>
          <th>Total</th>
          <th hidden="hidden">P.U</th>
          <th><button type="button" onClick="agregarnota();" name="add" id="add" class="btn btn-success btn-sm add"><span class="glyphicon glyphicon-plus"></span></button></th>
        </thead>

        <tbody>

        </tbody>
      </table>

      <br>
      <table class="table table-hover">
        <thead>

          <th>OBSERVACIONES</th>

        </thead>

        <tbody>

          <tr>
            <td>
              <textarea class="form-control" rows="1" maxlength="250" name="observaciones"></textarea>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div class="row" id="divmediopago">
              <div class="col-lg-12">
                  <div class="panel panel-primary">
                      <div class="panel-heading" hidden="hidden">
                          <h3 class="panel-title"><strong>MEDIOS DE PAGO</strong></h3>
                      </div>
                      <div class="panel-body">
                          <!-- Controles para agregar medios de pago -->
                          <div class="row">
                              <div class="col-lg-4">
                                  <div class="form-group">
                                      <label>Medio de Pago</label>
                                      <select class="form-control" id="selectMedioPago">
                                          <option value="">Seleccione...</option>
                                          @foreach($mediospagos as $mp)
                                          <option hidden="hidden" value="{{$mp->id_med_pag}}" 
                                                  data-comision="{{$mp->comision ?? 0}}" 
                                                  data-nombre="{{$mp->nom_med_pag}}">
                                              {{$mp->nom_med_pag}} 
                                              @if(!empty($mp->comision) && $mp->comision > 0)
                                                  (Comisión: {{$mp->comision}}%)
                                              @endif
                                          </option>
                                          @endforeach
                                      </select>
                                  </div>
                              </div>
                              <div class="col-lg-4">
                                  <div class="form-group">
                                      <label>Monto</label>
                                      <input type="number" step="0.01" min="0" class="form-control" 
                                             id="montoMedioPago" placeholder="0.00">
                                  </div>
                              </div>
                              <div class="col-lg-3">
                                  <div class="form-group">
                                      <label>&nbsp;</label>
                                      <button type="button" class="btn btn-success btn-block" 
                                              id="btnAgregarMedioPago">
                                          <i class="glyphicon glyphicon-plus"></i> Agregar
                                      </button>
                                  </div>
                              </div>
                          </div>

                          <!-- Tabla de medios de pago agregados -->
                          <div class="table-responsive" id="tabla-medios-pago">
                              <table class="table table-bordered table-hover">
                                  <thead>
                                      <tr>
                                          <th width="25%">Medio de Pago</th>
                                          <th width="20%">Monto</th>
                                          <th width="10%" class="text-center">% Comisión</th>
                                          <th width="15%" class="text-right">Comisión (S/)</th>
                                          <th width="20%" class="text-right">Total con Comisión</th>
                                          <th width="10%" class="text-center">Acciones</th>
                                      </tr>
                                  </thead>
                                  <tbody id="tbody-medios-pago">
                                      <!-- Las filas se agregarán dinámicamente -->
                                  </tbody>
                                  <tfoot>
                                      <tr class="total-medios-pago">
                                          <td colspan="1" class="text-right"><strong>TOTAL:</strong></td>
                                          <td colspan="5" id="totalMediosPago" style="font-size: 18pt;">
                                              S/ 0.00
                                          </td>
                                      </tr>
                                  </tfoot>
                              </table>
                          </div>

                          <!-- Alerta de diferencia -->
                          <div class="alert alert-warning hide" id="alertaDiferencia" style="margin-top: 10px;">
                              <strong>¡Atención!</strong> 
                              <span id="textoFalta">Falta por pagar:</span>
                              <span id="textoExceso" style="display:none;">Excede el total por:</span>
                              <strong id="diferenciaMonto">S/ 0.00</strong>
                          </div>
                      </div>
                  </div>
              </div>
          </div>

    <div hidden="hidden" class="box-body">
     @foreach($procesos as $proc)
     <div class="col-lg-6">

       <input type="checkbox" name="proceso[]"  value="{{$proc->proc_id}}">
       <label>{{$proc->proc_nom}}</label>
     </div>
     @endforeach
   </div>

 </div>

</div>
</div>

</div>

<div class="col-lg-5">

  <div class="col-lg-12">
  <div class="box">
    <div class="box-body">
      <div class="col-lg-4">

        <select class="form-control" name="tipo_comprobante" id="tipo_comprobante">
          <option value="15">PROFORMA</option>
          <option value="16">PEDIDO</option>         
       </select>


     </div>
     <div  class="col-lg-8">
       <input type="number" name="num_ped" id="num_ped" onkeypress="if(event.keyCode == 13) buscarpedido();"  class="form-control" value="" placeholder="INGRESAR NUMERO" >


     </div>
     <div  class="col-lg-6" hidden='hidden'>
       <input type="text" name="bus_comprobante" id="bus_comprobante" onkeypress="if(event.keyCode == 13) buscarcomprobante();"  class="form-control"  placeholder="CAMBIAR COMPROBANTE" >


     </div>

   </div>
 </div>
</div>

  <div class="col-lg-12">
    <div class="box">

     <div class="box-header" style="background-color:blue;">
      </div>
    <div class="box-body">



     <div class="row">
      <div class="col-lg-4" hidden='hidden'>
        <div class="form-group form-group-sm">
          <LABEL>Almacenes</LABEL>
          <select name="id_almacen" id="id_almacen" class="form-control">
            @foreach($almacenes as $alm)
            <option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
            @endforeach
          </select>
        </div>
      </div>

      <div class="col-lg-4" hidden='hidden'>
        <div class="form-group form-group-sm">
          <LABEL>Vendedor</LABEL>
          <select name="vendedor" id="vendedor" class="form-control">

           @foreach($vendedores as $ven)

           @if($ven->IdUsuario == Auth::user()->IdUsuario)
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
        <LABEL>F.Pago</LABEL>
        <select name="estadopago" id="estadopago" class="form-control">
          @foreach($creditos as $cre)
          <option value="{{$cre->cre_dia_id}}" data-medio="{{$cre->cre_dia_tip}}" data-dias="{{$cre->cre_dia_fac}}">{{$cre->cre_dia_nom}}</option>
          @endforeach
        </select>
      </div>
    </div>


    <div class="col-lg-3">
      <div class="form-group form-group-sm">
       <label>F. Emision</label>
       <input  type="date" id="fecEmi" name="fecEmi" readonly="readonly" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
     </div>
   </div>



   <div class="col-lg-6">
    <div class="form-group form-group-sm">
      <div class="btn-group btn-group-toggle" data-toggle="buttons">
        @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin') || Auth::User()->hasRole('vendedor2') || Auth::User()->hasRole('caja'))
        <label  >
          <input type="radio" name="tdocod" id="boleta" value="03"   checked="checked"> BO
        </label>
        <label  >
          <input type="radio" name="tdocod" id="factura" value="01" > FA
        </label>
        <label>
          <input type="radio" name="tdocod" id="nota" value="13"  > NV
        </label>
        <label hidden="hidden" >
          <input type="radio" name="tdocod" id="vale" value="14"  > VALE
        </label>
        <label>
          <input type="radio" name="tdocod" id="proforma"  value="15"> PROF
        </label>
        @endif
        @if(Auth::User()->hasRole('vendedor'))
        <label hidden='hidden'>
          <input type="radio" name="tdocod" id="proforma" checked="checked" value="15"> PROF
        </label>
        @endif
      </div>
    </div>

  </div>





   <div class="col-lg-3"  id="divfecVen">
     <div class="form-group form-group-sm">
      <label>F. Vencim.</label>
      <input type="date" name="fecVen" id="fecVen" value="{{Carbon::now()->format('Y-m-d')}}"  class="form-control">
    </div>
  </div>

  <div  class="col-lg-4" id="divcuotas">
    <div class="form-group form-group-sm">

     <br><button type="button" class="btn btn-sm btn-primary"  data-target="#modal-cuotas" data-toggle="modal">CUOTAS</button>
   </div>


 </div>

</div>


<div class="row">
  

  <div  class="col-lg-6" hidden='hidden'>
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

<div  class="col-lg-12" hidden='hidden'>
    <div class="form-group form-group-sm">
      <label class="radio-inline">
        <input type="radio" name="tipo_venta" value="NORMAL" checked> NORMAL
      </label>
      <label class="radio-inline">
        <input type="radio" name="tipo_venta" id="venta_masiva" value="MASIVA"> VENTA MASIVA
      </label>
    </div>      
</div>

</div>

</div>
<div class="row" hidden='hidden'>
  <div class="col-lg-4">
   <div class="form-group form-group-sm">
    <label>GUIA REMISIÓN</label>
    <input type="text" name="guia_remision" id="guia_remision" class="form-control">
    <input type="hidden" name="IdCpe_guia" id="IdCpe_guia" class="form-control">
  </div>
</div>
<div class="col-lg-6 form-group form-group-sm">
  <label>Destino</label>
  <select class="form-control input-sm ubigeo" name="ubi_cod_env" id="ubi_cod_env">
    <option></option>
    @foreach($ubigeos as $ubi)
    <option value="{{$ubi->ubi_cod}}">{{$ubi->ubi_des}}</option>
    @endforeach
  </select>
</div>
<div class="col-lg-4"  hidden='hidden'>
 <div class="form-group form-group-sm">
  <label>PLACA</label>
  <input type="text" name="placa_comp" class="form-control">
</div>
</div>

</div>


<div hidden="hidden" class="row">
  <div class="col-lg-4">
   <div class="form-group form-group-sm">
    <label>Habitaciones</label>

    <select  class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="hab_id">
     <option></option>
     @foreach($habitaciones as $hab)
     <option value="{{$hab->hab_id}}">{{$hab->hab_nom}}</option>
     @endforeach
   </select>
 </div>
</div>

</div>

</div>
<div class="box-header" style="background-color:blue;">
  <div class="box-tools pull-right">
   <a  data-target="#modal-cliente" data-toggle="modal"><button type="button" class="btn btn-success btn-sm">NUEVO CLIENTE</button></a>
 </div>
</div>
<div class="box-body" id="divcliente">
 <div class="row form-group form-group-sm">


  <div hidden="hidden" class="col-lg-2">
    <div class="form-group form-group-sm">
      <label for="clinum">Num. Doc</label>
      <input type="text"  name="clinum" id="clinum" value="{{old('clinum')}}"  placeholder="" class="form-control" >

    </div>
  </div>


  <div class="col-lg-12" >
    <div class="form-group">
      <label class="control-label">Cliente</label>
      <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="clicod" id="clicod" onchange="seleccionarcliente();">
        <option>VENTA AL PORTADOR</option>
        @foreach($clientes as $cliente)
        <option value="{{$cliente->clicod}}" data-documento="{{$cliente->tdicod}}" data-clinum="{{$cliente->clinum}}"  data-direccion="{{$cliente->clidir}}" data-clinom="{{$cliente->clinom}}" data-correo="{{$cliente->clicor}}" data-correo2="{{$cliente->clicor2}}" data-correo3="{{$cliente->clicor3}}" data-correo4="{{$cliente->clicor4}}"  data-telefono="{{$cliente->telefono}}">{{$cliente->clinum}} - {{$cliente->clinom}}</option>
        @endforeach
      </select>
      <input type="hidden" readonly="readonly" name="clinom" id="clinom">
    </div>
  </div>

  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
    <div class="form-group form-group-sm">
      <label>Tipo</label>
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

  <div class="col-lg-9">


   <label>Direcci&oacute;n</label>
   <div class="input-group input-group-sm">

    <input name="clidir" id="clidir" value="--" class="form-control">
    <span class="input-group-btn">
      <button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="seleccionardireccion();">...</button>
    </span>
  </div>


</div>


<div class="col-lg-2 col-md-3 col-sm-12 col-xs-12" hidden='hidden'>
  <div class="form-group form-group-sm">
    <label>Correo</label>
    <input name="clicor" id="clicor" value="{{old('clicor')}}" class="form-control">
  </div>
</div>
<div hidden="hidden" class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label>Correo Electr&oacute;nico 2</label>
    <input name="clicor2" id="clicor2" value="" class="form-control">
  </div>
</div>
<div hidden="hidden"  class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label>Correo Electr&oacute;nico 3</label>
    <input name="clicor3" id="clicor3" value="" class="form-control">
  </div>
</div>
<div hidden="hidden"  class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
  <div class="form-group form-group-sm">
    <label>Correo Electr&oacute;nico 4</label>
    <input name="clicor4" id="clicor4" value="" class="form-control">
  </div>
</div>
              </div>
          </div>

          <div class="row" id="div_alerta_puntos" style="display:none; margin-top: 15px; margin-bottom: 15px;">
    <div class="col-lg-12">
        <div class="alert alert-info" id="box_alerta_puntos" style="border-radius: 8px;">
            <h4 style="margin-bottom: 5px;">
                <i class="fa fa-gift"></i> Puntos Hola P: <b id="lbl_puntos_actuales" style="font-size: 1.3em;">0</b>
            </h4>
            <div id="txt_mensaje_puntos" style="font-size: 11pt;"></div>
            <div id="lista_premios_a_entregar" style="margin-top: 10px;"></div>
        </div>
    </div>
</div>

          <div class="box-header" style="background-color:blue;">
            </div>
          <div class="box-body">

            <br>
            <div class="row">
             <div class="col-lg-3" hidden='hidden'>
              <div class="form-group-sm">
                <label>ICBPER</label>
                <input type="text" class="form-control" style="font-size:16pt;font-weight:bold;" id="tot_icbper" name="tot_icbper" value='0.00' readonly="readonly">
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group-sm">
                <label>Total</label>
                <input type="text" class="form-control" style="font-size:16pt;font-weight:bold;" id="total" name="total" value='0.00' readonly="readonly">
                  <div class="form-group" style="margin-top:10px;" hidden='hidden'>
                    <label for="monto_descuento">Monto Descuento (S/)</label>
                    <input type="text" readonly class="form-control" id="monto_descuento" value="0.00">
                  </div>
                  <script>
                    document.addEventListener("DOMContentLoaded", function () {
                      const totalInput = document.querySelector('input[name="total"]');
                      const descuentoInput = document.querySelector('input[name="descuento_global"]');
                      const descuentoMonto = document.getElementById('monto_descuento');

                      function actualizarDescuento() {
                        const total = parseFloat(totalInput.value) || 0;
                        const porcentaje = parseFloat(descuentoInput.value) || 0;
                        const monto = total * (porcentaje / 100);
                        descuentoMonto.value = monto.toFixed(2);
                        const totalConDescuento = total - monto;                        
                        // Actualizar la suma total de medios de pago
                        actualizarSumaTotal();
                      }
                      if (descuentoInput) {
                          descuentoInput.addEventListener("input", actualizarDescuento);
                      }
                      if (totalInput) {
                          totalInput.addEventListener("input", actualizarDescuento);
                      }
                      actualizarDescuento(); 
                    });
                  </script>        
              </div>
            </div>

            <div class="col-lg-3">
              <div class="form-group-sm">
                  <label>Comisión (+)</label>
                  <input type="text" class="form-control" style="font-size:16pt;font-weight:bold; color: red;" id="comision_total" name="comision" value='0.00' readonly="readonly">
              </div>
          </div>

            <div  class="col-lg-3">
              <div class="form-group-sm">
                <label>Paga con:</label>
                <input type="number"  step="any" class="form-control" style="font-size:16pt;font-weight:bold;"  id="pagar" name="pagar" value="0.00" onkeyup="calculartotal();">
              </div>
            </div>
            <div  class="col-lg-3">
              <div class="form-group-sm">
                <label>Vuelto</label>
                <input type="text" class="form-control" style="font-size:16pt;font-weight:bold;"  id="vuelto" name="vuelto" value="0.00" readonly="readonly">
              </div>
            </div>            

            <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
          </div>

          <br>
          <!-- SECCIÓN NUEVA DE MEDIOS DE PAGO -->
          <div class="row">

            <div class="col-lg-4">
              <button type="button" id="btnRegComp" class=" btn btn-block btn-success btn-lg botones">IMPRIMIR</button><br>
            </div>
            
            <div class="col-lg-4">              
                <div class="form-group" style="margin-bottom: 15px;" hidden='hidden'>
                  <label for="descuento_global">% Descuento Global</label>
                  <input type="number" step="0.01" id="descuento_global" name="descuento_global" value="0" class="form-control" placeholder="0%" min="0" max="100">
                </div>
                <button type="button" id="btnRegImp" class=" btn btn-block btn-primary btn-lg botones">REGISTRAR</button><br>
            </div>
            
            
            


            <div class="col-lg-4">
              <a href="/pos"><button type="button" class=" btn btn-block btn-danger btn-lg botones">CANCELAR</button></a><br>
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
@include('empresas.puntosventas.modaldatosvehiculo')

{!!Form::close()!!}
</div>

</div>
@include('empresas.puntosventas.modalgasto')

@endsection

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="{{ asset('js/sweetalert2/sweetalert2.all.min.js') }}"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    const descInput = document.getElementById('descuento_global');
    if (descInput) {
        descInput.addEventListener('input', function () {
            let val = parseFloat(this.value);
            if (val > 50) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Límite excedido',
                    text: 'El descuento máximo permitido es 50%',
                    confirmButtonText: 'Entendido'
                });
                this.value = 50;
            } else if (val < 0) {
                this.value = 0;
            }
        });
    }
});

window.puntos_base = 0;
window.reglas_puntos = [];
window.puntos_gastados = 0;
window.cliente_id_actual = null;

function verificar_puntos(clicod) {
    if(!clicod || clicod == '' || clicod == 'VENTA AL PORTADOR') {
        $("#div_alerta_puntos").hide();
        window.cliente_id_actual = null;
        return;
    }
    
    window.cliente_id_actual = clicod;
    
    $.ajax({ 
        type: "GET", 
        dataType: "json", 
        url: "/cliente/" + clicod + "/puntos" 
    }).done(function(res) {
        window.puntos_base = res.puntos; 
        window.reglas_puntos = res.reglas; 
        window.puntos_gastados = 0;
        $("#lista_premios_a_entregar").empty(); 
        actualizar_caja_puntos();
    });
}

function actualizar_caja_puntos() {
    if(!window.cliente_id_actual) return;
    
    // Leemos el total base neto (sin comisiones de medios de pago)
    var total_venta = parseFloat($('#total').attr('data-base')) || 0; 
    var puntos_proyectados = Math.floor(total_venta / 1); 
    var puntos_totales = window.puntos_base + puntos_proyectados - window.puntos_gastados;

    $("#div_alerta_puntos").show();
    $("#lbl_puntos_actuales").html(puntos_totales + " <small>(+ " + puntos_proyectados + " por la compra de hoy)</small>");

    var html_premios = '<ul style="list-style: none; padding-left: 0; margin-top: 10px;">';
    var puede_canjear = false;

    if(window.reglas_puntos && window.reglas_puntos.length > 0) {
        $.each(window.reglas_puntos, function(index, regla) {
            if(puntos_totales >= regla.puntos_minimos) {
                puede_canjear = true;
                html_premios += '<li style="margin-bottom: 8px; font-size: 11pt;">' +
                                '<i class="fa fa-check-circle" style="color:#00a65a;"></i> Disponible: <strong style="color:#00a65a;">' + regla.premio + '</strong> (' + regla.puntos_minimos + ' pts)' + 
                                '<button type="button" class="btn btn-xs btn-success text-bold" style="margin-left: 10px; font-weight:bold;" onclick="agregar_premio(' + regla.id + ', \'' + regla.premio + '\', ' + regla.puntos_minimos + ')"><i class="fa fa-plus"></i> Canjear Premio</button>' +
                                '</li>';
            } else {
                html_premios += '<li style="margin-bottom: 8px; opacity: 0.7; font-size: 11pt;"><i class="fa fa-lock"></i> Faltan <b>' + (regla.puntos_minimos - puntos_totales) + ' pts</b> para: ' + regla.premio + '</li>';
            }
        });
    }
    html_premios += '</ul>';
    $("#txt_mensaje_puntos").html(html_premios);

    if(puede_canjear) { 
        $("#box_alerta_puntos").removeClass('alert-info alert-warning').addClass('alert-success'); 
    } else { 
        $("#box_alerta_puntos").removeClass('alert-success alert-warning').addClass('alert-info'); 
    }
}

function agregar_premio(regla_id, premio_nombre, costo) {
    window.puntos_gastados += costo;
    var html_premio = '<div class="alert alert-warning" style="padding: 8px; margin-top: 5px; margin-bottom: 5px; color: #856404; background-color: #fff3cd; border-color: #ffeeba; border-radius: 4px; font-weight: bold;">' +
                      '<input type="hidden" name="premios_canjeados[]" value="' + regla_id + '">' +
                      '🎁 PREMIO SELECCIONADO: ' + premio_nombre + ' (-' + costo + ' pts) ' +
                      '<button type="button" class="btn btn-xs btn-danger pull-right" style="font-weight:bold;" onclick="quitar_premio(this, ' + costo + ')"><i class="fa fa-times"></i> Quitar</button></div>';
    $("#lista_premios_a_entregar").append(html_premio);
    actualizar_caja_puntos(); 
}

function quitar_premio(btn, costo) {
    window.puntos_gastados -= costo;
    $(btn).parent().remove();
    actualizar_caja_puntos();
}


</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const tipoVentaRadios = document.querySelectorAll('input[name="tipo_venta_modo"]');
    const botonRegistrar = document.getElementById('btnRegistrar');
    const tipoVentaInput = document.getElementById('tipo_venta_masiva');

    if (tipoVentaRadios && tipoVentaRadios.length > 0) {
        tipoVentaRadios.forEach(radio => {
            radio.addEventListener('change', function () {
                if (this.value === 'masiva') {
                    tipoVentaInput.value = 'masiva';
                    Swal.fire('Modo Venta Masiva activado', 'Se generarán comprobantes a todos los clientes marcados con MENSUAL = SI', 'info');
                } else {
                    tipoVentaInput.value = 'normal';
                }
            });
        });
    }

    $(document).ready(function() {
        $('#venta_masiva').on('click', function() {
          if ($(this).is(':checked')) {
            Swal.fire({
              icon: 'info',
              title: 'VENTA MASIVA ACTIVADA',
              text: 'Se va a realizar venta masiva para todos los clientes con mensual = "SI".',
              confirmButtonText: 'Aceptar'
            });
          }
        });
      });
});
</script>