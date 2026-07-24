@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')
@include('empresas.puntosventas.modaldirecciones')
@include('empresas.puntosventas.modalclientes')

<script type="text/javascript">

    window.puntos_base = 0;
    window.reglas_puntos = [];
    window.puntos_gastados = 0;
    window.cliente_id_actual = null;

    // 1. BLOQUEO DE SALIDA (Lanza el cartel del navegador al intentar refrescar o cerrar)
    // Este no se puede evitar, el navegador siempre preguntará: "¿Desea salir de este sitio?"
    window.onbeforeunload = function() { 
        return "¡Cuidado! El cobro está en proceso. Si refrescas podrías perder los datos."; 
    };

    // 2. BLOQUEO DE FLECHAS (Atrás/Adelante)
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };

    

    /*$(document).keydown(function (e) {
      
        if (e.which === 116 || (e.ctrlKey && e.which === 82) || (e.ctrlKey && e.shiftKey && e.which === 82)) {
            e.preventDefault();
            alert("Para actualizar, debes terminar el cobro primero.");
        }
    });*/
    
    $(document).ready(function(){

      // 3. BLOQUEO DE TECLADO (F5, Ctrl+R, etc.)
        $(document).keydown(function (e) {
            var code = e.keyCode || e.which;
            // F5 (116) o Ctrl+R (82)
            if (code === 116 || (e.ctrlKey && code === 82)) {
                e.preventDefault();
                alert("La actualización de página está deshabilitada durante el cobro.");
                return false;
            }
        });

        calcular_total()

          var comprobante = $("#comprobante").val();
           var documento = $("#documento").val();
           $("#btnPrint").printPage({

            url: "/voucher/"+comprobante,
            attr: "href",
            messageBox:false

          })
           $("#btnRegistrar").on("click", function(e) {
                // 1. Prevenimos el envío por defecto para controlar el flujo
                e.preventDefault();

                // 2. Obtener lo que FALTA PAGAR para validar el cuadre (en lugar del total_venta)
                var totalPendienteStr = $("#total_pendiente").val();
                if (!totalPendienteStr) { totalPendienteStr = "0"; } // Por si está vacío
                var totalPendiente = parseFloat(totalPendienteStr.replace(',', '.')) || 0;
                
                var totalPagado = 0;
                var filasPagos = $('#tbody_med_pag tr').length; // Usamos el ID del tbody que es más preciso

                console.log("Iniciando validación de cobro...");
                console.log("Falta cobrar: " + totalPendiente);

                // 3. Validar montos si hay pagos en la tabla
                if (filasPagos > 0) {
                    $("#tbody_med_pag tr").each(function() {
                        // Buscamos el input del monto en la fila
                        var montoFilaStr = $(this).find("input[name='mon_med_pag[]']").val().replace(',', '.');
                        var montoFila = parseFloat(montoFilaStr) || 0;
                        totalPagado += montoFila;
                    });

                    console.log("Suma detectada en tabla: " + totalPagado);

                    // Comparación con margen de error mínimo para decimales
                    // AHORA COMPARAMOS totalPagado CONTRA totalPendiente
                    if (Math.abs(totalPagado - totalPendiente) > 0.01) {
                        var dif = (totalPendiente - totalPagado).toFixed(2);
                        alert("¡REVISAR MEDIO DE PAGO!\n\nFalta cobrar S/ " + totalPendiente.toFixed(2) + 
                              "\nUsted ha ingresado S/ " + totalPagado.toFixed(2) + 
                              "\nDiferencia: S/ " + dif);
                        return false;
                    }
                }

                // 4. Validar que haya productos en la mesa
                if ($('#items_pedidos tr').length == 0){
                    alert("No hay productos para cobrar.");
                    return false;
                }

                // 5. Si todo está OK, lanzamos el AJAX
                console.log("Todo correcto. Enviando cobro...");
                
                var formulario = $("#frmcomandas").serializeArray();
                $("#imgload").show();
                $(".botones").hide();

                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/registrarcobro',
                    data: formulario,
                }).done(function(respuesta){
                    if(respuesta.estado =='error'){
                        alert(respuesta.mensaje);
                        $("#imgload").hide();
                        $(".botones").show();
                    } else {
                        window.onbeforeunload = null;
                        window.location.href = "/seleccion/";
                    }
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Error en el servidor: ", textStatus, errorThrown);
                    alert("Hubo un error al procesar el cobro en el servidor.");
                    $("#imgload").hide();
                    $(".botones").show();
                });
            });

           
           /*$("#btnRegistrar").on("click", function(e) {           
            e.preventDefault();            
            var totalVentaStr = $("#total_venta").val().replace(',', '.');
            var totalVenta = parseFloat(totalVentaStr) || 0;            
            var totalPagado = 0;
            var filasPagos = $('#tbody_med_pag tr').length;
            console.log("Iniciando validación de cobro...");
            console.log("Total a cobrar: " + totalVenta);
            if (filasPagos > 0) {
                $("#tbody_med_pag tr").each(function() {                    
                    var montoFilaStr = $(this).find("input[name='mon_med_pag[]']").val().replace(',', '.');
                    var montoFila = parseFloat(montoFilaStr) || 0;
                    totalPagado += montoFila;
                });
                console.log("Suma detectada en tabla: " + totalPagado);
                if (Math.abs(totalPagado - totalVenta) > 0.01) {
                    var dif = (totalVenta - totalPagado).toFixed(2);
                    alert("¡REVISAR MEDIO DE PAGO!\n\nEl total es S/ " + totalVenta.toFixed(2) + 
                          "\nUsted ha ingresado S/ " + totalPagado.toFixed(2) + 
                          "\nDiferencia: S/ " + dif);
                    return false;
                }
            }
            if ($('#items_pedidos tr').length == 0){
                alert("No hay productos para cobrar.");
                return false;
            }
            console.log("Todo correcto. Enviando cobro...");            
            var formulario = $("#frmcomandas").serializeArray();
            $("#imgload").show();
            $(".botones").hide();
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: '/registrarcobro',
                data: formulario,
            }).done(function(respuesta){
                if(respuesta.estado =='error'){
                    alert(respuesta.mensaje);
                    $("#imgload").hide();
                    $(".botones").show();
                } else {
                    window.onbeforeunload = null;
                    window.location.href = "/seleccion/";
                }
            }).fail(function(jqXHR, textStatus, errorThrown) {
                console.error("Error en el servidor: ", textStatus, errorThrown);
                alert("Hubo un error al procesar el cobro en el servidor.");
                $("#imgload").hide();
                $(".botones").show();
            });
        });*/

        
        var metodo = $('#estadopago').find(':selected').attr('data-medio');
        var dias = $('#estadopago').find(':selected').attr('data-dias');
        var nuevafecha = $('#fecEmi').val();

        if(metodo=='CREDITO'){
            $("#divfecVen").hide('true');
            $("#fecVen").val(nuevafecha);
            $("#divcuotas").show('true');
            $('#tbody_med_pag').empty();
            calcular_vuelto();
        }

        if(metodo =='CONTADO'){
            $("#divfecVen").hide('true');
            $("#fecVen").val($("#fecEmi").val());
            $('#predeterminado_1').val($('#total').val());
            $("#divcuotas").hide('true');
        }

        if(metodo =='PERSONALIZADO'){
            $("#divfecVen").show('true');
            $("#divcuotas").show('true');
            $('#tbody_med_pag').empty();
            calcular_vuelto();
        }


        $("#estadopago").on("change", function() {
            var metodo = $(this).find(':selected').attr('data-medio');
            var dias = $(this).find(':selected').attr('data-dias');
            var nuevafecha = $('#fecEmi').val();



            if(metodo=='CREDITO'){


                $("#divfecVen").hide('true');
                $("#fecVen").val(nuevafecha);
                $("#divcuotas").show('true');
                $('#tbody_med_pag').empty();
                calcular_vuelto();

            }

            if(metodo =='CONTADO'){

                $("#divfecVen").hide('true');
                $("#divcuotas").hide('true');
                $("#fecVen").val($("#fecEmi").val());
                $('#predeterminado_1').val($('#total').val());
            }

            if(metodo =='PERSONALIZADO'){


                $("#divfecVen").show('true');
                $("#divcuotas").show('true');
                $('#tbody_med_pag').empty();
                calcular_vuelto();
            }
        });


        $(".selectpicker").selectpicker();

        $("#tipo").val('1');


        $("#btnCambiar").click(function(){
            var mesa_actual = $("#mes_id").val();
            var mesa_nom_actual = $("#mes_nom").val();
            var ped_id_actual = $("#ped_id").val();

            if(mesa_actual===""){
                alert('Elegir una mesa');
            }else{
                $("#mes_id_act").val(mesa_actual);
                $("#ped_id_act").val(ped_id_actual);
                $("#mes_act").val(mesa_nom_actual);
                $("#modal-cambiar-mesa").modal("show");
            }

        });

        $("#btnComanda").click(function(){

            var formulario = $("#frmcomandas").serializeArray();
            var accion = $("#accion").val();
            var tipo_comanda = $("#tipo").val();

            //$("#imgloadcliente").show();
            
            if(accion=='0'){

                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/registrarcomanda',
                    data: formulario,
                }).done(function(respuesta){

                    if(respuesta.estado=='error'){
                        alert(respuesta.mensaje);
                    }else{
                        /*$("#tipo").val(tipo_comanda);
                        alert(respuesta.mensaje);
                        limpiarpedido();*/

                        window.location.href = "/consola";

                    }

                });

            }else{

                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/actualizarcomanda',
                    data: formulario,
                }).done(function(respuesta){
                    window.location.href = "/consola";
                    /*$("#tipo").val(tipo_comanda);
                    alert(respuesta.mensaje);
                    limpiarpedido();*/  
                });

            }
            

        });



        $("#btnSalon").click(function(){
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: '/panelsalon',
            }).done(function(respuesta){
                $("#tipo").val('1');
                $("#salon").html(respuesta.vista);  
                limpiarpedido();    
            });
        });

        $("#btnDelivery").click(function(){
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: '/paneldelivery',
            }).done(function(respuesta){
                $("#tipo").val('2');
                $("#salon").html(respuesta.vista);  
                limpiarpedido();    
            });
        });

        $("#btnLlevar").click(function(){
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: '/panelllevar',
            }).done(function(respuesta){
                $("#tipo").val('3');
                $("#salon").html(respuesta.vista);  
                limpiarpedido();    
            });
        });


        $("#txt_bus_pro").keyup(function(){

            var producto = $(this).val();
            var contarcarateres = $(this).val().length;

            if(contarcarateres >0){
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "/buscarcarta/"+producto,
                }).done(function(respuesta){
                    $("#items_productos").html(respuesta.vista);
                });
            }

        });

        $("#piso").change(function(){

            $("#mes_id").val("");
            $("#pis_id").val("");
            var piso = $(this).val();

            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/buscarmesas/"+piso,
            }).done(function(respuesta){
                $("#listar_mesas").html(respuesta.vista);
            });

        });


    });


function buscar_producto_categoria(id){
    var producto=0;
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/buscarcarta/"+producto+"/"+id,
    }).done(function(respuesta){
        $("#items_productos").html(respuesta.vista);
    });
}

    function agregar_item(){

            var id_ = $('#_id').val();
            var producto_ = $('#_producto').val();
            var precio_ = $('#_precio').val();
            var acompa_ = $('#_acompa').val();
            var icbper_ = $('#_icbper').val();

            var validar = checkId(id_);

            if (validar==true){
                $("#tbl_detalle  > tbody  > tr").each(function(){
                    if(id==$(this).find("td:eq(0) > input").val()){
                        var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;

                        $(this).find("td:eq(2) > input").val(calcular_cantidad);
                    }
                });

            }else{

                $('#items_pedidos').append('<tr>'+
                '<td hidden="hidden" id="'+id_+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
                '<td>'+producto_+' '+acompa_+'</td>'+
                '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
                '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
                '<td style="text-align:right;" ><input  class="form-control" type="number"  step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
                '<td style="text-align:right;" hidden="hidden" ><input  class="form-control" type="text"   name="descripcion[]" value="'+producto_+' '+acompa_+'"></td>'+
                '<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
                '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
                '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
            }

            $('#_id').val('');
            $('#_producto').val('');
            $('#_precio').val('');
            $('#_acompa').val('');

    

        calcular_total();

    }

    function elegir_acompanamiento(id,producto,precio,cat_sig,acom,icbper){

        var cat_acom = $('#cat_acom').val();

        if(cat_acom=='1'){

            $('#_acompa').val(producto);
            agregar_item();

            $('#_id').val('');
            $('#_producto').val('');
            $('#_precio').val('');
            $('#_acompa').val('');
            $('#_icbper').val('');
        

        }else{

            $('#_id').val(id);
            $('#_producto').val(producto);
            $('#_precio').val(precio);
            $('#_icbper').val(icbper);
            $("#btnAgregarItem").hide();    

        }
        

        if(cat_sig !=''){

                buscar_producto_categoria(cat_sig);

            
            

        }else{


            var id_ = $('#_id').val();
            var producto_ = $('#_producto').val();
            var precio_ = $('#_precio').val();
            var icbper_ = $('#_icbper').val();

            var validar = checkId(id_);

            if (validar==true){
                $("#tbl_detalle  > tbody  > tr").each(function(){
                    if(id==$(this).find("td:eq(0) > input").val()){
                        var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;

                        $(this).find("td:eq(2) > input").val(calcular_cantidad);
                    }
                });

            }else{

                $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
                '<td>'+producto_+'</td>'+
                '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
                '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
                '<td style="text-align:right;" ><input class="form-control" type="number" step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
                '<td style="text-align:right;" hidden="hidden"  ><input  class="form-control" type="text"   name="descripcion[]" value="'+producto_+'"></td>'+
                '<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
                    '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
                '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
            }

                $('#_id').val('');
                $('#_producto').val('');
                $('#_precio').val('');
                $('#_acompa').val('');
                
        }
        

        calcular_total();

    }

function checkId(id) {

    var contar=0;

    $("#tbl_detalle  > tbody  > tr").each(function(){

        if(id==$(this).find("td:eq(0) > input").val()){
            contar = contar+1;
        }
    });


    if(contar>0){
        return true;
    }else{
        return false;
    }

}

function checkIdMedPag(id) {

    var contar=0;

    $("#tbl_med_pag > tbody  > tr").each(function(){

        if(id==$(this).find("td:eq(0) > input").val()){
            contar = contar+1;
        }
    });


    if(contar>0){
        return true;
    }else{
        return false;
    }

}

function eliminar_item(btn) {
    var row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);

};

function eliminar_item_registrado(btn,item) {

    eliminar_item_pedido(item);

    var row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);





};




function elegir_mesa(mesa,id,nombre){

    var piso =  $("#piso option:selected").text();
    var pis_id = $("#piso option:selected").val();

    $("#mes_id").val(id);
    $("#mes_nom").val(nombre);
    $("#pis_id").val(pis_id);

    $("#lbl_pis_mes").text(piso+' / '+mesa);

    consultar_mesa_pedido(id);

}

function eliminar_item_pedido(item){

    var pedido = $("#ped_id").val();

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/eliminaritem/"+item+"/"+pedido,
    }).done(function(respuesta){

        alert(respuesta.mensaje);

    });


}

function consultar_mesa_pedido(id){

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/buscarpedidomesa/"+id,
    }).done(function(respuesta){
        if(respuesta.estado=='1'){
            $("#listar_pedido").html(respuesta.vista);
            $("#ped_id").val(respuesta.ped_id);
            $("#accion").val("1");

        }else{
            $('#items_pedidos').empty();
            $("#accion").val("0");
            $("#ped_id").val("");
                //alert('no encontrado');

              }
              calcular_total();
            });



}


function consultar_pedido_llevar_delivery(id){

    $.ajax({
        type: "GET",
        dataType: 'json',
        url: "/buscarpedidollevardelivery/"+id,
    }).done(function(respuesta){
        if(respuesta.estado=='1'){
            $("#listar_pedido").html(respuesta.vista);
            $("#ped_id").val(respuesta.ped_id);
            $("#accion").val("1");

        }else{
            $('#items_pedidos').empty();
            $("#accion").val("0");
            $("#ped_id").val("");
                //alert('no encontrado');

              }
              calcular_total();
            });



}

function limpiarpedido(){
    $("#mes_id").val("");
    $("#pis_id").val("");
    $("#accion").val("0");

    $("#ped_num_doc").val("");
    $("#ped_cli_nom").val("");
    $("#ped_dir").val("");
    $("#ped_obs").val("");
    $("#tdicod").val("1").attr('selected', 'selected');

    $("#ped_tel").val("");
    $("#ped_ref").val("");
    $("#ped_pag_tar").prop("checked", false);
    $("#ped_pag_efe").val("");
    $("#ped_fac").prop("checked", false);

    $('#items_pedidos').empty();

    calcular_total();

}

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


    function calcular_total(){
        var total_general = 0; // El costo de toooooodo lo que pidió la mesa
        var total_pagado = 0;  // El costo de lo que ya te pagaron por adelantado
        var total_pendiente = 0; // Lo que tienes que cobrarle AHORITA al cliente

        var total_icbper_general = 0;
        var icbper_val = parseFloat($("#icbper_val").val()) || 0;

        $("#tbl_detalle tbody tr").each(function(){
            // Detectamos si la fila es de un producto ya pagado
            var esPagado = $(this).attr('data-pagado') === '1';
            
            var cantidad = parseFloat($(this).find("td:eq(2)> input").val()) || 0;
            var precio = parseFloat($(this).find("td:eq(4)>input").val()) || 0;
            var icbper_ind = $(this).find("td:eq(5)> input").val();
            
            var subtotal_producto = cantidad * precio;
            var subtotal_icbper = (icbper_ind == 1) ? (cantidad * icbper_val) : 0;

            // Sumamos al costo total de la mesa sin importar el estado
            total_general += subtotal_producto;
            total_icbper_general += subtotal_icbper;

            // Separamos el dinero según el estado
            if (esPagado) {
                total_pagado += (subtotal_producto + subtotal_icbper);
            } else {
                total_pendiente += (subtotal_producto + subtotal_icbper);
            }
        });

        total_general += total_icbper_general;

        // Actualizamos los campos ocultos y el input del comprobante
        $('#icbper_tot').val(total_icbper_general.toFixed(2));
        $('#total_venta').val(total_general.toFixed(2)); // <-- Mandamos el total completo al form

        // Actualizamos el cuadrito amarillo
        $('#lbl_total_general').text(total_general.toFixed(2));
        $('#lbl_total_pagado').text(total_pagado.toFixed(2));
        $('#lbl_total_pendiente').text(total_pendiente.toFixed(2));
        
        // Guardamos lo pendiente en el input oculto para usarlo en la validación
        $('#total_pendiente').val(total_pendiente.toFixed(2));

        calcular_vuelto();

        if(typeof actualizar_caja_puntos === "function") { actualizar_caja_puntos(); }
    }
    /*function calcular_total(){
        var total_general = 0;
        var total_pagado = 0;
        var total_pendiente = 0;
        var total_icbper_general = 0;
        var total_icbper_pendiente = 0;
        var icbper_val = parseFloat($("#icbper_val").val()) || 0;
        $("#tbl_detalle tbody tr").each(function(){            
            var esPagado = $(this).attr('data-pagado') === '1';            
            var cantidad = parseFloat($(this).find("td:eq(2)> input").val()) || 0;
            var precio = parseFloat($(this).find("td:eq(4)>input").val()) || 0;
            var icbper_ind = $(this).find("td:eq(5)> input").val();            
            var subtotal_producto = cantidad * precio;
            var subtotal_icbper = (icbper_ind == 1) ? (cantidad * icbper_val) : 0;
            total_general += subtotal_producto;
            total_icbper_general += subtotal_icbper;
            if (esPagado) {
                total_pagado += (subtotal_producto + subtotal_icbper);
            } else {
                total_pendiente += subtotal_producto;
                total_icbper_pendiente += subtotal_icbper;
            }
        });
        total_general += total_icbper_general;
        total_pendiente += total_icbper_pendiente;
        
        $('#icbper_tot').val(total_icbper_pendiente.toFixed(2));
        $('#total_venta').val(total_pendiente.toFixed(2));
        
        $('#lbl_total_general').text(total_general.toFixed(2));
        $('#lbl_total_pagado').text(total_pagado.toFixed(2));        
        
        calcular_vuelto();
    }*/


function agregar_medio_pago(){

    var med_pag = $("#med_pag").val();
    var mon_med_pag = $("#mon_med_pag").val();
    var nom_med_pag = $("#med_pag").find(':selected').attr('data-nom');
    var predeterminado = $("#med_pag").find(':selected').attr('data-predeterminado');


    var validar = checkIdMedPag(med_pag);

    if (validar==true){

        alert('EL MEDIO DE PAGO YA SE ENCUENTRA AGREGADO');

    }else{
        $('#tbl_med_pag').append('<tr><td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="id_med_pag[]" value="'+med_pag+'"></td>'+
            '<td hidden="hidden"><input type="number"  style="text-align:center;width:400px;"  class="form-control" name="mon_med_pag[]" value="'+mon_med_pag+'"></td>'+
            '<td hidden="hidden"><input type="text"  style="text-align:center;width:400px;"  class="form-control" name="predeterminado[]" value="'+predeterminado+'"></td>'+
            '<td><button class="btn btn-success btn-sm btn-block">'+nom_med_pag+'  S/ '+mon_med_pag+'</td>'+
            '<td><button type="button" onClick="ElimMedPag(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
    }

    $("#mon_med_pag").val('0.00');

    //calcular_vuelto();

}


function ElimMedPag(btn) {
    var row = btn.parentNode.parentNode;
    row.parentNode.removeChild(row);

    calcular_vuelto();

};



function  buscarclienteruc(){

    $("#div_alerta_puntos").hide();

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
            // $('#clicorn4').val(respuesta[0].cor4);
            // $('#clicorn2').val(respuesta[0].cor2);
            //  $('#clicorn3').val(respuesta[0].cor3);
            $('#clicod').val(respuesta[0].clicod);
            $("#tdicod").val(respuesta[0].tdicod).attr('selected', 'selected');

            if($('#tdicod').val() =='6' ){
                $("#tdocod").val('01').attr('selected', 'selected');
            }

            if($('#tdicod').val() =='1' ){
                $("#tdocod").val('03').attr('selected', 'selected');
            }

            verificar_puntos(respuesta[0].clicod);

            $("#imgloadcliente").hide();
            // $(".botones").show(); 

        }


    });



}
    
    function calcular_vuelto(){
        // El vuelto se calcula restando lo que le FALTA pagar, no el total de la mesa
        var total_pendiente = parseFloat($("#total_pendiente").val()) || 0; 
        var pagar = parseFloat($("#pagar").val()) || 0;
        var vuelto = pagar - total_pendiente;

        if(vuelto < 0){
            $("#vuelto").val("0.00");
        }else{
            $("#vuelto").val(vuelto.toFixed(2));   
        }
    }
/*function calcular_vuelto(){

    var total_venta = $("#total_venta").val();
    var pagar = $("#pagar").val();
    var vuelto = 0;
    var monto = 0;
    vuelto = pagar-total_venta;    
    if(vuelto<0){
        $("#vuelto").val(0);
    }else{
        $("#vuelto").val(vuelto);   
    }
}*/


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


function buscarclientenombre(){

    $("#div_alerta_puntos").hide();

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

    //verificar_puntos(clicod);

    $("#modal-lista-clientes").modal("hide");

    verificar_puntos(clicod);

}

    // Evitar que regresen con las flechas del navegador
history.pushState(null, null, location.href);
window.onpopstate = function () {
    history.go(1);
};

function verificar_puntos(clicod) {
    if(!clicod || clicod == '') return;
    window.cliente_id_actual = clicod;
    
    $.ajax({
        type: "GET",
        dataType: "json",
        url: "/cliente/" + clicod + "/puntos",
    }).done(function(res) {
        window.puntos_base = res.puntos;
        window.reglas_puntos = res.reglas;
        window.puntos_gastados = 0;
        $("#lista_premios_a_entregar").empty(); // Limpiamos la canasta de premios
        actualizar_caja_puntos();
    });
}

function actualizar_caja_puntos() {
    if(!window.cliente_id_actual) return;
    
    // 1. Calculamos los puntos proyectados de esta venta
    var total_venta = parseFloat($("#total_venta").val()) || 0;
    var puntos_proyectados = Math.floor(total_venta / 1); // 1 Sol = 1 Punto
    
    // 2. Calculamos la bolsa total disponible
    var puntos_totales = window.puntos_base + puntos_proyectados - window.puntos_gastados;

    $("#div_alerta_puntos").show();
    $("#lbl_puntos_actuales").html(puntos_totales + " <small>(Incluye +" + puntos_proyectados + " de esta venta)</small>");

    var html_premios = '<ul style="list-style: none; padding-left: 0; margin-top: 10px;">';
    var puede_canjear = false;

    if(window.reglas_puntos && window.reglas_puntos.length > 0) {
        $.each(window.reglas_puntos, function(index, regla) {
            
            // ==========================================
            // INICIO: LÓGICA DE VENCIMIENTO DEL PREMIO
            // ==========================================
            var etiqueta_vence = "";
            if(regla.fecha_vencimiento && regla.fecha_vencimiento !== null) {
                var f_venc = new Date(regla.fecha_vencimiento + "T23:59:59");
                var f_hoy = new Date();
                // Calculamos la diferencia en días
                var dif_dias = Math.ceil((f_venc - f_hoy) / (1000 * 60 * 60 * 24));

                if(dif_dias < 0) {
                    etiqueta_vence = ' <span class="label label-default" style="margin-left:5px;"><i class="fa fa-times"></i> Vencido</span>';
                } else if(dif_dias === 0) {
                    // ¡Vence HOY! Se pone en rojo para llamar la atención del cajero
                    etiqueta_vence = ' <span class="label label-danger" style="margin-left:5px;"><i class="fa fa-warning"></i> ¡Vence HOY!</span>';
                } else if(dif_dias <= 5) {
                    // Faltan 5 días o menos, alerta roja
                    etiqueta_vence = ' <span class="label label-danger" style="margin-left:5px;"><i class="fa fa-clock-o"></i> Vence en ' + dif_dias + ' días</span>';
                } else {
                    // Faltan más de 5 días, se muestra amarillo sutil
                    var dia = ("0" + f_venc.getDate()).slice(-2);
                    var mes = ("0" + (f_venc.getMonth() + 1)).slice(-2);
                    etiqueta_vence = ' <small style="color: #ffeb3b; font-weight: bold; margin-left:5px;"><i class="fa fa-calendar"></i> Hasta el ' + dia + '/' + mes + '</small>';
                }
            }
            // ==========================================
            // FIN: LÓGICA DE VENCIMIENTO
            // ==========================================

            if(puntos_totales >= regla.puntos_minimos) {
                puede_canjear = true;
                html_premios += '<li style="margin-bottom: 8px;">' +
                                '<i class="fa fa-check-circle"></i> Disponible: <b>' + regla.premio + '</b> (' + regla.puntos_minimos + ' pts)' + etiqueta_vence +
                                '<button type="button" class="btn btn-xs btn-default text-bold" style="color:#00a65a; margin-left: 10px;" onclick="agregar_premio(' + regla.id + ', \'' + regla.premio + '\', ' + regla.puntos_minimos + ')"><i class="fa fa-plus"></i> Añadir al Ticket</button>' +
                                '</li>';
            } else {
                var faltan = regla.puntos_minimos - puntos_totales;
                html_premios += '<li style="margin-bottom: 8px; opacity: 0.8;">' +
                                '<i class="fa fa-lock"></i> Te faltan <b>' + faltan + ' pts</b> para: ' + regla.premio + etiqueta_vence +
                                '</li>';
            }
        });
    } else {
        html_premios += '<li>No hay premios configurados actualmente.</li>';
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
    
    // Creamos una franja amarilla con un input hidden que se enviará en tu form "frmcomandas"
    var html_premio = '<div class="alert alert-warning" style="padding: 5px; margin-top: 5px; margin-bottom: 5px; color: #856404; background-color: #fff3cd; border-color: #ffeeba;">' +
                      '<input type="hidden" name="premios_canjeados[]" value="' + regla_id + '">' +
                      '🎁 <b>ENTREGAR: ' + premio_nombre + '</b> (-' + costo + ' pts) ' +
                      '<button type="button" class="btn btn-xs btn-danger pull-right" onclick="quitar_premio(this, ' + costo + ')"><i class="fa fa-times"></i></button>' +
                      '</div>';
    
    $("#lista_premios_a_entregar").append(html_premio);
    actualizar_caja_puntos(); // Recalculamos los colores
}

function quitar_premio(btn, costo) {
    window.puntos_gastados -= costo;
    $(btn).parent().remove();
    actualizar_caja_puntos();
}

function procesar_canje(cliente_id, regla_id, premio_nombre, puntos_costo) {
    if(confirm("¿Estás seguro de canjear '" + premio_nombre + "' por " + puntos_costo + " puntos?")) {
        
        // Obtenemos el token de seguridad que ya tienes en el header de tu sistema
        var token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            type: "POST",
            dataType: "json",
            url: "/canjear-premio",
            data: {
                _token: token,
                cliente_id: cliente_id,
                regla_id: regla_id
            }
        }).done(function(res) {
            if(res.estado == 'success') {
                alert("✅ ¡Éxito! " + res.mensaje + "\n\nEntregar al cliente: " + premio_nombre);
                // Volvemos a consultar para que los puntos bajen en pantalla al instante
                verificar_puntos(cliente_id);
            } else {
                alert("❌ Error: " + res.mensaje);
            }
        }).fail(function() {
            alert("❌ Ocurrió un error de conexión al intentar el canje.");
        });
    }
}

// Alerta de confirmación si intentan cerrar o recargar la pestaña
window.onbeforeunload = function() {
    return "¿Estás seguro de que quieres salir? El proceso de cobro no ha terminado.";
};



</script>

<style>
    /* Suponiendo que tus clases de menú son estas, cámbialas por las de tu layout */
    .main-header, .main-sidebar, .navbar, .sidebar, .nav {
        display: none !important;
    }
    
    /* Ajustar el contenido para que ocupe toda la pantalla al quitar el menú */
    .content-wrapper {
        margin-left: 0 !important;
        padding-top: 0 !important;
    }

    /* Ocultar el botón SALIR que tienes en el código actual */
    a[href="/consolacaja"] {
        display: none !important;
    }
</style>

<br>

    
{!!Form::open(array('url'=>'/registrar','autocomplete'=>'off','method'=>'POST','name'=>'frmcomandas','id'=>'frmcomandas','role'=>'form','files'=>'true'))!!}
{{Form::token()}}

<div class="container-fluid">

    <div class="row">
        <div class="col-lg-5">
            <div class="box">
                <div class="box-header" style="background-color:#E8E8E8;">
                    <strong><font style="font-size:10pt;font-weight:bold;"><center>DATOS DEL COMPROBANTE</center></font></strong>
                    <div class="box-tools pull-right">
                        <div class="form-check">
                            <label class="form-check-label" for="flexCheckDefault">
                                IMPRIMIR
                            </label>
                            <input class="form-check-input" name="imprimir" type="checkbox" value="1" checked="checked">

                        </div>
                    </div>
                </div>
                <div class="box-body">

                    <div class="row">
                        <div class="col-lg-3">
                            <div class="form-group form-group-sm">
                                <label>Comprobante</label>
                                <select class="form-control" name="tdocod" id="tdocod">
                                        @foreach($comprobantes as $comp)
                                                @if($comp->tdocod == $negocio->tdocod_pred)
                                                    <option selected="selected" value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
                                            
                                                @else
                                                    <option  value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
                                            
                                                @endif
                                            
                                        
                                        @endforeach
                                </select>

                            </div>
                        </div>

                        <div class="col-lg-3">
                            <div class="form-group form-group-sm">
                                <label>Estado Pago</label>
                                <select class="form-control" name="estadopago"  id="estadopago">
                                    @foreach($estadopagos as $est_pag)
                                    <option value="{{$est_pag->cre_dia_id}}" data-medio="{{$est_pag->cre_dia_tip}}" data-dias="{{$est_pag->cre_dia_fac}}">{{$est_pag->cre_dia_nom}}</option>
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


                        <div class="col-lg-3">
                            <div class="form-group form-group-sm">
                                <label>X CONSUMO</label>
                                <select class="form-control" name="consumo">
                                    <option value="0">NO</option>
                                    <option value="1">SI</option>
                                </select>

                            </div>
                        </div>

                        <div class="col-lg-1">
                            <div class="form-group form-group-sm">
                                <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgloadcliente" id="imgloadcliente"></center>

                            </div>
                        </div>

                        
                    </div>

                    <div class="row form-group form-group-sm">
                        <div class="col-lg-2 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label>Tipo</label>
                                <select name="tdicod" id="tdicod" class="form-control">
                                    @foreach($documentos as $doc)
                                        @if(!empty($cabecera->tdicod))
                                            @if($doc->tdicod == $cabecera->tdicod)
                                                <option selected="selected"  value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
                                            @else
                                                <option value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
                                            @endif
                                        @else
                                            @if($doc->tdicod == '1')
                                                <option selected="selected"  value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
                                            @else
                                                <option value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
                                            @endif
                                        @endif
                                    
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-lg-4">
                            <div class="form-group">

                                <label>DNI / RUC</label>
                                <div class="input-group input-group-sm">

                                    <input name="clinum" id="clinum" value="@if(!empty($cabecera->ped_num_doc)){{$cabecera->ped_num_doc}} @else 00000000 @endif" class="form-control" onkeypress="if(event.keyCode == 13) buscarclienteruc();">
                                    <input type="hidden" name="clicod" id="clicod"  class="form-control">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-flat" onclick="buscarclienteruc();"><span class="fa fa-search"></span></button>
                                        
                                    </span>
                                </div>
                            </div>
                        </div>




                        <div class="col-lg-6">
                            <div class="form-group">

                                <label>Nombre o Razon Social - Cliente</label>
                                <div class="input-group input-group-sm">

                                    <input name="clinom" id="clinom" value="@if(!empty($cabecera->ped_cli_nom)){{$cabecera->ped_cli_nom}} @else VENTA AL PORTADOR @endif" class="form-control" onkeypress="if(event.keyCode == 13) buscarclientenombre();">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-flat" onclick="buscarclientenombre();"><span class="fa fa-search"></span></button>
                                    </span>
                                </div>
                            </div>

                        </div>
                    </div>
                    <div class="row form-group form-group-sm">

                        <div class="col-lg-4">
                            <div class="form-group">

                                <label>Direcci&oacute;n</label>
                                <div class="input-group input-group-sm">

                                    <input name="clidir" id="clidir" value="@if(!empty($cabecera->ped_dir)) {{$cabecera->ped_dir}} @else -- @endif" class="form-control">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="seleccionardireccion();"><span class="fa fa-search"></span></button>
                                    </span>
                                </div>
                            </div>

                        </div>


                        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
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
                  <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                    <div class="form-group form-group-sm">
                      <label>Tel&eacute;fono</label>
                      <input name="clitel" id="clitel" value="{{$cabecera->ped_tel}}" class="form-control">
                    </div>
                </div>


            </div>

            <div class="row">
                <div class="col-lg-12">
                    <label>Observaciones:</label>
                    <textarea class="form-control" rows="1" name="observaciones">{{$cabecera->ped_obs}}</textarea>
                </div>
            </div>

            <!-- INICIO: ALERTA DE PUNTOS HOLA P -->
            <div class="row" id="div_alerta_puntos" style="display:none; margin-top: 15px;">
                <div class="col-lg-12">
                    <div class="alert alert-info" id="box_alerta_puntos" style="border-radius: 8px;">
                        <h4 style="margin-bottom: 5px;">
                            <i class="fa fa-gift"></i> Puntos Hola P: <b id="lbl_puntos_actuales" style="font-size: 1.3em;">0</b>
                        </h4>
                        <div id="txt_mensaje_puntos" style="font-size: 11pt;"></div>
                        <!-- Aquí aparecerán los premios que el cajero agregue a la orden -->
                        <div id="lista_premios_a_entregar" style="margin-top: 10px;"></div>
                    </div>
                </div>
            </div>
            <!-- FIN: ALERTA DE PUNTOS -->


        </div>

    </div>
</div>


<div class="col-lg-7">
    <div class="box">
        <div class="box-header" style="background-color:#E8E8E8;">
            <font style="font-size:10pt;">
                
                <strong>
                    DETALLE: @if(!empty($dat_pis)) {{$dat_pis->pis_nom}} / @endif @if(!empty($dat_mes)) {{$dat_mes->mes_nom}} @endif
                    @if($cabecera->ped_tip !='Salon') {{strtoupper($cabecera->ped_tip)}} - {{strtoupper($cabecera->ped_cli_nom)}} @endif
                </strong>
            </font>

            <div class="box-tools pull-right">
                <a  href="/cseparadas/{{$cabecera->ped_id}}"><button type="button" class="btn btn-sm btn-primary">Cuentas Separadas</button></a>
            </div>

        </div>
        <div class="box-body">

            <div class="row">

                
                <!-----------------------------------------------------inicio segunda columna----------------------------------------------------------->

                <div class="col-lg-9">
                    <div class="col-lg-12" >
                        <div class="form-group form-group-sm">
                            <table class="table table-hover table-bordered table-condensed">
                                <thead style="background:orange;">
                                    <tr style="text-align:center;font-weight:bold;">
                                        <td colspan="3">PRODUCTOS 
                                            <input type="text" name="txt_bus_pro" id="txt_bus_pro" class="form-control input-lg input-block" placeholder="BUSCAR PRODUCTO"></td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>

                        <div class="col-lg-12" style="margin-top:-20px;margin-bottom:20px;overflow-y:auto;max-height:200px;" id="items_productos">



                        </div>


                        <div class="col-lg-12" id="listar_pedido">
                            <div class="form-group form-group-sm">
                                <table class="table table-striped table-hover table-bordered table-condensed" id="tbl_detalle">
                                    <thead style="background:orange;">
                                        <tr style="text-align:center;font-weight:bold;">
                                            <td colspan="5">
                                                <label id="lbl_pis_mes"> </label>

                                                <select name="mozo" id="mozo" class="form-control"> 
                                                    <option value="">Seleccione Mozo</option>
                                                    @foreach($mozos as $mz)
                                                        <option value="{{$mz->IdUsuario}}" hidden="hidden" {{ $cabecera->mozo == $mz->IdUsuario ? 'selected' : '' }}>
                                                            {{$mz->name}} {{$mz->apeusu}}
                                                        </option>
                                                    @endforeach
                                                </select>


                                            </td>

                                        </tr>
                                        <tr style="text-align:center;font-weight:bold;">
                                            <td hidden="hidden"></td>
                                            <td>PRODUCTO</td>
                                            <td>CANTIDAD</td>
                                            <td>PRECIO</td>
                                            <td>OBSERVACION</td>
                                            <td>ELIMINAR</td>
                                        </tr>
                                    </thead>
                                    <tbody id="items_pedidos">
                                        @foreach($detalle as $det)
                                        @php
                                            // Verificamos si el ítem ya está pagado
                                            $esPagado = (isset($det->pagado) && $det->pagado == 1) ? true : false;
                                            // Si está pagado, pintamos la fila de verdecito claro
                                            $colorFila = $esPagado ? 'background-color: #d4edda;' : '';
                                        @endphp
                                        <tr style="{{ $colorFila }}" data-pagado="{{ $esPagado ? '1' : '0' }}">
                                            <td hidden="hidden" for="id"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="{{$det->IdProducto}}"></td>
                                            
                                            <td>
                                                <input type="text" class="form-control" name="descripcion[]" value="{{$det->descripcion}}" style="display:inline-block; width: {{ $esPagado ? '75%' : '100%' }};" {{ $esPagado ? 'readonly' : '' }}>
                                                @if($esPagado)
                                                    <span class="label label-success pull-right" style="margin-top: 8px; font-size: 11px;">PAGADO</span>
                                                @endif
                                            </td>
                                            
                                            <td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" class="form-control" name="txt_cantidad[]" value="{{number_format($det->ped_det_can-$det->item_facturado,'2','.','')}}" min="1" {{ $esPagado ? 'readonly' : '' }}></td>
                                            <td style="text-align:right;" hidden="hidden">{{$det->ped_det_pre}}</td>
                                            <td style="text-align:right;" ><input type="number" readonly="readonly" class="form-control" onkeyup="calcular_total();" step="any" name="precios[]" id="precios[]" value="{{$det->ped_det_pre}}"></td>
                                            <td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="{{$det->icbper_ind}}"></td>
                                            <td style="text-align:right;"  ><input class="form-control" type="text" name="item_obs[]" value="{{$det->item_obs}}" {{ $esPagado ? 'readonly' : '' }}></td>
                                            
                                            <td style="text-align:center;">
                                                @if(!$esPagado)
                                                    <button type="button" onClick="eliminar_item_registrado(this,{{$det->IdProducto}});" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>

                    <!-----------------------------------------------------fin segunda columna--------------------------------------------------------------->


                    <!-----------------------------------------------------inicio tercer columna----------------------------------------------------------->

                    <div class="col-lg-3" hidden="hidden">
                        @foreach($categorias as $cat)
                        <div class="col-lg-12">
                            <div class="form-group form-group-sm">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-md" onclick="buscar_producto_categoria({{$cat->cat_id}});" style="width:200px;margin-bottom:-10px;background:{{$cat->color}};color:white;font-weight:bold;">{{$cat->cat_nom}}</button>
                                </div>
                            </div>
                        </div>
                        @endforeach

                    </div>
                    <!-----------------------------------------------------fin tercer columna--------------------------------------------------------------->
                </div>

                <!-- ============================================================== -->
                    <!-- INICIO: ZONA DE PAGO (Movida a la derecha) -->
                    <!-- ============================================================== -->
                    <div class="row" style="margin-top: 20px; border-top: 2px dashed #ccc; padding-top: 15px;">
                        
                        <!-- Columna 1: Totales -->
                        <div class="col-lg-3 col-md-3">
                            <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #ffc107; white-space: nowrap;">
                                <p style="margin:0; font-size: 10pt; color: #856404;"><b>Total Mesa:</b> S/ <span id="lbl_total_general">0.00</span></p>
                                <p style="margin:0; font-size: 10pt; color: #155724;"><b>Ya Pagado:</b> S/ <span id="lbl_total_pagado">0.00</span></p>
                                <p style="margin:0; font-size: 11pt; color: #d33; margin-top: 5px;"><b>Por Cobrar:</b> S/ <span id="lbl_total_pendiente">0.00</span></p>
                            </div>
                            <input type="hidden" id="total_pendiente" value="0.00">
                            
                            <!-- Inputs ocultos de ICBPER -->
                            <div class="form-group form-group-sm" style="display:none;" >
                                <input type="number" class="form-control" step="any" readonly="readonly" name="icbper_tot" id="icbper_tot" value="{{$cabecera->icbper_tot}}">
                                <input type="number" class="form-control" step="any" readonly="readonly" name="icbper_val" id="icbper_val" value="{{$cabecera->icbper_val}}">
                            </div>

                            <div class="form-group form-group-sm">
                                <label style="font-size: 9pt;">TOTAL COMPROBANTE S/.</label>
                                <input type="number" class="form-control input-lg" style="height:50px; font-size:18pt; font-weight:bold; color:#000; width:100%;" step="any" readonly="readonly" name="total_venta" id="total_venta" value="{{$cabecera->ped_tot}}">
                            </div>
                        </div>
                        
                        <!-- Columna 2: Medios de Pago -->
                        <div class="col-lg-4 col-md-4">
                            <div class="form-group form-group-sm">
                                <label style="font-size: 9pt;">MEDIOS PAGO</label>
                                <select class="form-control" name="med_pag" id="med_pag">
                                    @foreach($mediospagos as $medpag)
                                    <option value="{{$medpag->id_med_pag}}" data-nom="{{$medpag->nom_med_pag}}"  data-predeterminado="{{$medpag->predeterminado}}">{{$medpag->nom_med_pag}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group form-group-sm">
                                <div class="input-group input-group-sm">
                                    <input name="mon_med_pag" id="mon_med_pag" value="0.00" class="form-control">
                                    <span class="input-group-btn">
                                        <button type="button" class="btn btn-primary btn-flat" onclick="agregar_medio_pago();"><span class="fa fa-plus-square"> Agregar</span></button>
                                    </span>
                                </div>
                            </div>
                            <div class="form-group form-group-sm" style="max-height: 80px; overflow-y: auto;">
                                <table class="table table-responsive table-striped table-hover" id="tbl_med_pag" style="margin-bottom: 0;">
                                    <tbody id="tbody_med_pag"></tbody>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Columna 3: Paga Con y Vuelto -->
                        <div class="col-lg-2 col-md-2" style="padding-left: 5px; padding-right: 5px;">
                            <div class="form-group form-group-sm">
                                <label style="font-size: 9pt;">PAGA CON:</label>
                                <input type="number" step="any" class="form-control" style="height:50px; font-size:18pt; font-weight:bold; width:100%;" id="pagar" name="pagar" value="0.00" onkeyup="calcular_vuelto();">
                            </div>
                            <div class="form-group form-group-sm">
                                <label style="font-size: 9pt;">VUELTO:</label>
                                <input type="text" class="form-control" style="height:50px; font-size:18pt; font-weight:bold; width:100%; color:#d33;" id="vuelto" name="vuelto" value="0.00" readonly="readonly">
                            </div>
                        </div>

                        <!-- Columna 4: Botones -->
                        <div class="col-lg-3 col-md-3">
                            <div class="form-group form-group-sm">
                                <label style="font-size: 9pt;">OPERACIONES</label>
                                <button type="button" id="btnRegistrar" class="btn btn-success btn-block botones" style="height: 50px; font-size: 12pt; white-space: normal; line-height: 1.1; font-weight:bold;">REGISTRAR<br>COBRAR</button>
                            </div>
                            <div class="form-group form-group-sm">
                                <a href="/consolacaja"><button type="button" class="btn btn-danger btn-block botones" style="height: 50px; font-size: 12pt; font-weight:bold;">SALIR</button></a>
                            </div>
                            <center><img style="display:none;" width="60px" height="60px" src="/img/load.gif" name="imgload" id="imgload"></center>
                        </div>

                    </div>
                    <!-- ============================================================== -->
                    <!-- FIN: ZONA DE PAGO -->
                    <!-- ============================================================== -->

                </div>
                
            </div>
            <input type="hidden" name="mes_id" readonly="readonly" id="mes_id" value="{{$cabecera->mes_id}}">            
            <input type="hidden" name="pis_id" readonly="readonly" id="pis_id" value="{{$cabecera->pis_id}}">
            <input type="hidden" name="ped_tip" readonly="readonly" id="ped_tip" value="{{$cabecera->ped_tip}}">
            <input type="hidden" name="ped_id" id="ped_id" readonly="readonly" id="ped_id" value="{{$cabecera->ped_id}}">
            <input type="hidden" name="tipo" id="tipo" readonly="readonly" value="">
            
        </div>
    </div>
</div>

<!--<div class="row">
    <div class="col-lg-12">
        <div class="box">
            <div class="box-header" style="background-color:#E8E8E8;">
                <strong><font style="font-size:10pt;"><center>PAGO</center></font></strong>

            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-lg-2">
                        <div style="background: #fff3cd; padding: 10px; border-radius: 5px; margin-bottom: 15px; border-left: 4px solid #ffc107;">
                            <p style="margin:0; font-size: 10pt; color: #856404;"><b>Total Mesa:</b> S/ <span id="lbl_total_general">0.00</span></p>
                            <p style="margin:0; font-size: 10pt; color: #155724;"><b>Ya Pagado:</b> S/ <span id="lbl_total_pagado">0.00</span></p>
                            <p style="margin:0; font-size: 12pt; color: #d33; margin-top: 5px;"><b>Por Cobrar:</b> S/ <span id="lbl_total_pendiente">0.00</span></p>
                        </div>

                        <input type="hidden" id="total_pendiente" value="0.00">

                        <div class="form-group form-group-sm" style="display:none;" >
                            <label>ICBPER S/.</label>
                            <input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="icbper_tot" id="icbper_tot" value="{{$cabecera->icbper_tot}}">
                            <input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="icbper_val" id="icbper_val" value="{{$cabecera->icbper_val}}">
                        </div>

                        <div class="form-group form-group-sm">
                            <label>TOTAL COMPROBANTE S/.</label>
                            <input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold; color: #000;" step="any" readonly="readonly" name="total_venta" id="total_venta" value="{{$cabecera->ped_tot}}">
                        </div>
                    </div>
                    
                    <div class="col-lg-3">
                        <div class="form-group form-group-sm">
                            <label>MEDIOS PAGO</label>
                            <select class="form-control" name="med_pag" id="med_pag">
                                @foreach($mediospagos as $medpag)
                                <option value="{{$medpag->id_med_pag}}" data-nom="{{$medpag->nom_med_pag}}"  data-predeterminado="{{$medpag->predeterminado}}">{{$medpag->nom_med_pag}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group form-group-sm">
                            <div class="input-group input-group-sm">
                                <input name="mon_med_pag" id="mon_med_pag" value="0.00" class="form-control">
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-primary btn-flat" onclick="agregar_medio_pago();"><span class="fa fa-plus-square"> Agregar Pago</span></button>
                                </span>
                            </div>
                        </div>

                        <div class="form-group form-group-sm">
                            <table class="table table-responsive table-striped table-hover" id="tbl_med_pag">

                                <tbody id="tbody_med_pag">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-lg-2">
                            <div class="form-group form-group-sm">
                                <label>PAGA CON:</label>
                                <input type="number"  step="any" class="form-control" style="height:60px;width:180px;font-size:22pt;font-weight:bold;"  id="pagar" name="pagar" value="0.00" onkeyup="calcular_vuelto();">
                            </div>
                      

                        <div class="form-group form-group-sm">
                            <label>VUELTO</label>
                            <input type="text" class="form-control" style="height:60px;width:180px;font-size:22pt;font-weight:bold;"  id="vuelto" name="vuelto" value="0.00" readonly="readonly">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group form-group-sm">
                            <label>OPERACIONES</label>
                            <button type="button" id="btnRegistrar" class="btn btn-success btn-lg btn-block botones">REGISTRAR - COBRAR</button>
                        </div>

                        <div class="form-group form-group-sm">
                            
                            <a href="/consolacaja"><button type="button" class="btn btn-danger btn-lg btn-block botones">SALIR</button></a>
                        </div>

                        <center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>

                        
                    </div>
                </div>

                <div class="row">
                    
                </div>
            </div>  
        </div>
    </div>
</div>-->





</div>
            <input type="hidden" readonly="readonly" class="form-control" name="_id" id="_id">
            <input type="hidden" readonly="readonly" class="form-control" name="_producto" id="_producto">
            <input type="hidden" readonly="readonly"  class="form-control" name="_precio" id="_precio">
                <input type="hidden" readonly="readonly" class="form-control" name="_icbper" id="_icbper">
            <input type="hidden" readonly="readonly" class="form-control" name="_acompa" id="_acompa">
                    <input type="hidden" readonly="readonly"  class="form-control" name="icbper_val" id="icbper_val" value="{{$empresa->icbper}}">
{!!Form::close()!!}
@endsection
