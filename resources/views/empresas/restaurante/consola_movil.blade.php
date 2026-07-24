@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')

@include('empresas.puntosventas.modalclientes')
@include('empresas.restaurante.modal_unir_mesas')
@include('empresas.restaurante.modal_desunir_mesas')

<style>
    /* Estilos generales para responsividad en móviles */
    body {
        font-size: 0.9em; /* Ligeramente más pequeño para aprovechar espacio */
    }

    /* Contenedor principal de los botones de acción */
    .action-buttons-container {
        display: flex;
        flex-wrap: wrap; /* Permite que los botones se envuelvan */
        justify-content: space-around; /* Distribuye espacio alrededor de los botones */
        padding: 5px;
        margin-bottom: 10px;
        background-color: #f0f0f0; /* Fondo sutil para la sección */
        border-bottom: 1px solid #e0e0e0;
    }

    /* Estilo base para todos los botones de acción superior */
    .btn-top-action {
        width: 100%; /* Ocupa todo el ancho disponible en su celda */
        padding: 8px 5px; /* Padding vertical y horizontal reducido */
        margin: 3px; /* Pequeño margen entre botones */
        font-size: 0.9em; /* Texto más pequeño */
        font-weight: bold;
        border-radius: 5px; /* Bordes redondeados */
        box-shadow: 0 2px 4px rgba(0,0,0,0.1); /* Sombra sutil */
        transition: transform 0.1s ease;
        flex-grow: 1; /* Permite que crezcan para ocupar espacio */
    }

    .btn-top-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.15);
    }

    /* Media queries para adaptar los botones de acción superior */
    @media (min-width: 576px) { /* Para pantallas pequeñas (landscape phones) */
        .btn-top-action {
            width: auto; /* Ancho automático, se ajustan al contenido */
            flex-basis: calc(25% - 6px); /* 4 botones por fila */
            font-size: 0.85em;
        }
    }
    @media (min-width: 768px) { /* Para pantallas medianas (tablets) */
        .btn-top-action {
            padding: 10px 8px;
            font-size: 1em;
            flex-basis: calc(16.66% - 6px); /* 6 botones por fila */
        }
    }

    /* Estilos para los botones SALON/LLEVAR/DELIVERY */
    .type-buttons-container {
        display: flex;
        justify-content: center;
        padding: 5px;
        margin-bottom: 10px;
    }

    .btn-type {
        flex-grow: 1;
        padding: 10px 5px;
        margin: 0 2px; /* Pequeño margen entre ellos */
        font-size: 1em;
        font-weight: bold;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        background-color: #007bff; /* Color azul */
        color: white;
    }
    .btn-type:hover {
        opacity: 0.9;
    }


    /* Estilos para las mesas */
    .mesa-container {
        padding: 5px; /* Reducido para juntar más las mesas */
    }

    .btn-mesa {
        width: 100%;
        height: 50px; /* Altura fija para todas las mesas */
        font-size: 1em; /* Tamaño de fuente para el nombre de la mesa */
        font-weight: bold;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        transition: transform 0.1s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        margin: 3px; /* Pequeño margen para separar visualmente */
    }

    .btn-mesa.ocupado {
        background-color: #E74C3C; /* Rojo */
        color: white;
    }

    .btn-mesa.libre {
        background-color: #52BE80; /* Verde */
        color: white;
    }

    .btn-mesa.cuenta { /* Si tienes un estado "cuenta" (amarillo) */
        background-color: #F4D03F; /* Amarillo */
        color: #333; /* Texto oscuro para contraste */
    }

    .btn-mesa:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.15);
    }

    /* Estilos para los botones de categoría (igual que en caja_tactil_6 pero ajustado para móvil) */
    .category-buttons-container {
        background-color: #E8E8E8;
        padding: 5px; /* Reducido de 15px a 5px */
        display: flex; /* Usar flexbox para mejor alineación */
        flex-wrap: wrap; /* Permitir que los elementos se envuelvan a la siguiente línea */
        justify-content: center; /* Centrar los botones horizontalmente */
        align-items: flex-start;
        gap: 5px; /* Añadido gap para espacio entre elementos flex */
        margin-bottom: 10px; /* Espacio debajo del contenedor de categorías */
    }

    .btn-category {
        width: 100%; /* Ocupar el 100% del ancho de su columna */
        max-width: 180px; /* Limitar el ancho máximo para que no sean demasiado grandes */
        height: 55px; /* Altura fija para todos los botones, ligeramente menor */
        font-size: 0.95em; /* Tamaño de fuente más pequeño */
        font-weight: bold;
        color: white;
        border-radius: 8px; /* Bordes más redondeados */
        box-shadow: 0 4px 6px rgba(0,0,0,0.1); /* Sombra sutil */
        transition: transform 0.2s, box-shadow 0.2s; /* Transición para efectos hover/active */
        display: flex; /* Para centrar el texto vertical y horizontalmente */
        align-items: center;
        justify-content: center;
        text-align: center;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3); /* Sombra de texto para legibilidad */
        margin: 2px; /* Margen para separar visualmente */
    }

    .btn-category:hover {
        transform: translateY(-2px); /* Pequeño efecto de elevación al pasar el mouse */
        box-shadow: 0 6px 10px rgba(0,0,0,0.15); /* Sombra más pronunciada al pasar el mouse */
        opacity: 0.9; /* Pequeña opacidad para indicar interactividad */
    }

    .btn-category:active {
        transform: translateY(1px); /* Efecto de "presionado" al hacer clic */
        box-shadow: 0 2px 3px rgba(0,0,0,0.2);
    }

    /* Estilos para la tabla de productos seleccionados (items_pedidos) */
    #tbl_detalle thead {
        background: orange;
        color: white;
    }
    #tbl_detalle th, #tbl_detalle td {
        padding: 8px; /* Reducir padding de celdas */
        font-size: 0.9em;
    }
    #tbl_detalle input[type="number"],
    #tbl_detalle input[type="text"] {
        padding: 5px; /* Reducir padding de inputs */
        font-size: 0.9em;
        height: auto; /* Ajustar altura automáticamente */
    }
    #tbl_detalle .remove {
        padding: 5px 8px; /* Ajustar tamaño del botón eliminar */
    }


    /* Estilos para la barra de búsqueda de productos */
    .product-search-box {
        padding: 10px;
        background-color: orange; /* Color de fondo */
        color: white;
        font-weight: bold;
        text-align: center;
        border-radius: 5px;
        margin-bottom: 10px;
    }
    .product-search-box input {
        border-radius: 5px;
        padding: 8px;
        font-size: 1em;
        border: none;
    }
    .product-search-box input::placeholder { /* Para el placeholder */
        color: #eee;
    }

    /* Contenedor de la lista de productos (items_productos) */
    #items_productos {
        overflow-y: auto;
        max-height: 350px; /* Ajustar altura máxima para que no ocupe demasiado espacio */
        margin-top: 0; /* Eliminar margen superior */
        padding: 5px; /* Pequeño padding interno */
    }

    /* Estilos para los cuadros de productos individuales dentro de #items_productos */
    .product-item-custom {
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 8px;
        margin-bottom: 10px;
        background-color: #fff;
        height: 120px; /* Altura ajustada para móvil */
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        align-items: center;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        transition: transform 0.1s ease;
        cursor: pointer;
    }
    .product-item-custom:hover {
        transform: translateY(-1px);
        box-shadow: 0 3px 6px rgba(0,0,0,0.1);
    }
    .product-item-custom .info-box-icon {
        width: 50px; height: 50px; /* Icono/imagen más pequeño */
        margin-bottom: 5px;
    }
    .product-item-custom .product-name-custom {
        font-size: 0.8em; /* Nombre más pequeño */
        line-height: 1.1;
        height: auto;
        white-space: normal;
    }
    .product-item-custom .product-price-custom {
        font-size: 1em; /* Precio ajustado */
        font-weight: bold;
    }
    .product-item-custom .product-stock-custom {
        font-size: 0.7em; /* Stock más pequeño */
        color: #999;
    }

    /* Columna izquierda de la interfaz (controles, lista de productos, pago) */
    .left-panel {
        padding-right: 5px; /* Reducir padding horizontal */
        padding-left: 5px;
    }
    /* Columna derecha de la interfaz (categorías, productos) */
    .right-panel {
        padding-left: 5px; /* Reducir padding horizontal */
        padding-right: 5px;
    }
    .box-body {
        padding: 5px; /* Reducir padding de los box-body */
    }
    .box-header {
        padding: 10px; /* Reducir padding de los box-header */
    }
    .form-group-sm {
        margin-bottom: 5px; /* Reducir margen inferior de los form-groups */
    }
    .form-control {
        height: auto;
        padding: 8px 12px;
    }

    /* Media queries para responsividad (botones de categoría) */
    @media (max-width: 767px) { /* Para pantallas pequeñas (móviles) */
        .btn-category {
            height: 50px;
            font-size: 1em;
            max-width: 150px;
            margin: 0; /* Aseguramos que no haya margen en móviles */
        }
        .category-button-wrapper {
            padding-left: 1px !important;
            padding-right: 1px !important;
        }
        .category-buttons-container {
            padding: 1px; /* Reducir aún más el padding del contenedor en móviles */
        }
    }
</style>

@if(!empty($cat_pred))
    <script type="text/javascript">
        $(document).ready(function(){
            var tipo = $('#tipo').val();
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

            function buscar_producto_categoria_llevar(id){
                var producto=0;
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: "/buscarcartallevar/"+producto+"/"+id,
                }).done(function(respuesta){                
                    $("#items_productos").html(respuesta.vista);
                });
            }
            buscar_producto_categoria({{$cat_pred->cat_id}});       
        });
    </script>
    @php
     $cat_ = $cat_pred->cat_id;
    @endphp
@endif

<script type="text/javascript">
    $(document).ready(function(){
        setTimeout(refrescar, 5000);
        limpiarpedido();

        $("#btnSalon").click(function(){
            buscar_producto_categoria({{$cat_}});
            $("#btnCobrar").show(); 
            $("#btnCambiar").show();  
        });

        $("#btnLlevar").click(function(){
            buscar_producto_categoria_llevar({{$cat_}});            
            $("#btnCobrar").hide();   
            $("#btnCambiar").hide();                 
        });

        $("#btnDelivery").click(function(){
            buscar_producto_categoria_llevar({{$cat_}});
            $("#btnCobrar").hide();
            $("#btnCambiar").hide();                       
        });

        $("#btnPrecuenta").click(function(){
            var ped_id = $("#ped_id").val();
            $("<iframe>")                             
            .hide()                              
            .attr("src", "/imprimircuenta/"+ped_id) 
            .appendTo("body");                   
        });

        $("#btnCobrar").click(function(){
            var ped_id = $("#ped_id").val();
            var mes_id = $("#mes_id").val();
            if (!ped_id) {
                alert('Por favor, selecciona una mesa antes de cobrar.');
                return false;
            }
            window.location.href = "/cobrarmesa/" + ped_id;
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
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: '/buscarmesasdesocupadas',
                }).done(function(respuesta){
                    $("#modal-cambiar-mesa").modal("show"); 
                    $("#mesas_desocupadas").html(respuesta.vista);  
                });
            }
        });

        $("#btnUnirMesas").click(function(){
            var mesa_actual = $("#mes_id").val();
            var mesa_nom_actual = $("#mes_nom").val();
            var ped_id_actual = $("#ped_id").val();
            if(mesa_actual===""){
                alert('Elegir una mesa');
            }else{
                $("#mes_id_act_unir").val(mesa_actual);
                $("#ped_id_act_unir").val(ped_id_actual);
                $("#mes_act_unir").val(mesa_nom_actual);
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: '/buscarmesasdesocupadasunir/'+mesa_actual,
                }).done(function(respuesta){
                    $("#modal-unir-mesas").modal("show");   
                    $("#mesas_desocupadas_unir").html(respuesta.vista); 
                });
            }
        });

        $("#btnDesunirMesas").click(function(){
            var mesa_actual = $("#mes_id").val();
            var mesa_nom_actual = $("#mes_nom").val();
            var ped_id_actual = $("#ped_id").val();
            if(mesa_actual===""){
                alert('Elegir una mesa');
            }else{
                $("#mes_id_act_desunir").val(mesa_actual);
                $("#ped_id_act_desunir").val(ped_id_actual);
                $("#mes_act_desunir").val(mesa_nom_actual);
                $.ajax({
                    type: "GET",
                    dataType: 'json',
                    url: '/buscarmesasdesunir/'+mesa_actual,
                }).done(function(respuesta){
                    $("#modal-desunir-mesas").modal("show");    
                    $("#mesas_desocupadas_desunir").html(respuesta.vista);  
                });
            }
        });

        $("#btnImpComanda").click(function(){
            var id = $("#ped_id").val();
            if(id==""){
                alert('ELEGIR UN PEDIDO');
            }else{
                window.location.href = "/imprimircomandatotal/"+id;
            }
        });

        $("#btnComanda").click(function(){
            var formulario = $("#frmcomandas").serializeArray();
            var accion = $("#accion").val();
            var tipo_comanda = $("#tipo").val();
            var ped_id = $("#ped_id").val();
            $("#btnAcciones").hide();
            $("#imgload").show();
            if(accion=='0'){
                $.ajax({
                    type: "POST",
                    dataType: 'json',
                    url: '/registrarcomanda',
                    data: formulario,
                }).done(function(respuesta){
                    if(respuesta.estado=='error'){
                        alert(respuesta.mensaje);
                        $("#imgload").hide();
                        $("#btnAcciones").show();
                    }else{
                        if(tipo_comanda=='3' || tipo_comanda=='2'){
                            window.location.href = "/cobrarmesa/"+respuesta.ped_id; 
                        }else{
                            window.location.href = "/consola";
                        }
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
        var icbper_ = $('#_icbper').val();
        var precio_ = $('#_precio').val();
        var acompa_ = $('#_acompa').val();
        var stock_ = $('#_stock').val();

        var validar = checkId(id_);
        if (validar==true){
            $("#tbl_detalle > tbody > tr").each(function(){
                if($(this).find("td:eq(0) > input").val() == id_){
                    var current_qty_input = $(this).find("td:eq(2) > input");
                    var current_quantity = parseFloat(current_qty_input.val());
                    var new_quantity = current_quantity + 1;
                    if(parseFloat(stock_) !== null && parseFloat(stock_) !== '' && parseFloat(stock_) >= 0 && new_quantity > parseFloat(stock_)){ 
                        alert('No hay suficiente stock para este producto. Stock disponible: ' + stock_);
                        current_qty_input.val(stock_);
                        return false;
                    }
                    current_qty_input.val(new_quantity);
                }
            });
        }else{
            var permitir_venta_sin_stock = {{ $negocio->ven_sin_sto ?? 0 }};
            if(parseFloat(stock_) <= 0 && permitir_venta_sin_stock == 0){
                alert('No hay stock disponible para este producto y la venta sin stock no está permitida.');
                return false;
            }
            $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text"  class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
            '<td><input type="hidden" class="form-control" name="descripcion[]" value="'+producto_+' - '+acompa_+'">'+producto_+' - '+acompa_+'</td>'+
            '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1" ' + (stock_ !== null && stock_ !== '' && parseFloat(stock_) >= 0 ? 'max="'+stock_+'"' : '') + '></td>'+
            '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
            '<td style="text-align:right;" ><input  class="form-control" type="number" step="0.01" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
            '<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
            '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
            '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_stock[]" value="'+stock_+'"></td>'+
            '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
        }
        $('#_id').val('');
        $('#_producto').val('');
        $('#_precio').val('');
        $('#_acompa').val('');
        $('#_icbper').val('');
        $('#_stock').val('');
        $(".cantidad").focus();
        $(".cantidad").select();
        calcular_total();
    }

    function elegir_acompanamiento(id,producto,precio,cat_sig,acom,icbper,stock){
        var cat_acom = $('#cat_acom').val();
        if(cat_acom=='1'){
            $('#_acompa').val(producto);
            $('#_stock').val(stock);
            agregar_item();
            $('#_id').val('');
            $('#_producto').val('');
            $('#_precio').val('');
            $('#_icbper').val('');
            $('#_acompa').val('');
            $('#_stock').val('');
        }else{
            $('#_id').val(id);
            $('#_producto').val(producto);
            $('#_precio').val(precio);
            $('#_icbper').val(icbper);
            $('#_stock').val(stock);
            $("#btnAgregarItem").hide();    
        }
        if(cat_sig !=''){
            buscar_producto_categoria(cat_sig);
        }else{
            var id_ = $('#_id').val();
            var producto_ = $('#_producto').val();
            var precio_ = $('#_precio').val();
            var icbper_ = $('#_icbper').val();
            var stock_ = $('#_stock').val();
            var validar = checkId(id_);
            if (validar==true){
                $("#tbl_detalle > tbody > tr").each(function(){
                    if($(this).find("td:eq(0) > input").val() == id_){
                        var current_qty_input = $(this).find("td:eq(2) > input");
                        var current_quantity = parseFloat(current_qty_input.val());
                        var new_quantity = current_quantity + 1;
                        if(parseFloat(stock_) !== null && parseFloat(stock_) !== '' && parseFloat(stock_) >= 0 && new_quantity > parseFloat(stock_)){ 
                            alert('No hay suficiente stock para este producto. Stock disponible: ' + stock_);
                            current_qty_input.val(stock_);
                            return false;
                        }
                        current_qty_input.val(new_quantity);
                    }
                });
            }else{
                var permitir_venta_sin_stock = {{ $negocio->ven_sin_sto ?? 0 }};
                 if(parseFloat(stock_) <= 0 && permitir_venta_sin_stock == 0){
                    alert('No hay stock disponible para este producto y la venta sin stock no está permitida.');
                    return false;
                }
                $('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text"  class="form-control" name="txt_id_producto[]" value="'+id_+'"></td>'+
                '<td style="font-weight:bold;"><input type="hidden" class="form-control" name="descripcion[]"   value="'+producto_+'">'+producto_+'</td>'+
                '<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="cantidad form-control" name="txt_cantidad[]" value="1" min="1" ' + (stock_ !== null && stock_ !== '' && parseFloat(stock_) >= 0 ? 'max="'+stock_+'"' : '') + '></td>'+
                '<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
                '<td style="text-align:right;" ><input class="form-control" type="number" step="0.01" onkeyup="calcular_total();" onChange="calcular_total();" readonly="readonly" name="precios[]" value="'+precio_+'"></td>'+
                '<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
                '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
                '<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_stock[]" value="'+stock_+'"></td>'+
                '<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
            }
            $('#_id').val('');
            $('#_producto').val('');
            $('#_icbper').val('');
            $('#_precio').val('');
            $('#_acompa').val('');
            $('#_stock').val('');
        }
        calcular_total();
    }

    function checkId(id) {
        var contar=0;
        $("#tbl_detalle > tbody > tr").each(function(){
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

    window.eliminar_item_registrado = function(btn, itemId) {
        var pedidoId = $("#ped_id").val();
        if (!pedidoId) {
            alert('No se pudo obtener el ID del pedido. Por favor, recargue la página.');
            return;
        }
        $('#confirmDeleteModal').data('action-type', 'item');
        $('#confirmDeleteModal').data('item-id', itemId);
        $('#confirmDeleteModal').data('pedido-id', pedidoId);
        $('#confirmDeleteModal').data('button-element', btn);
        $('#confirmDeleteModal').modal('show');
    };

    window.eliminar_pedido = function() {
        var pedidoId = $("#ped_id").val();
        if (pedidoId === "") {
            alert('ELEGIR PEDIDO A ELIMINAR');
            return;
        }
        $('#confirmDeleteModal').data('action-type', 'pedido');
        $('#confirmDeleteModal').data('pedido-id', pedidoId);
        $('#confirmDeleteModal').modal('show');
    };

    function eliminar_item(btn) {
        var row = btn.parentNode.parentNode;
        row.parentNode.removeChild(row);
        calcular_total();
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
            if(respuesta.contar==0){
                window.location.href = "/consola";
            }
        });
    }

    function eliminar_pedido(item){
        var pedido = $("#ped_id").val();
        if(pedido==''){
            alert('ELEGIR PEDIDO A ELIMINAR');
        }else{
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/eliminarpedido/"+pedido,
            }).done(function(respuesta){
                alert(respuesta.mensaje);
                window.location.href = "/consola";
            });
        }
    }

    function refrescar(){
        var piso = $("#piso").val();
        var tipo = $("#tipo").val();
        if(tipo=='1'){
            $.ajax({
                type: "GET",
                dataType: 'json',
                url: "/buscarmesas/"+piso,
            }).done(function(respuesta){
                $("#listar_mesas").html(respuesta.vista);
            }); 
        }
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
            }
            calcular_total();
        });
    }

    function limpiarpedido(){
        $("#mes_id").val("");
        $("#pis_id").val("");
        $("#accion").val("0");
        $("#ped_num_doc").val("00000000");
        $("#ped_cli_nom").val("");
        $("#ped_dir").val("");
        $("#ped_id").val("");
        $("#mes_nom").val("");
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

    function buscarclientenombre(){
        id = $("#ped_cli_nom").val();
        $("#modal-lista-clientes").modal("show");
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarclientenombre/"+id,
        }).done(function(respuesta){
            $("#clientes").html(respuesta.vista);
        });
    }

    function buscarclientetelefono(){
        id = $("#ped_tel").val();
        $("#modal-lista-clientes").modal("show");
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarclientenombre/"+id,
        }).done(function(respuesta){
            $("#clientes").html(respuesta.vista);
        });
    }

    function agregarcliente(clicod,clinum,clinom,clidir,tdicod,clicor,telefono){
        $('#ped_cli_nom').val(clinom);
        $('#ped_num_doc').val(clinum);
        $('#ped_dir').val(clidir);
        $('#ped_tel').val(telefono);
        $("#tdicod").val(tdicod).attr('selected', 'selected');
        $("#modal-lista-clientes").modal("hide");
    }

    function buscar_producto_categoria_llevar(id){
        var producto=0;
        $.ajax({
            type: "GET",
            dataType: 'json',
            url: "/buscarcartallevar/"+producto+"/"+id,
        }).done(function(respuesta){
            $("#items_productos").html(respuesta.vista);
        });
    }

    function calcular_total(){
        var total = 0;
        var total_icbper = 0;
        var icbper_val = $("#icbper_val").val();
        $("#tbl_detalle tbody tr").each(function(){
            if($(this).find("td:eq(6)> input").val()==1){
                 total_icbper = total_icbper + parseFloat($(this).find("td:eq(2)> input").val()*icbper_val);
            }
            total = total + parseFloat($(this).find("td:eq(2)> input").val()*$(this).find("td:eq(4)>input").val());
        });
        total = parseFloat(total) + parseFloat(total_icbper);
        $('#icbper_tot').val(total_icbper.toFixed(2));
        $('#total_venta').val(total.toFixed(2));
        calcular_vuelto();
    }

    function calcular_vuelto(){
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
        if(total_venta==0){
            $("#pagar").val('0.00');
            $("#vuelto").val('0.00');
        }
    }

    function executeDeletion() {
        var password = $('#admin_password').val();
        var actionType = $('#confirmDeleteModal').data('action-type');
        var pedidoId = $('#confirmDeleteModal').data('pedido-id');
        var itemId = $('#confirmDeleteModal').data('item-id');
        var btnElement = $('#confirmDeleteModal').data('button-element');

        if (!password) {
            alert('Por favor, ingresa la contraseña.');
            return;
        }
        $('#confirmDeleteModal').modal('hide');
        $('#admin_password').val('');
        $('#password_error').hide();

        $.ajax({
            type: "POST",
            dataType: 'json',
            url: "/validar-admin-password",
            data: {
                _token: "{{ csrf_token() }}",
                password: password
            },
            success: function(response) {
                if (response.success) {
                    if (actionType === 'item') {
                        $.ajax({
                            type: "GET",
                            dataType: 'json',
                            url: "/eliminaritem/" + itemId + "/" + pedidoId,
                            success: function(respuesta) {
                                alert(respuesta.mensaje);
                                if (respuesta.action === 'reload_page') {
                                    window.location.href = "/consola";
                                } else if (respuesta.action === 'reload_partial') {
                                    if (btnElement) {
                                        var row = btnElement.parentNode.parentNode;
                                        row.parentNode.removeChild(row);
                                    }
                                    calcular_total();
                                }
                            },
                            error: function(xhr) {
                                alert('Error al eliminar el ítem.');
                                console.log(xhr.responseText);
                            }
                        });
                    } else if (actionType === 'pedido') {
                        $.ajax({
                            type: "GET",
                            dataType: 'json',
                            url: "/eliminarpedido/" + pedidoId,
                            success: function(respuesta) {
                                alert(respuesta.mensaje);
                                window.location.href = "/consola";
                            },
                            error: function(xhr) {
                                alert('Error al eliminar el pedido.');
                                console.log(xhr.responseText);
                            }
                        });
                    }
                } else {
                    $('#admin_password').val('');
                    $('#password_error').text(response.message).show();
                    $('#confirmDeleteModal').modal('show');
                }
            },
            error: function(xhr) {
                $('#admin_password').val('');
                $('#password_error').text('Error en la comunicación con el servidor.').show();
                $('#confirmDeleteModal').modal('show');
                console.log(xhr.responseText);
            }
        });
    }
</script>

<div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmDeleteModalLabel">Confirmar Eliminación</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <p>Esta operación requiere autorización de un administrador.</p>
        <div class="form-group">
          <label for="admin_password">Contraseña de Administrador:</label>
          <input type="password" class="form-control" id="admin_password" name="admin_password">
          <small id="password_error" class="form-text text-danger" style="display:none;"></small>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-danger" onclick="executeDeletion()">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<br>


<div class="container-fluid">
    {!!Form::open(array('url'=>'/registrar','autocomplete'=>'off','method'=>'POST','name'=>'frmcomandas','id'=>'frmcomandas','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
    <div class="box">
        <div class="box-body">
            <div class="row action-buttons-container">
                
                <div class="col-xs-3"><button type="button" class="btn btn-success btn-top-action" name="btnComanda" id="btnComanda"><strong>C.</strong></button></div>
                
                <div class="col-xs-3"><button type="button" class="btn btn-info btn-top-action" name="btnPrecuenta" id="btnPrecuenta"><strong>P.C.</strong></button></div>

                <div class="col-xs-3"><button type="button" class="btn btn-primary btn-top-action" name="btnCambiar" id="btnCambiar"><strong>C.M.</strong></button></div>
                
                <div class="col-xs-3"><button type="button" class="btn btn-primary btn-top-action" name="btnUnirMesas" id="btnUnirMesas"><strong>U.M.</strong></button></div>

                <div class="col-xs-4"><button type="button" id="btnSalon" class="btn btn-primary btn-type"><strong>SALON</strong></button></div>
                <div class="col-xs-4"><button type="button" id="btnLlevar" class="btn btn-primary btn-type"><strong>LLEVAR</strong></button></div>
                <div class="col-xs-4"><button type="button" id="btnDelivery" class="btn btn-primary btn-type"><strong>DELIVERY</strong></button></div>
                
            </div>

            <!--<div class="row type-buttons-container">
                {{-- Botones SALON/LLEVAR/DELIVERY --}}
                
            </div>-->
            
            <div class="row">
                <div class="col-xs-12">
                    <div class="form-group form-group-sm">
                        <select class="form-control" name="piso" id="piso">
                            @foreach($pisos as $piso)
                            <option value="{{$piso->pis_id}}">{{$piso->pis_nom}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div id="listar_mesas" class="col-xs-12">
                    {{-- Esta sección se llenará con JavaScript --}}
                    <?php $i=0; ?>
                    @if(!empty($mesas))
                    @foreach($mesas as $mesa_item) {{-- Renombrado a $mesa_item para evitar conflicto con $mesas que es la colección --}}
                    <?php $i=$i+1; ?>
                    <div class="col-xs-3 mesa-container"> {{-- col-xs-3 para 4 mesas por fila --}}
                        <button type="button" class="btn btn-mesa @if($mesa_item->mes_est=='Ocupado') ocupado @elseif($mesa_item->mes_est=='Libre') libre @else cuenta @endif" onclick="elegir_mesa('{{$mesa_item->mes_nom}}','{{$mesa_item->mes_id}}','{{$mesa_item->mes_nom}}')">
                            <strong>{{$mesa_item->mes_nom}}</strong>
                        </button>
                    </div>
                    @endforeach
                    @endif
                </div>
            </div>

            <div class="row category-buttons-container">
                {{-- Botones de categoría --}}
                @foreach($categorias as $cat)
                <div class="col-xs-3 category-button-wrapper"> {{-- col-xs-3 para 4 categorías por fila --}}
                    <button type="button" class="btn btn-category" onclick="buscar_producto_categoria({{$cat->cat_id}});" style="background-color:{{$cat->color}}; border-color:{{$cat->color}};">
                        {{$cat->cat_nom}}
                    </button>
                </div>
                @endforeach
            </div>

            <div class="row">
                <div class="col-xs-12">
                    <div class="product-search-box">
                        PRODUCTOS
                        <input type="text" name="txt_bus_pro" id="txt_bus_pro" class="form-control" placeholder="BUSCAR PRODUCTO">
                    </div>
                </div>

                <div id="items_productos" class="col-xs-12">
                    {{-- Aquí se inyectan los productos con JS --}}
                </div>
            </div>

            <div class="row">
                <div class="col-xs-12" id="listar_pedido">
                    <div class="form-group form-group-sm">
                        <table class="table table-striped table-hover table-bordered table-condensed" id="tbl_detalle">
                            <thead style="background:orange;">
                                <tr style="text-align:center;font-weight:bold;">
                                    <td colspan="5">
                                        <label id="lbl_pis_mes"> </label>
                                        <select name="mozo" id="mozo" class="form-control input-block"> 
                                            <option></option>
                                            @foreach($mozos as $mz)
                                                @if($mz->IdUsuario == Auth::user()->IdUsuario)
                                                    <option selected="selected" value="{{$mz->IdUsuario}}">{{$mz->name}} {{$mz->apeusu}}</option>
                                                @else
                                                    <option value="{{$mz->IdUsuario}}">{{$mz->name}} {{$mz->apeusu}}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                                <tr style="text-align:center;font-weight:bold;">
                                    <td hidden="hidden"></td>
                                    <td>PRODUCTO</td>
                                    <td>CANTIDAD</td>
                                    <td>PRECIO</td>
                                    <td>OBSERVACIONES</td>
                                    <td>ELIMINAR</td>
                                </tr>
                            </thead>
                            <tbody id="items_pedidos">
                                <tr>
                                    <td hidden="hidden"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-xs-6">
                    <div class="form-group form-group-sm">
                        <label>TOTAL S/.</label>
                        <input type="number" class="form-control" style="font-size:1.5em;font-weight:bold;" step="any" readonly="readonly" name="total_venta" id="total_venta" value="0.00">
                    </div>
                </div>
                <div class="col-xs-6">
                    <div class="form-group form-group-sm">
                        <label>PAGA CON: </label>
                        <input type="number" step="any" class="form-control" style="font-size:1.5em;font-weight:bold;" id="pagar" name="pagar" value="0.00" onkeyup="calcular_vuelto();">
                    </div>
                </div>
                <div class="col-xs-12">
                    <div class="form-group form-group-sm">
                        <label>VUELTO </label>
                        <input type="text" class="form-control" style="font-size:1.5em;font-weight:bold;" id="vuelto" name="vuelto" value="0.00" readonly="readonly">
                    </div>
                </div>
            </div>
            
            {{-- Campos ocultos del formulario --}}
            <input type="hidden" name="mes_id" readonly="readonly" id="mes_id" value="">
            <input type="hidden" name="mes_nom" readonly="readonly" id="mes_nom" value="">
            <input type="hidden" name="pis_id" readonly="readonly" id="pis_id" value="">
            <input type="hidden" name="accion" readonly="readonly" id="accion" value="0">
            <input type="hidden" name="ped_id" id="ped_id" readonly="readonly" value="">
            <input type="hidden" name="tipo" id="tipo" readonly="readonly" value="">
            <input hidden="hidden" type="date" name="ped_fec" id="ped_fec" value="{{now()->format('Y-m-d')}}">
            <input type="hidden" readonly="readonly" class="form-control" name="_id" id="_id">
            <input type="hidden" readonly="readonly" class="form-control" name="_producto" id="_producto">
            <input type="hidden" readonly="readonly" class="form-control" name="_icbper" id="_icbper">
            <input type="hidden" readonly="readonly" class="form-control" name="_precio" id="_precio">
            <input type="hidden" readonly="readonly" class="form-control" name="_acompa" id="_acompa">
            <input type="hidden" readonly="readonly" class="form-control" name="_stock" id="_stock">
            <input type="hidden" readonly="readonly" class="form-control" name="icbper_val" id="icbper_val" value="{{$empresa->icbper}}">

        </div>
    </div>
    {!!Form::close()!!}
</div>

@endsection