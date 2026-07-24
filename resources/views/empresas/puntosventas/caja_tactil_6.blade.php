@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')
@include('empresas.puntosventas.modaldirecciones')
@include('empresas.puntosventas.modalclientes')


@if(!empty($cat_pred))

<style>

	.product-stock-custom {
        display: block; /* Asegura que tome su propia línea */
        font-size: 0.9em; /* Un poco más pequeño que el precio */
        color: #666; /* Color gris suave */
        margin-top: 3px; /* Espacio superior */
    }

    /* Opcional: Ajustes en media queries si el stock hace que se vea apretado */
    @media (max-width: 991px) {
        .product-stock-custom {
            font-size: 0.8em;
        }
    }
    @media (max-width: 767px) {
        .product-stock-custom {
            font-size: 0.75em;
        }
    }
    
    .product-item-custom {
        border: 1px solid #ddd; /* Borde más suave */
        border-radius: 10px; /* Bordes más redondeados */
        padding: 10px;
        margin-bottom: 15px; /* Más espacio entre los cuadros */
        background-color: #fcfcfc; /* Fondo casi blanco */
        height: 180px; /* Altura consistente */
        display: flex;
        flex-direction: column;
        justify-content: space-between; /* Espaciar contenido uniformemente */
        align-items: center;
        text-align: center;
        box-shadow: 0 4px 8px rgba(0,0,0,0.08); /* Sombra más notoria */
        transition: transform 0.2s ease, box-shadow 0.2s ease; /* Transición suave */
        cursor: pointer; /* Indica que es clicable */
    }

    .product-item-custom:hover {
        transform: translateY(-3px); /* Efecto de elevación al pasar el mouse */
        box-shadow: 0 8px 16px rgba(0,0,0,0.15); /* Sombra más fuerte */
        border-color: #a0d4e7; /* Borde al pasar el mouse */
    }

    .product-image-container {
        flex-grow: 1; /* Permite que el contenedor de la imagen crezca para ocupar espacio */
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%; /* Asegura que la imagen se centre correctamente */
        margin-bottom: 8px; /* Más espacio entre imagen y texto */
    }

    .info-box-icon {
        background-color: transparent !important; /* Asegura que no tenga fondo azul si no hay imagen */
        display: flex; /* Para centrar la imagen/icono dentro del span */
        align-items: center;
        justify-content: center;
        width: 90px; /* Tamaño fijo para la imagen */
        height: 90px;
        background-position: center !important; /* Centra la imagen de fondo */
        background-repeat: no-repeat !important;
        background-size: contain !important; /* Asegura que la imagen se ajuste sin cortarse */
    }

    .product-name-custom {
        font-weight: bold;
        display: block;
        white-space: normal;
        word-wrap: break-word;
        height: auto; /* Dejar que el contenido defina la altura, si no se lee bien se puede limitar con max-height */
        overflow: hidden;
        text-overflow: ellipsis; /* Mostrar puntos suspensivos si el texto es muy largo */
        font-size: 1.0em; /* Un poco más grande para el nombre */
        color: #333; /* Color de texto más oscuro */
        line-height: 1.2; /* Espaciado entre líneas */
        margin-bottom: 5px; /* Espacio entre nombre y precio */
    }

    .product-price-custom {
        display: block;
        font-size: 1.2em; /* Precio más grande */
        font-weight: bold;
        color: #007bff; /* Color azul para el precio */
    }

    .product-details-container {
        padding-top: 5px;
        width: 100%;
    }

    @media (max-width: 991px) { /* Small devices (tablets, 768px and up) */
        .product-item-custom {
            height: 160px; /* Un poco más pequeños en tablets */
            padding: 8px;
        }
        .info-box-icon {
            width: 70px;
            height: 70px;
        }
        .product-name-custom {
            font-size: 0.9em;
            line-height: 1.1;
        }
        .product-price-custom {
            font-size: 1.1em;
        }
    }

    @media (max-width: 767px) { /* Extra small devices (phones, 480px and up) */
        .product-item-custom {
            height: 140px; /* Aún más pequeños en móviles */
            padding: 5px;
            margin-bottom: 10px;
        }
        .info-box-icon {
            width: 60px;
            height: 60px;
        }
        .product-name-custom {
            font-size: 0.85em;
            height: auto; /* Permitir que ocupe varias líneas si es necesario */
        }
        .product-price-custom {
            font-size: 1.0em;
        }
    }



    .category-buttons-container {
        background-color: #E8E8E8;
        padding: 2px; /* Más padding para que los botones no estén tan pegados */
        display: flex; /* Usar flexbox para mejor alineación */
        flex-wrap: wrap; /* Permitir que los elementos se envuelvan a la siguiente línea */
        justify-content: center; /* Centrar los botones horizontalmente */
        align-items: flex-start;
    }

    .category-button-wrapper {
        padding: 2px; /* Espacio entre los botones */
        display: flex; /* Para centrar el botón dentro de su columna */
        justify-content: center;
    }

    .btn-category {
        width: 100%; /* Ocupar el 100% del ancho de su columna */
        max-width: 180px; /* Limitar el ancho máximo para que no sean demasiado grandes */
        height: 30px; /* Altura fija para todos los botones */
        font-size: 1.1em; /* Tamaño de fuente más grande */
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

    /* Media queries para responsividad si es necesario afinar más los tamaños */
    @media (max-width: 767px) { /* Para pantallas pequeñas (móviles) */
        .btn-category {
            height: 50px;
            font-size: 1em;
            max-width: 150px;
        }
    }
</style>

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

		
		$("#btnRegistrar").on("click", function() {


			if ($('#items_pedidos >tbody >tr').length == 0){
				$('#alertitem').show();
				event.preventDefault(); 
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

					//window.location.href = "/consolacaja/"+respuesta.codfact;
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

			$("<iframe>")                             
			.hide()                              
			.attr("src", "/precuenta/"+ped_id) 
			.appendTo("body");                   
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
				}).done(function(respuesta){

					
				});

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
						/*$("#tipo").val(tipo_comanda);
						alert(respuesta.mensaje);
						limpiarpedido();*/
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

function buscar_producto_categoria_img(id){
	
	var producto=0;
	$.ajax({
		type: "GET",
		dataType: 'json',
		url: "/buscarcartaimg/"+producto+"/"+id,
	}).done(function(respuesta){


		$("#items_productos_img").html(respuesta.vista);
	});
}


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

	calcular_vuelto();

}


function calcular_vuelto(){

	var total_venta = $("#total_venta").val();
	var pagar = $("#pagar").val();
	var vuelto = 0;
	var monto_om = 0;
	var monto = 0;

	

	$("#tbl_med_pag tbody tr").each(function(){

		if($(this).find("td:eq(2)  > input").val()!='1'){
			monto_om = parseFloat($(this).find("td:eq(1)  > input").val()) + monto_om;
		}

		if($(this).find("td:eq(2)  > input").val()=='1'){
			monto = parseFloat($(this).find("td:eq(1)  > input").val()) + monto;
		}
	})


	vuelto = pagar-(total_venta-monto_om);

	if(vuelto<0){
		$("#vuelto").val(0);
	}else{
		$("#vuelto").val(vuelto);	
	}
	


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

function ElimMedPag(btn) {
	var row = btn.parentNode.parentNode;
	row.parentNode.removeChild(row);

	calcular_vuelto();

};


function agregar_item(){

	var id_ = $('#_id').val();
	var producto_ = $('#_producto').val();
	var precio_ = $('#_precio').val();
	var acompa_ = $('#_acompa').val();

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
			'<td>'+producto_+' '+acompa_+'</td>'+
			'<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
			'<td style="text-align:right;" hidden="hidden">'+precio_+'</td>'+
			'<td style="text-align:right;" ><input  class="form-control" type="number"  step="any" onkeyup="calcular_total();" onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
			'<td style="text-align:right;" hidden="hidden" ><input  class="form-control" type="text"   name="descripcion[]" value="'+producto_+' '+acompa_+'"></td>'+
			'<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
			'<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
	}

	$('#_id').val('');
	$('#_producto').val('');
	$('#_precio').val('');
	$('#_acompa').val('');

	

	calcular_total();

}

function elegir_acompanamiento(id,producto,precio,cat_sig,acom){

	var cat_acom = $('#cat_acom').val();



	if(cat_acom=='1'){

		$('#_acompa').val(producto);
		agregar_item();

		$('#_id').val('');
		$('#_producto').val('');
		$('#_precio').val('');
		$('#_acompa').val('');
		

	}else{

		$('#_id').val(id);
		$('#_producto').val(producto);
		$('#_precio').val(precio);
		$("#btnAgregarItem").hide();	

	}


	if(cat_sig !=''){

		buscar_producto_categoria(cat_sig);




	}else{


		var id_ = $('#_id').val();
		var producto_ = $('#_producto').val();
		var precio_ = $('#_precio').val();
		
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
				'<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
		}

		$('#_id').val('');
		$('#_producto').val('');
		$('#_precio').val('');
		$('#_acompa').val('');

	}

		/*var validar = checkId(id);

		if (validar==true){
			$("#tbl_detalle  > tbody  > tr").each(function(){
				if(id==$(this).find("td:eq(0) > input").val()){
					var calcular_cantidad = parseFloat($(this).find("td:eq(2) > input").val())+1;

					$(this).find("td:eq(2) > input").val(calcular_cantidad);
				}
			});

		}else{
			$('#items_pedidos').append('<tr><td hidden="hidden" id="'+id+'"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="'+id+'"></td>'+
				'<td>'+producto+'</td>'+
				'<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" onChange="calcular_total();" class="form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
				'<td style="text-align:right;">'+precio+'</td>'+
				'<td style="text-align:right;" hidden="hidden"><input type="number" readonly="readonly" step="any" name="precios[]" value="'+precio+'"></td>'+
				'<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
		}*/

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

	calcular_total();

};

function eliminar_item_registrado(btn,item) {

	eliminar_item_pedido(item);

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

	    	//$("#mes_id").val("");
			//$("#pis_id").val("");

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

	$("#tbl_detalle tbody tr").each(function(){

		total = total + parseFloat($(this).find("td:eq(2)> input").val()*$(this).find("td:eq(4)>input").val());


	})


	$('#total_venta').val(total.toFixed(2));

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

			$("#imgloadcliente").hide();
		   	// $(".botones").show(); 

		}


	});



}



</script>

@if(isset($codfact))
<a class="btnPrint" href='' ><button type="button" hidden="hidden" id="btnPrint" class="btnPrint" value="imprimir"></button></a>
@endif

@if(isset($codfact))
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
		
		<div class="col-lg-5">
			<div class="box" style="min-height:600px;">
			
	            <div class="box-body">
	            	<div class="row">
	            		<!-----------------------------------------------------inicio segunda columna----------------------------------------------------------->
						<!--<div class="col-lg-12">
							<div class="col-lg-12" style="display:none;" >
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
								<div class="col-lg-12" style="margin-top:-20px;margin-bottom:20px;overflow-y:auto;max-height:200px;display:none;" id="items_productos">

								</div>-->

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

						<div  class="col-lg-12" style="margin-top:-20px;margin-bottom:20px;overflow-y:auto;max-height:200px;" id="items_productos">



						</div>



								<div class="col-lg-12" id="listar_pedido">
									<div class="form-group form-group-sm">
										<table class="table table-striped table-hover table-bordered table-condensed" id="tbl_detalle">

											<thead style="background:orange;">
											
												<tr style="text-align:center;font-weight:bold;" hidden='hidden'>
													<td colspan="5">
														<label id="lbl_pis_mes"> </label>
														<select name="mozo" id="mozo" class="form-control input-block"> 
															<option></option>
															@foreach($mozos as $mz)
															<option value="{{$mz->IdUsuario}}">{{$mz->name}} {{$mz->apeusu}}</option>
															@endforeach
														</select>
													</td>
												</tr>
												<tr style="text-align:center;font-weight:bold;">
													<td hidden="hidden"></td>
													<td>PRODUCTO</td>
													<td>CANTIDAD</td>
													<td>PRECIO</td>
													<td>OBSERVACIÓN</td>
													<td>ELIMINAR</td>
												</tr>
											</thead>
											<tbody id="items_pedidos">
											</tbody>
										</table>
									</div>
								</div>
								<!--</div>-->

								
							</div>
						</div>
						<div class="box-header" style="background-color:#E8E8E8;">
							<strong><font style="font-size:10pt;"><center>PAGO</center></font></strong>

						</div>
						<div class="box-body">
							<div class="row">
								<div class="col-lg-3">
									<div class="form-group form-group-sm">
										<label>TOTAL S/.</label>
										<input type="number" class="form-control input-lg" style="height:45px;width:160px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="total_venta" id="total_venta" value="0.00">
									</div>

									<div class="form-group form-group-sm">
										<label>PAGA CON:</label>
										<input type="number"  step="any" class="form-control" style="height:45px;width:160px;font-size:22pt;font-weight:bold;"  id="pagar" name="pagar" value="0.00" onkeyup="calcular_vuelto();">
									</div>


									<div class="form-group form-group-sm">
										<label>VUELTO</label>
										<input type="text" class="form-control" style="height:45px;width:160px;font-size:22pt;font-weight:bold;"  id="vuelto" name="vuelto" value="0.00" readonly="readonly">
									</div>


								</div>

								<div class="col-lg-4">
									<div class="form-group form-group-sm">
										<label>MEDIOS PAGO</label>
										<select class="form-control" name="med_pag" id="med_pag">
											@foreach($mediospagos as $medpag)
											<option value="{{$medpag->id_med_pag}}" data-nom="{{$medpag->nom_med_pag}}" data-comision="{{$medpag->comision_mont}}"  data-predeterminado="{{$medpag->predeterminado}}">{{$medpag->nom_med_pag}}</option>
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

								

								<div class="col-lg-5">
									<div class="form-group form-group-sm">
										<label>OPERACIONES</label>
										<button type="button" id="btnRegistrar" class="btn btn-success btn-lg btn-block botones">COBRAR</button>
									</div>

									<div class="form-group form-group-sm">

										<a href="/consolacaja"><button type="button" class="btn btn-danger btn-lg btn-block botones">SALIR</button></a>
									</div>

									<center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>


								</div>
							</div>

							
						</div>	

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
				<div class="box-body" >
					<div class="row">
						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>Comprobante</label>
								<select class="form-control" name="tdocod" id="tdocod">
									@foreach($comprobantes as $comp)
									@if($comp->tdocod==$negocio->tdocod_pred)
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
						<div class="col-lg-3"  id="divfecVen" hidden="hidden">
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

									@if($doc->tdicod == '1')
									<option selected="selected"  value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
									@else
									<option value='{{$doc->tdicod}}'>{{$doc->tdides}}</option>
									@endif

									@endforeach
								</select>
							</div>
						</div>
						<div class="col-lg-4">
							<div class="form-group">
								<label>DNI / RUC</label>
								<div class="input-group input-group-sm">
									<input name="clinum" id="clinum" value="00000000" class="form-control" onkeypress="if(event.keyCode == 13) buscarclienteruc();">
									<input type="hidden" name="clicod" id="clicod"  class="form-control">
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary btn-flat" onclick="buscarclienteruc();"><span class="fa fa-search"></span></button>

									</span>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="form-group">
								<label>Nombre o Razon Social -Cliente</label>
								<div class="input-group input-group-sm">
									<input name="clinom" id="clinom" value="VENTA AL PORTADOR" class="form-control" onkeypress="if(event.keyCode == 13) buscarclientenombre();">
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
									<input name="clidir" id="clidir" value="--" class="form-control">
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
	              	<!--<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
	                		<div class="form-group form-group-sm">
	                  			<label>Tel&eacute;fono</label>
	                  			<input name="clitel" id="clitel" value="{{old('clitel')}}" class="form-control">
	                		</div>
	                	</div>-->
	                </div>
	                <div class="row" hidden="hidden">
	                	<div class="col-lg-12">
	                		<label>Observaciones:</label>
	                		<textarea class="form-control" rows="5" name="observaciones"></textarea>
	                	</div>
	                </div>
	            </div>
	            
						<input type="hidden" name="mes_id" readonly="readonly" id="mes_id" value="">
						<input type="hidden" name="pis_id" readonly="readonly" id="pis_id" value="">
						<input type="hidden" name="ped_id" id="ped_id" readonly="readonly" id="ped_id" value="">
						<input type="hidden" name="tipo" id="tipo" readonly="readonly" value="">

					</div>
				</div>

				<div class="col-lg-7">
					<div class="box" style="background-color:#F0F0F0;">
						<div class="box-header category-buttons-container">
					    @foreach($categorias as $cat)
					    <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6 category-button-wrapper">
					        <button type="button" class="btn btn-category" onclick="buscar_producto_categoria_img({{$cat->cat_id}});" style="background-color:{{$cat->color}}; border-color:{{$cat->color}};">
					            {{$cat->cat_nom}}
					        </button>
					    </div>
					    @endforeach
					</div>

						<div class="box-body" style="background-color:#F0F0F0;" >
							<div class="col-lg-12" style="padding:10px;" id="items_productos_img">
								@foreach($productos as $p)
    <div class="col-lg-3 col-md-4 col-sm-4 col-xs-6" onclick="elegir_acompanamiento('{{$p->IdProducto}}','{{$p->pronom}}','{{$p->precio}}','{{$p->cat_sig}}','{{$p->acom}}','{{$p->icbper}}','{{$p->stock_disponible}}')">
        <div class="product-item-custom">
            <div class="product-image-container">
                <span class="info-box-icon bg-aqua" style="background-image:url('/imagenes/productos/{{$p->imagenproducto}}');background-repeat: no-repeat;background-size: 90px 90px; display: block; margin: 0 auto; text-align: center;" ></span>
            </div>
            <div class="product-details-container" style="text-align: center; padding-top: 5px;">
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


		</div>
		<input type="hidden" readonly="readonly" class="form-control" name="_id" id="_id">
		<input type="hidden" readonly="readonly" class="form-control" name="_producto" id="_producto">
		<input type="hidden" readonly="readonly" class="form-control" name="_icbper" id="_icbper"> // Este ya estaba
		<input type="hidden" readonly="readonly"  class="form-control" name="_precio" id="_precio">
		<input type="hidden" readonly="readonly" class="form-control" name="_acompa" id="_acompa">
		<input type="hidden" readonly="readonly" class="form-control" name="_stock" id="_stock">
		{!!Form::close()!!}
		@endsection
