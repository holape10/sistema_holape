@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')
@include('empresas.puntosventas.modaldirecciones')
@include('empresas.puntosventas.modalclientes')


@if(!empty($cat_pred))

{{-- ESTE BLOQUE DE CSS PERSONALIZADO ES IMPORTANTE, LO MANTENEMOS --}}
<style>
	.product-stock-custom {
        display: block; font-size: 0.9em; color: #666; margin-top: 3px;
    }
    @media (max-width: 991px) { .product-stock-custom { font-size: 0.8em; } }
    @media (max-width: 767px) { .product-stock-custom { font-size: 0.75em; } }
    .product-item-custom {
        border: 1px solid #ddd; border-radius: 10px; padding: 10px; margin-bottom: 15px;
        background-color: #fcfcfc; height: 180px; display: flex; flex-direction: column;
        justify-content: space-between; align-items: center; text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.08); transition: transform 0.2s ease, box-shadow 0.2s ease;
        cursor: pointer;
    }
    .product-item-custom:hover {
        transform: translateY(-3px); box-shadow: 0 8px 16px rgba(0,0,0,0.15); border-color: #a0d4e7;
    }
    .product-image-container {
        flex-grow: 1; display: flex; align-items: center; justify-content: center;
        width: 100%; margin-bottom: 8px;
    }
    .info-box-icon {
        background-color: transparent !important; display: flex; align-items: center;
        justify-content: center; width: 90px; height: 90px; background-position: center !important;
        background-repeat: no-repeat !important; background-size: contain !important;
    }
    .product-name-custom {
        font-weight: bold; display: block; white-space: normal; word-wrap: break-word;
        height: auto; overflow: hidden; text-overflow: ellipsis; font-size: 1.0em;
        color: #333; line-height: 1.2; margin-bottom: 5px;
    }
    .product-price-custom {
        display: block; font-size: 1.2em; font-weight: bold; color: #007bff;
    }
    .product-details-container { padding-top: 5px; width: 100%; }
    @media (max-width: 991px) {
        .product-item-custom { height: 160px; padding: 8px; }
        .info-box-icon { width: 70px; height: 70px; }
        .product-name-custom { font-size: 0.9em; line-height: 1.1; }
        .product-price-custom { font-size: 1.1em; }
    }
    @media (max-width: 767px) {
        .product-item-custom { height: 140px; padding: 5px; margin-bottom: 10px; }
        .info-box-icon { width: 60px; height: 60px; }
        .product-name-custom { font-size: 0.85em; height: auto; }
        .product-price-custom { font-size: 1.0em; }
    }
    .category-buttons-container {
        background-color: #E8E8E8; padding: 10px; display: flex;
        flex-wrap: wrap; justify-content: flex-start; align-items: flex-start;
        gap: 8px; margin-bottom: 15px; border-radius: 8px;
    }
    .btn-category {
        font-weight: bold; color: white; border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.2s, box-shadow 0.2s;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
    }
    .btn-category:hover {
        transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.15); opacity: 0.95;
    }
</style>

{{-- MANTENEMOS TODO EL JAVASCRIPT ORIGINAL --}}
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
		var comprobante = $("#comprobante").val();
		var documento = $("#documento").val();
		$("#btnPrint").printPage({
			url: "/voucher/"+comprobante,
			attr: "href",
			messageBox:false
		})
		limpiarpedido();
		$("#btnRegistrar").on("click", function(event) { //Añadido event
			if ($('#items_pedidos >tbody >tr').length == 0){
				// Se puede mejorar esto con una alerta más moderna (ej. SweetAlert)
                alert('Debe agregar al menos un producto a la venta.');
				event.preventDefault(); 
                return; // Detener ejecución
			}
			var formulario = $("#frmcomandas").serializeArray();
			$("#imgload").show();
			$(".botones").hide();
			$.ajax({
				type: "POST",
				dataType: 'json',
				url: '/registrarventa',
				data: formulario,
			}).done(function(respuesta){
				if(respuesta.estado =='error'){
					alert(respuesta.mensaje);
					$("#imgload").hide();
					$(".botones").show();
				}else{
					window.location.href = "/ventacaja6/"+respuesta.codfact;
				}
			});
		});
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
			$("<iframe>").hide().attr("src", "/precuenta/"+ped_id).appendTo("body");                   
		});
		$("#btnCobrar").click(function(){
			var ped_id = $("#ped_id").val();
			window.location.href = "/cobrarmesa/"+ped_id;               
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
		$("#btnImpComanda").click(function(){
			var id = $("#ped_id").val();
			if(id==""){
				alert('ELEGIR UN PEDIDO');
			}else{
				$.ajax({
					type: "GET",
					dataType: 'json',
					url: '/imprimircomandatotal/'+id,
				}).done(function(respuesta){});
			}
		});
		$("#btnComanda").click(function(){
			var formulario = $("#frmcomandas").serializeArray();
			var accion = $("#accion").val();
			var tipo_comanda = $("#tipo").val();
			var ped_id = $("#ped_id").val();
			$("#imgloadcliente").show();
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
						if(tipo_comanda=='3'){
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
			$.ajax({type: "GET",dataType: 'json',url: '/panelsalon'}).done(function(respuesta){
				$("#tipo").val('1'); $("#salon").html(respuesta.vista); limpiarpedido();	
			});
		});
		$("#btnDelivery").click(function(){
			$.ajax({type: "GET",dataType: 'json',url: '/paneldelivery'}).done(function(respuesta){
				$("#tipo").val('2'); $("#salon").html(respuesta.vista);	limpiarpedido();	
			});
		});
		$("#btnLlevar").click(function(){
			$.ajax({type: "GET",dataType: 'json',url: '/panelllevar'}).done(function(respuesta){
				$("#tipo").val('3'); $("#salon").html(respuesta.vista);	limpiarpedido();	
			});
		});
		$("#txt_bus_pro").keyup(function(){
			var producto = $(this).val();
			var contarcarateres = $(this).val().length;
			if(contarcarateres > 0){
				// Llama a la función que muestra productos con imágenes
				buscar_producto_categoria_img(producto);
			} else {
                // Si el campo está vacío, carga la categoría predeterminada
                buscar_producto_categoria_img({{$cat_}});
            }
		});
		$("#piso").change(function(){
			$("#mes_id").val("");
			$("#pis_id").val("");
			var piso = $(this).val();
			$.ajax({type: "GET",dataType: 'json',url: "/buscarmesas/"+piso}).done(function(respuesta){
				$("#listar_mesas").html(respuesta.vista);
			});
		});
	});
    function buscar_producto_categoria(id){
        var producto=0;
        $.ajax({type: "GET", dataType: 'json', url: "/buscarcarta/"+producto+"/"+id}).done(function(respuesta){
            $("#items_productos_img").html(respuesta.vista); // Apuntar al div de imágenes
        });
    }
    function buscar_producto_categoria_img(id_or_search){
        var producto = 0;
        var cat_id = 0;

        if (isNaN(id_or_search)) { // Si no es un número, es una búsqueda por texto
            producto = id_or_search;
        } else { // Si es un número, es un id de categoría
            cat_id = id_or_search;
        }
        
        $.ajax({type: "GET", dataType: 'json', url: "/buscarcartaimg/"+producto+"/"+cat_id}).done(function(respuesta){
            $("#items_productos_img").html(respuesta.vista);
        });
    }
	function agregar_medio_pago(){
		var med_pag = $("#med_pag").val(); var mon_med_pag = $("#mon_med_pag").val();
		var nom_med_pag = $("#med_pag").find(':selected').attr('data-nom'); var predeterminado = $("#med_pag").find(':selected').attr('data-predeterminado');
		var validar = checkIdMedPag(med_pag);
		if (validar==true){ alert('EL MEDIO DE PAGO YA SE ENCUENTRA AGREGADO');
		}else{
			$('#tbl_med_pag tbody').append('<tr><td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="id_med_pag[]" value="'+med_pag+'"></td><td hidden="hidden"><input type="number" class="form-control" name="mon_med_pag[]" value="'+mon_med_pag+'"></td><td hidden="hidden"><input type="text" class="form-control" name="predeterminado[]" value="'+predeterminado+'"></td><td class="align-middle">'+nom_med_pag+' S/ '+parseFloat(mon_med_pag).toFixed(2)+'</td><td><button type="button" onClick="ElimMedPag(this);" class="btn btn-danger btn-sm"><i class="fa fa-times"></i></button></td></tr>');
		}
		$("#mon_med_pag").val('0.00');
		calcular_vuelto();
	}
	function calcular_vuelto(){
		var total_venta = parseFloat($("#total_venta").val()) || 0;
		var pagar = parseFloat($("#pagar").val()) || 0;
		var vuelto = 0; var monto_om = 0; var monto = 0;
		$("#tbl_med_pag tbody tr").each(function(){
			if($(this).find("td:eq(2) > input").val()!='1'){
				monto_om += parseFloat($(this).find("td:eq(1) > input").val()) || 0;
			}
			if($(this).find("td:eq(2) > input").val()=='1'){
				monto += parseFloat($(this).find("td:eq(1) > input").val()) || 0;
			}
		});
		vuelto = pagar - (total_venta - monto_om);
		$("#vuelto").val(vuelto < 0 ? '0.00' : vuelto.toFixed(2));
	}
	function buscarclientenombre(){
		id = $("#clinom").val(); $("#modal-lista-clientes").modal("show");
		$.ajax({type: "GET", dataType: 'json', url: "/buscarclientenombre/"+id}).done(function(respuesta){
			$("#clientes").html(respuesta.vista);
		});
	}
	function agregardireccion(direccion){ $("#clidir").val(direccion); $("#modal-direcciones").modal("hide"); }
	function agregarcliente(clicod,clinum,clinom,clidir,tdicod,clicor){
		$('#clinom').val(clinom); $('#clinum').val(clinum); $('#clidir').val(clidir);
		$('#clicor').val(clicor);  $('#clicod').val(clicod); $("#tdicod").val(tdicod).change();
		if($('#tdicod').val()=='6') { $("#tdocod").val('01').change(); }
		if($('#tdicod').val()=='1') { $("#tdocod").val('03').change(); }
		$("#modal-lista-clientes").modal("hide");
	}
	function ElimMedPag(btn) { var row = btn.parentNode.parentNode; row.parentNode.removeChild(row); calcular_vuelto(); };
	function agregar_item(){
		var id_ = $('#_id').val(); var producto_ = $('#_producto').val();
		var precio_ = $('#_precio').val(); var acompa_ = $('#_acompa').val();
		var validar = checkId(id_);
		if (validar==true){
			$("#tbl_detalle > tbody > tr").each(function(){
				if(id_==$(this).find("td:eq(0) > input").val()){ //Corregido: id_
					var calcular_cantidad = parseFloat($(this).find("input[name='txt_cantidad[]']").val()) + 1; // Corregido selector
					$(this).find("input[name='txt_cantidad[]']").val(calcular_cantidad);
				}
			});
		}else{
			$('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id_+'"></td><td>'+producto_+' '+acompa_+'</td><td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="form-control form-control-sm" name="txt_cantidad[]" value="1" min="1"></td><td hidden="hidden">'+precio_+'</td><td><input class="form-control form-control-sm" type="number" step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td><td hidden="hidden"><input class="form-control" type="text" name="descripcion[]" value="'+producto_+' '+acompa_+'"></td><td><input class="form-control form-control-sm" type="text" name="item_obs[]"></td><td style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm"><i class="fa fa-times"></i></button></td></tr>');
		}
		$('#_id').val(''); $('#_producto').val(''); $('#_precio').val(''); $('#_acompa').val('');
		calcular_total();
	}
	function elegir_acompanamiento(id,producto,precio,cat_sig,acom){
		var cat_acom = $('#cat_acom').val();
		if(cat_acom=='1'){
			$('#_acompa').val(producto); agregar_item();
			$('#_id').val(''); $('#_producto').val(''); $('#_precio').val(''); $('#_acompa').val('');
		}else{
			$('#_id').val(id); $('#_producto').val(producto); $('#_precio').val(precio); $("#btnAgregarItem").hide();	
		}
		if(cat_sig !=''){
			buscar_producto_categoria(cat_sig);
		}else{
			var id_ = $('#_id').val(); var producto_ = $('#_producto').val(); var precio_ = $('#_precio').val();
			var validar = checkId(id_);
			if (validar==true){
				$("#tbl_detalle > tbody > tr").each(function(){
					if(id_==$(this).find("td:eq(0) > input").val()){ //Corregido
						var calcular_cantidad = parseFloat($(this).find("input[name='txt_cantidad[]']").val()) + 1; //Corregido
						$(this).find("input[name='txt_cantidad[]']").val(calcular_cantidad);
					}
				});
			}else{
				$('#items_pedidos').append('<tr><td hidden="hidden" id="'+id_+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id_+'"></td><td>'+producto_+'</td><td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="form-control form-control-sm" name="txt_cantidad[]" value="1" min="1"></td><td hidden="hidden">'+precio_+'</td><td><input class="form-control form-control-sm" type="number" step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td><td hidden="hidden"><input class="form-control" type="text" name="descripcion[]" value="'+producto_+'"></td><td><input class="form-control form-control-sm" type="text" name="item_obs[]"></td><td style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm"><i class="fa fa-times"></i></button></td></tr>');
			}
			$('#_id').val(''); $('#_producto').val(''); $('#_precio').val(''); $('#_acompa').val('');
		}
		calcular_total();
	}
	function checkId(id) {
		var contar=0;
		$("#tbl_detalle > tbody > tr").each(function(){
			if(id==$(this).find("td:eq(0) > input").val()){ contar = contar+1; }
		});
		return contar > 0;
	}
	function checkIdMedPag(id) {
		var contar=0;
		$("#tbl_med_pag > tbody > tr").each(function(){
			if(id==$(this).find("td:eq(0) > input").val()){ contar = contar+1; }
		});
		return contar > 0;
	}
	function eliminar_item(btn) { var row = btn.parentNode.parentNode; row.parentNode.removeChild(row); calcular_total(); };
	function eliminar_item_registrado(btn,item) { eliminar_item_pedido(item); var row = btn.parentNode.parentNode; row.parentNode.removeChild(row); calcular_total(); };
	function elegir_mesa(mesa,id,nombre){
		var piso = $("#piso option:selected").text(); var pis_id = $("#piso option:selected").val();
		$("#mes_id").val(id); $("#mes_nom").val(nombre); $("#pis_id").val(pis_id);
		$("#lbl_pis_mes").text(piso+' / '+mesa);
		consultar_mesa_pedido(id);
	}
	function eliminar_item_pedido(item){
		var pedido = $("#ped_id").val();
		$.ajax({type: "GET",dataType: 'json',url: "/eliminaritem/"+item+"/"+pedido}).done(function(respuesta){
			alert(respuesta.mensaje); if(respuesta.contar==0){ window.location.href = "/consola"; }
		});
	}
	function eliminar_pedido(item){
		var pedido = $("#ped_id").val();
		if(pedido==''){ alert('ELEGIR PEDIDO A ELIMINAR');
		}else{ $.ajax({type: "GET",dataType: 'json',url: "/eliminarpedido/"+pedido}).done(function(respuesta){
				alert(respuesta.mensaje); window.location.href = "/consola"; });
		}
	}
	function refrescar(){
		var piso = $("#piso").val(); var tipo = $("#tipo").val();
		if(tipo=='1'){ $.ajax({type: "GET",dataType: 'json',url: "/buscarmesas/"+piso}).done(function(respuesta){ $("#listar_mesas").html(respuesta.vista); });	}
	}
	function consultar_mesa_pedido(id){
		$.ajax({type: "GET",dataType: 'json',url: "/buscarpedidomesa/"+id}).done(function(respuesta){
			if(respuesta.estado=='1'){
				$("#listar_pedido").html(respuesta.vista); $("#ped_id").val(respuesta.ped_id); $("#accion").val("1");
			}else{
				$('#items_pedidos').empty(); $("#accion").val("0"); $("#ped_id").val("");
			}
			calcular_total();
		});
	}
	function consultar_pedido_llevar_delivery(id){
		$.ajax({type: "GET",dataType: 'json',url: "/buscarpedidollevardelivery/"+id}).done(function(respuesta){
			if(respuesta.estado=='1'){
				$("#listar_pedido").html(respuesta.vista); $("#ped_id").val(respuesta.ped_id); $("#accion").val("1");
			}else{
				$('#items_pedidos').empty(); $("#accion").val("0"); $("#ped_id").val("");
			}
			calcular_total();
		});
	}
	function limpiarpedido(){
		$("#mes_id, #pis_id, #ped_num_doc, #ped_cli_nom, #ped_dir, #ped_id, #mes_nom, #ped_obs, #ped_tel, #ped_ref, #ped_pag_efe").val("");
		$("#accion").val("0"); $("#tdicod").val("1").change();
		$("#ped_pag_tar, #ped_fac").prop("checked", false);
		$('#items_pedidos').empty();
		calcular_total();
	}
	function buscarcliente(){
		var ped_cli_num = $("#ped_num_doc").val(); $("#imgloadcliente").show();
		$.ajax({type: "get",dataType: 'json',url: '/autocomplete/'+ped_cli_num}).done(function(respuesta){
			if(respuesta.error){ alert(respuesta.error); $("#imgloadcliente").hide();
			}else{
				$('#ped_cli_nom').val(respuesta[0].nom); $('#ped_dir').val(respuesta[0].dir);
				$("#tdicod").val(respuesta[0].tdicod).change(); $("#imgloadcliente").hide(); $(".botones").show(); 
			}
		});
	}
	function buscar_producto_categoria_llevar(id){
		var producto=0;
		$.ajax({type: "GET",dataType: 'json',url: "/buscarcartallevar/"+producto+"/"+id}).done(function(respuesta){
			$("#items_productos").html(respuesta.vista);
		});
	}
	function calcular_total(){
		var total = 0;
		$("#tbl_detalle tbody tr").each(function(){
			total += (parseFloat($(this).find("input[name='txt_cantidad[]']").val()) || 0) * (parseFloat($(this).find("input[name='precios[]']").val()) || 0);
		});
		$('#total_venta').val(total.toFixed(2));
	}
	function buscarclienteruc(){
		var formulario = $("#clinum").val(); $("#imgloadcliente").show();
		$.ajax({type: "get",dataType: 'json',url: '/autocomplete/'+formulario}).done(function(respuesta){
			if(respuesta.error){
				alert(respuesta.error); $("#imgloadcliente").hide();
			}else{
				$('#clinom').val(respuesta[0].nom); $('#clidir').val(respuesta[0].dir); $('#clicor').val(respuesta[0].cor);
				$('#clicod').val(respuesta[0].clicod); $("#tdicod").val(respuesta[0].tdicod).change();
				if($('#tdicod').val()=='6') { $("#tdocod").val('01').change(); }
				if($('#tdicod').val()=='1') { $("#tdocod").val('03').change(); }
				$("#imgloadcliente").hide();
			}
		});
	}
</script>

{{-- HTML ESTRUCTURAL --}}
@if(isset($codfact))
<a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
<input type="hidden" name="comprobante" id="comprobante" value="{{$codfact}}">
@endif
@if(isset($tdocod))
<input type="hidden" name="documento" id="documento" value="{{$tdocod}}">
@endif

<br>

{!!Form::open(array('url'=>'/registrar','autocomplete'=>'off','method'=>'POST','name'=>'frmcomandas','id'=>'frmcomandas','role'=>'form','files'=>'true'))!!}
{{Form::token()}}
<div class="container-fluid">
	<div class="row">
        {{-- COLUMNA IZQUIERDA - Carrito y Pago --}}
		<div class="col-md-5">
			<div class="card shadow-sm mb-4">
				<div class="card-header bg-primary text-white">
					<h5 class="mb-0"><strong><i class="fa fa-shopping-cart mr-2"></i>Detalle de Venta</strong></h5>
				</div>
				<div class="card-body">
					<div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
						<table class="table table-sm table-hover table-striped" id="tbl_detalle">
							<thead class="thead-dark">
								<tr class="text-center">
									<th hidden>ID</th>
									<th>Producto</th>
									<th style="width: 80px;">Cant.</th>
									<th style="width: 100px;">Precio</th>
									<th style="width: 120px;">Obs.</th>
									<th style="width: 50px;"></th>
								</tr>
							</thead>
							<tbody id="items_pedidos"></tbody>
						</table>
					</div>
				</div>
			</div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><strong><i class="fa fa-credit-card mr-2"></i>Proceso de Pago</strong></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="total_venta" class="font-weight-bold">TOTAL</label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">S/</span></div>
                                    <input type="text" class="form-control form-control-lg text-right font-weight-bold" style="font-size: 2rem;" readonly name="total_venta" id="total_venta" value="0.00">
                                </div>
                            </div>
                             <div class="form-group">
                                <label for="pagar" class="font-weight-bold">PAGA CON</label>
                                <div class="input-group">
                                     <div class="input-group-prepend"><span class="input-group-text font-weight-bold">S/</span></div>
                                    <input type="number" step="any" class="form-control form-control-lg text-right" style="font-size: 1.5rem;" id="pagar" name="pagar" value="0.00" onkeyup="calcular_vuelto();">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="vuelto" class="font-weight-bold">VUELTO</label>
                                 <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text font-weight-bold">S/</span></div>
                                    <input type="text" class="form-control form-control-lg text-right" style="font-size: 1.5rem;" id="vuelto" name="vuelto" value="0.00" readonly>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="med_pag">Medios de Pago</label>
                                <select class="form-control" name="med_pag" id="med_pag">
                                    @foreach($mediospagos as $medpag)
                                    <option value="{{$medpag->id_med_pag}}" data-nom="{{$medpag->nom_med_pag}}" data-predeterminado="{{$medpag->predeterminado}}">{{$medpag->nom_med_pag}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <input name="mon_med_pag" id="mon_med_pag" value="0.00" class="form-control">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-info" onclick="agregar_medio_pago();"><i class="fa fa-plus"></i></button>
                                    </div>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 100px; overflow-y: auto;">
                                <table class="table table-sm" id="tbl_med_pag">
                                    <tbody id="tbody_med_pag"></tbody>
                                </table>
                            </div>
                            <hr>
                            <div class="d-flex flex-column">
                                <button type="button" id="btnRegistrar" class="btn btn-success btn-lg btn-block mb-2 botones"><strong><i class="fa fa-check-circle mr-2"></i>COBRAR</strong></button>
                                <a href="/consolacaja" class="btn btn-danger btn-lg btn-block botones"><i class="fa fa-times-circle mr-2"></i>SALIR</a>
                                <center><img style="display:none;" width="60px" height="60px" src="/img/load.gif" name="imgload" id="imgload"></center>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
		</div>

        {{-- COLUMNA DERECHA - Categorías y Productos --}}
		<div class="col-md-7">
			<div class="card shadow-sm">
                <div class="card-body">
                    <div class="form-group">
                        <input type="text" name="txt_bus_pro" id="txt_bus_pro" class="form-control form-control-lg" placeholder="&#xF002; Buscar producto por nombre o código..." style="font-family:Arial, FontAwesome">
                    </div>
					<div class="category-buttons-container">
						@foreach($categorias as $cat)
						<button type="button" class="btn btn-category" onclick="buscar_producto_categoria_img({{$cat->cat_id}});" style="background-color:{{$cat->color}}; border-color:{{$cat->color}};">
							{{$cat->cat_nom}}
						</button>
						@endforeach
					</div>
					<div id="items_productos_img" class="row" style="max-height: 520px; overflow-y: auto;">
						{{-- Los productos se cargarán aquí dinámicamente vía AJAX --}}
                        @foreach($productos as $p)
                            <div class="col-lg-3 col-md-4 col-sm-6 col-6" onclick="elegir_acompanamiento('{{$p->IdProducto}}','{{$p->pronom}}','{{$p->precio}}','{{$p->cat_sig}}','{{$p->acom}}','{{$p->icbper}}','{{$p->stock_disponible}}')">
                                <div class="product-item-custom">
                                    <div class="product-image-container">
                                        <span class="info-box-icon" style="background-image:url('/imagenes/productos/{{$p->imagenproducto}}');"></span>
                                    </div>
                                    <div class="product-details-container">
                                        <span class="product-name-custom">{{$p->pronom}}</span>
                                        <span class="product-price-custom">S/. {{number_format($p->precio,'2','.','')}}</span>
                                        <span class="product-stock-custom">STOCK: {{ number_format($p->stock_disponible, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
					</div>
                </div>
            </div>
		</div>
	</div>

    {{-- DATOS DEL COMPROBANTE (SECCIÓN COLAPSABLE) --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">
                        <a data-toggle="collapse" href="#comprobanteCollapse" role="button" aria-expanded="false" aria-controls="comprobanteCollapse" class="text-white stretched-link">
                            <i class="fa fa-file-alt mr-2"></i><strong>Datos del Comprobante</strong> (Opcional)
                        </a>
                    </h5>
                </div>
                <div class="collapse" id="comprobanteCollapse">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 form-group">
                                <label>Comprobante</label>
                                <select class="form-control" name="tdocod" id="tdocod">
                                    @foreach($comprobantes as $comp)
                                    <option value="{{$comp->tdocod}}" {{$comp->tdocod == $negocio->tdocod_pred ? 'selected' : ''}}>{{$comp->tdodes}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>Condición Pago</label>
                                <select class="form-control" name="estadopago" id="estadopago">
                                    @foreach($estadopagos as $est_pag)
                                    <option value="{{$est_pag->cre_dia_id}}">{{$est_pag->cre_dia_nom}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <label>F. Emisión</label>
                                <input type="date" id="fecEmi" name="fecEmi" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" class="form-control">
                            </div>
                             <div class="col-md-3 form-group">
                                <label>Imprimir</label>
                                <div class="form-check">
                                    <input class="form-check-input" name="imprimir" type="checkbox" value="1" checked="checked" id="imprimirCheck">
                                    <label class="form-check-label" for="imprimirCheck">Sí, imprimir al cobrar</label>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                             <div class="col-md-2 form-group">
                                <label>Tipo Doc.</label>
                                <select name="tdicod" id="tdicod" class="form-control">
                                    @foreach($documentos as $doc)
                                    <option value='{{$doc->tdicod}}' {{$doc->tdicod == '1' ? 'selected' : ''}}>{{$doc->tdides}}</option>
                                    @endforeach
                                </select>
                            </div>
                             <div class="col-md-3 form-group">
                                <label>N° Documento</label>
                                <div class="input-group">
                                    <input name="clinum" id="clinum" value="00000000" class="form-control" onkeypress="if(event.keyCode == 13) buscarclienteruc();">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-primary" onclick="buscarclienteruc();"><i class="fa fa-search"></i></button>
                                    </div>
                                </div>
                            </div>
                             <div class="col-md-7 form-group">
                                <label>Nombre / Razón Social</label>
                                <input name="clinom" id="clinom" value="VENTA AL PORTADOR" class="form-control">
                                <input type="hidden" name="clicod" id="clicod">
                            </div>
                        </div>
                         <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Dirección</label>
                                <input name="clidir" id="clidir" value="--" class="form-control">
                            </div>
                             <div class="col-md-6 form-group">
                                <label>Correo Electrónico</label>
                                <input name="clicor" id="clicor" value="" class="form-control" placeholder="cliente@example.com">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div> {{-- Cierre de container-fluid --}}

{{-- Inputs ocultos para la lógica JS --}}
<input type="hidden" name="mes_id" readonly="readonly" id="mes_id" value="">
<input type="hidden" name="pis_id" readonly="readonly" id="pis_id" value="">
<input type="hidden" name="ped_id" id="ped_id" readonly="readonly" value="">
<input type="hidden" name="tipo" id="tipo" readonly="readonly" value="">
<input type="hidden" readonly="readonly" class="form-control" name="_id" id="_id">
<input type="hidden" readonly="readonly" class="form-control" name="_producto" id="_producto">
<input type="hidden" readonly="readonly" class="form-control" name="_icbper" id="_icbper">
<input type="hidden" readonly="readonly"  class="form-control" name="_precio" id="_precio">
<input type="hidden" readonly="readonly" class="form-control" name="_acompa" id="_acompa">
<input type="hidden" readonly="readonly" class="form-control" name="_stock" id="_stock">

{!!Form::close()!!}
@endsection