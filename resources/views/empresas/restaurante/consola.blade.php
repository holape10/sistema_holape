@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')

@include('empresas.puntosventas.modalclientes')
@include('empresas.restaurante.modal_unir_mesas')
@include('empresas.restaurante.modal_desunir_mesas')
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


		/*$("#btnPrecuenta").click(function(){

			var ped_id = $("#ped_id").val();
	
    		$("<iframe>")                             
        	.hide()                              
        	.attr("src", "/imprimircuentaweb/"+ped_id) 
        	.appendTo("body");                   
		});*/

		

	$("#btnCobrar").click(function(){
    var ped_id = $("#ped_id").val(); // Obtiene el valor actual del campo oculto ped_id
    var mes_id = $("#mes_id").val(); // Obtiene el valor actual del campo oculto mes_id

    // Verificar si se ha seleccionado una mesa (mes_id) o si hay un pedido activo (ped_id)
    // Usamos mes_id como condición principal, ya que si hay mesa, debería haber un pedido o uno se creará.
    // Aunque ped_id podría ser más directo si ya está poblado.
    // Vamos a usar ped_id directamente para mayor certeza, ya que es el que se pasa a la ruta.
    if (!ped_id) { // Si ped_id es nulo o vacío
        // Opción 1: Usar un alert simple
        alert('Por favor, selecciona una mesa antes de cobrar.');

        // Opción 2: Usar un modal (si ya tienes uno de información o quieres crear uno)
        // Ejemplo con un modal genérico:
        // $('#infoModal').find('.modal-title').text('Atención');
        // $('#infoModal').find('.modal-body').text('Debes seleccionar una mesa para poder cobrar.');
        // $('#infoModal').modal('show');
        return false; // Evita la acción por defecto del botón (la redirección)
    }

    // Si ped_id tiene un valor, procede con la redirección
    window.location.href = "/cobrarmesa/" + ped_id;
	});

		/*$("#btnCobrar").click(function(){

			var ped_id = $("#ped_id").val();

    		window.location.href = "/cobrarmesa/"+ped_id;               
		});*/



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
				
				/*$.ajax({
						type: "GET",
						dataType: 'json',
						url: '/imprimircomandatotal/'+id,
				}).done(function(respuesta){

					
				});*/

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
						/*$("#tipo").val(tipo_comanda);
						alert(respuesta.mensaje);
						limpiarpedido();*/
						if(tipo_comanda=='3' || tipo_comanda=='2'){
							window.location.href = "/cobrarmesa/"+respuesta.ped_id;	
						}else{


							//window.open('http://192.168.0.106/imprimircomanda/'+json_encode(respuesta.data),'_blank');
							//window.location.href = "/imprimircomanda/"+respuesta.ped_id;	

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

						//window.location.href = "/imprimircomanda/"+respuesta.ped_id;	

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
					'<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
				'<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
				'<td  style="text-align:center;"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
			}

			$('#_id').val('');
			$('#_producto').val('');
			$('#_icbper').val('');
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
			$('#_icbper').val('');
			$('#_precio').val('');
			$('#_acompa').val('');
		

		}else{

			$('#_id').val(id);
			$('#_producto').val(producto);
			$('#_icbper').val(icbper);
			$('#_precio').val(precio);
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
				'<td style="text-align:right;" ><input class="form-control" type="number" step="any" onkeyup="calcular_total();"  onChange="calcular_total();" name="precios[]" value="'+precio_+'"></td>'+
				'<td style="text-align:right;" hidden="hidden"  ><input  class="form-control" type="text"   name="descripcion[]" value="'+producto_+'"></td>'+
					'<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="'+icbper_+'"></td>'+
				'<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>'+
				'<td  style="text-align:center;"><button type="button" onClick="eliminar_item(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>');
			}

				$('#_id').val('');
				$('#_producto').val('');
				$('#_icbper').val('');
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

	window.eliminar_item_registrado = function(btn, itemId) {
    var pedidoId = $("#ped_id").val(); // <-- ¡Añadir esta línea para obtener el ID del pedido!
    
    // Si el pedidoId es indefinido o vacío, alertar y no continuar.
    if (!pedidoId) {
        alert('No se pudo obtener el ID del pedido. Por favor, recargue la página.');
        return;
    }

    $('#confirmDeleteModal').data('action-type', 'item');
    $('#confirmDeleteModal').data('item-id', itemId);
    $('#confirmDeleteModal').data('pedido-id', pedidoId); // <-- Pasar el ID del pedido al modal
    $('#confirmDeleteModal').data('button-element', btn);
    $('#confirmDeleteModal').modal('show');
	};

	 /*window.eliminar_item_registrado = function(btn, itemId) {
        // Guardamos el contexto para poder usarlo después de que el modal se cierre
        $('#confirmDeleteModal').data('action-type', 'item');
        $('#confirmDeleteModal').data('item-id', itemId);
        $('#confirmDeleteModal').data('button-element', btn); // Para eliminar la fila visualmente después
        $('#confirmDeleteModal').modal('show');
    };*/


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

	/*function eliminar_item_registrado(btn,item) {
		eliminar_item_pedido(item);
		var row = btn.parentNode.parentNode;
		row.parentNode.removeChild(row);
		calcular_total();
	};*/

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
  //$('#clicor').val(clicor); 
  //$('#clicod').val(clicod);
  //$("#tdicod").val(tdicod).attr('selected', 'selected');

 // if($('#tdicod').val() =='6' ){
 //   $("#tdocod").val('01').attr('selected', 'selected');
 // }

 // if($('#tdicod').val() =='1' ){
 //   $("#tdocod").val('03').attr('selected', 'selected');
 // }

  $("#modal-lista-clientes").modal("hide");

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
		var total_icbper = 0;
		var icbper_val = $("#icbper_val").val();

		$("#tbl_detalle tbody tr").each(function(){

			if($(this).find("td:eq(6)> input").val()==1){
				 total_icbper = total_icbper + parseFloat($(this).find("td:eq(2)> input").val()*icbper_val);
			}

		  total = total   + parseFloat($(this).find("td:eq(2)> input").val()*$(this).find("td:eq(4)>input").val());
		  
		   
		})

	
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
	/*$("#tbl_med_pag tbody tr").each(function(){

		if($(this).find("td:eq(2)  > input").val()!='1'){
			monto = parseFloat($(this).find("td:eq(1)  > input").val());
		}

		if($(this).find("td:eq(2)  > input").val()=='1'){
			vuelto = parseFloat($(this).find("td:eq(1)  > input").val()) - total_venta + monto;
		}
	})*/

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
    var btnElement = $('#confirmDeleteModal').data('button-element'); // Si es eliminación de ítem

    if (!password) {
        alert('Por favor, ingresa la contraseña.');
        return;
    }

    // Ocultar modal y limpiar campo de contraseña
    $('#confirmDeleteModal').modal('hide');
    $('#admin_password').val('');
    $('#password_error').hide();

    $.ajax({
        type: "POST",
        dataType: 'json',
        url: "/validar-admin-password", // Esta será nuestra nueva ruta
        data: {
            _token: "{{ csrf_token() }}",
            password: password
        },
        success: function(response) {
            if (response.success) {
                if (actionType === 'item') {
                    // Lógica para eliminar el ítem
                    $.ajax({
                        type: "GET",
                        dataType: 'json',
                        url: "/eliminaritem/" + itemId + "/" + pedidoId,
                        success: function(respuesta) {
                            alert(respuesta.mensaje);
                            if (respuesta.action === 'reload_page') {
                                window.location.href = "/consola";
                            } else if (respuesta.action === 'reload_partial') {
                                // Eliminar visualmente la fila del ítem
                                if (btnElement) {
                                    var row = btnElement.parentNode.parentNode;
                                    row.parentNode.removeChild(row);
                                }
                                calcular_total(); // Recalcular el total
                                // Opcional: Volver a cargar solo el detalle del pedido para asegurar consistencia
                                // consultar_mesa_pedido($("#mes_id").val());
                            }
                        },
                        error: function(xhr) {
                            alert('Error al eliminar el ítem.');
                            console.log(xhr.responseText);
                        }
                    });
                } else if (actionType === 'pedido') {
                    // Lógica para eliminar el pedido completo
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
                $('#confirmDeleteModal').modal('show'); // Volver a mostrar el modal si la contraseña es incorrecta
            }
        },
        error: function(xhr) {
            $('#admin_password').val('');
            $('#password_error').text('Error en la comunicación con el servidor.').show();
            $('#confirmDeleteModal').modal('show'); // Volver a mostrar el modal en caso de error AJAX
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
			<div class="row" style="text-align:center;">
				<div class="col-lg-12"  >
						<div style="display:none;" id="imgload"><center><img src="/img/load.gif" width="100px" height="100px" ></center></div>
					<div class="form-group form-group-sm" id="btnAcciones">

						@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
						<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							<button type="button" style="height:50px" name="btnComanda" id="btnComanda"  class=" btn btn-md btn-success" ><strong>COMANDAR</strong></button>
						</div>
						@else
						<div class="btn-group" style="margin-bottom:10px; margin-right:15px;">
							<button type="button" style="height:40px" name="btnComanda" id="btnComanda"  class=" btn btn-md btn-success" ><strong>C.</strong></button>
						</div>
						@endif

						@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))

						<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							<button type="button" style="height:50px" name="btnCobrar" id="btnCobrar"  class=" btn btn-md btn-primary" ><strong>COBRAR</strong></button>
						</div>

						@endif


						@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
						<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							 <button type="button" style="height:50px" name="btnPrecuenta" id="btnPrecuenta"  class=" btn btn-md btn-info" ><strong>PRECUENTA</strong></button>
						</div>
						@else
						<div class="btn-group" style="margin-bottom:10px; margin-right:15px;">
							 <button type="button" style="height:40px" name="btnPrecuenta" id="btnPrecuenta"  class=" btn btn-md btn-info" ><strong>P.C.</strong></button>
						</div>
						@endif
						<!--<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							 <button type="button" style="height:50px" name="btnImpComanda" id="btnImpComanda"  class=" btn btn-md btn-dafault" ><strong>IMPRIMIR COMANDA</strong></button>
						</div>-->

						@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
						<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							<button type="button" style="height:50px" name="btnCambiar" id="btnCambiar" class=" btn btn-md btn-primary" ><strong>CAMBIAR MESA</strong></button>
						</div>
						@else
						<div class="btn-group" style="margin-bottom:10px; margin-right:15px;">
							<button type="button" style="height:40px" name="btnCambiar" id="btnCambiar" class=" btn btn-md btn-primary" ><strong>C.M.</strong></button>
						</div>
						@endif

						@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
						<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							<button type="button" style="height:50px" name="btnElimPed" id="btnElimPed" class=" btn btn-md btn-danger" onclick="eliminar_pedido();" ><strong>ELIMINAR PEDIDO</strong></button>
						</div>						
						@endif

						@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
						<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							<button type="button" style="height:50px" name="btnUnirMesas" id="btnUnirMesas" class=" btn btn-md btn-primary" ><strong>UNIR MESAS</strong></button>
						</div>
						@else
						<div class="btn-group" style="margin-bottom:10px; margin-right:15px;">
							<button type="button" style="height:40px" name="btnUnirMesas" id="btnUnirMesas" class=" btn btn-md btn-primary" ><strong>U.M.</strong></button>
						</div>
						@endif
				<!--			<div class="btn-group" style="margin-bottom:10px; margin-right:35px;">
							<button type="button" style="height:50px" name="btnDesunirMesas" id="btnDesunirMesas" class=" btn btn-md btn-primary" ><strong>DESUNIR MESAS</strong></button>
						</div>
					
					<div class="btn-group" style="margin-bottom:10px; margin-rigth:20px;">
							<button type="button" style="height:50px" name="btnLimpiar" id="btnLimpiar" class=" btn btn-md btn-warning" onclick="limpiarpedido();" ><strong>LIMPIAR</strong></button>
						</div>	-->
						

					</div>
				</div>
			</div>

			<div class="row">
				<!-------------------------------------------------------primera columna---------------------------------------------->
				<div class="col-lg-4">
					<div class="row">
						<div class="col-lg-4 col-sm-4 col-xs-4">
							<div class="btn-toolbar" role="toolbar" aria-label="...">
								<div class="btn-group">
									<button type="button" id="btnSalon" class="btn btn-primary btn-md"><font><strong>SALON</strong></font></button>
								</div>
							</div>
						</div>
						<div class="col-lg-4 col-sm-4 col-xs-4">
							<div class="btn-toolbar" role="toolbar" aria-label="...">
								<div class="btn-group">
									<button type="button" id="btnLlevar" class="btn btn-primary btn-md"><font><strong>LLEVAR</strong></font></button>
								</div>
							</div>
						</div>	
						<div class="col-lg-4 col-sm-4 col-xs-4">
							<div class="btn-toolbar" role="toolbar" aria-label="...">
								<div class="btn-group">
									<button type="button" id="btnDelivery" class="btn btn-primary btn-md"><font><strong>DELIVERY</strong></font></button>
								</div>
							</div>
						</div>
						
					</div>
						<br>
					<div class="row" id="salon">
						<div class="col-lg-11 col-xs-11">
							<div class="form-group form-group-sm">
								<select class="form-control" name="piso" id="piso">
									@foreach($pisos as $piso)
									<option value="{{$piso->pis_id}}">{{$piso->pis_nom}}</option>
									@endforeach
								</select>
							</div>
						</div>

						<div id="listar_mesas">
							<?php $i=0; ?>
							@if(!empty($mesas))
							@foreach($mesas as $mesas)
							<?php $i=$i+1; ?>
							<div class="col-lg-4 col-md-2 col-sm-3 col-xs-3 form-group form-group-sm">
							@if($mesas->mes_est=='Ocupado')

							@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
									<button type="button" class="btn" style="width:90px;height:80px; background:#E74C3C;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
									<strong >{{$mesas->mes_nom}}</strong>
								</button>
							@else								
							<button type="button" class="btn" style="width:90px;height:40px; background:#E74C3C;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
									<strong >{{$mesas->mes_nom}}</strong>
								</button>
								@endif

							@elseif($mesas->mes_est=='Libre')									

							@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
									<button type="button" class="btn" style="width:90px;height:80px;background:#52BE80;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
										<strong>{{$mesas->mes_nom}}</strong>
									</button>
									@else
									<button type="button" class="btn" style="width:90px;height:40px;background:#52BE80;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
										<strong>{{$mesas->mes_nom}}</strong>
									</button>
									@endif
							@else			

							@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))						
									<button type="button" class="btn" style="width:90px;height:80px;  background:#F4D03F;color:#FDFEFE;">
										<br><font style="color:#FDFEFE"><strong >{{$mesas->mes_nom}}</strong></font>
									</button>
									@else
									<button type="button" class="btn" style="width:90px;height:40px;  background:#F4D03F;color:#FDFEFE;">
										<br><font style="color:#FDFEFE"><strong >{{$mesas->mes_nom}}</strong></font>
									</button>
									@endif								
							@endif
							<BR>				
						</div>
							@endforeach
							@endif
						</div>
					</div>
				</div>

				<!----------------------------------------------------fin primera columna----------------------------------------------------------------->


			@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
			<div class="col-lg-3"  >			
						<div class="row">
							<div class="col-lg-12" style="vertical-align:middle;">
								<strong><center> <label style="font-size:12pt;">CATEGORÍAS</label></center></strong>
							</div>			
						</div>

					<div class="row hidden-xs" ><BR>					
						@foreach($categorias as $cat)
						<div class="col-lg-6">
							<div class="form-group form-group-sm">
								<div class="btn-group">
									<button type="button" class="btn btn-md" onclick="buscar_producto_categoria({{$cat->cat_id}});" style="width:180px;margin-bottom:-10px;background:{{$cat->color}};color:white;font-weight:bold;">{{$cat->cat_nom}}</button>
								</div>
							</div>
						</div>
						@endforeach
					</div>

					<div class="row hidden-lg hidden-sm hidden-md" >									
						@foreach($categorias as $cat)
						<div class="col-xs-6" style="padding-bottom:10px;">
							<div class="form-group form-group-sm">
								<div class="btn-group">
									<button type="button" class="btn btn-md" onclick="buscar_producto_categoria({{$cat->cat_id}});" style="width:150px;height:70px;margin-bottom:-10px;background:{{$cat->color}};color:white;font-weight:bold;">{{$cat->cat_nom}}</button>
								</div>
							</div>
						</div>
						@endforeach
					</div>
				<hr>
			</div>
			@else
			<div class="col-lg-3"  >			
						<div class="row">
							<div class="col-lg-12" style="vertical-align:middle;">
								<strong><center> <label style="font-size:12pt;">CATEGORÍAS</label></center></strong>
							</div>			
						</div>

					<div class="row hidden-xs" ><BR>					
						@foreach($categorias as $cat)
						<div class="col-lg-6">
							<div class="form-group form-group-sm">
								<div class="btn-group">
									<button type="button" class="btn btn-md" onclick="buscar_producto_categoria({{$cat->cat_id}});" style="width:150px;height:40px;margin-bottom:-10px;background:{{$cat->color}};color:white;font-weight:bold;">{{$cat->cat_nom}}</button>
								</div>
							</div>
						</div>
						@endforeach
					</div>

					<div class="row hidden-lg hidden-sm hidden-md" >									
						@foreach($categorias as $cat)
						<div class="col-xs-6" style="padding-bottom:10px;">
							<div class="form-group form-group-sm">
								<div class="btn-group">
									<button type="button" class="btn btn-md" onclick="buscar_producto_categoria({{$cat->cat_id}});" style="width:150px;height:40px;margin-bottom:-10px;background:{{$cat->color}};color:white;font-weight:bold;">{{$cat->cat_nom}}</button>
								</div>
							</div>
						</div>
						@endforeach
					</div>
				<hr>
			</div>
			@endif

						

				<!-----------------------------------------------------inicio segunda columna----------------------------------------------------------->

				<div class="col-lg-5">
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

						<div class="col-lg-12" style="margin-top:-20px;margin-bottom:20px;overflow-y:auto;max-height:270px;" id="items_productos">



						</div>
						<div class="col-lg-12" style="display:none;" id="btnAgregarItem">
							<button type="button" class="btn-btn-success btn-lg"  onclick="agregar_item()">AGREGAR</button>
						</div>


						<div class="col-lg-12" id="listar_pedido">
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
															<option selected="selected" value="{{$mz->IdUsuario}}" hidden="hidden">{{$mz->name}} {{$mz->apeusu}}</option>
														@else
															<option value="{{$mz->IdUsuario}}" hidden="hidden">{{$mz->name}} {{$mz->apeusu}}</option>

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
										</tr>
									</tbody>
								</table>
							</div>
						</div>


					<div class="col-lg-3" style="display:none;">
						<div class="form-group form-group-sm" >
							<label>ICBPER S/.</label>
							<input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="icbper_tot" id="icbper_tot" value="0">
						</div>
					</div>

					<div class="col-lg-3">
						<div class="form-group form-group-sm">
						<label>TOTAL S/.</label>
						<input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="total_venta" id="total_venta" value="0">
						</div>
					</div>
					@if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') ||  Auth::User()->hasRole('caja'))
					<div class="col-lg-3">
						<div class="form-group form-group-sm">
						<label>PAGA CON: </label>
						<input type="number" class="form-control input-lg"  onkeyup="calcular_vuelto();" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any"  name="pagar" id="pagar" value="0">
						</div>
					</div>
					<div class="col-lg-3">
						<div class="form-group form-group-sm">
						<label>VUELTO </label>
						<input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="vuelto" id="vuelto" value="0">
						</div>
					</div>
					@else

					@endif
						
					</div>

					<!-----------------------------------------------------fin segunda columna--------------------------------------------------------------->

					<!--      FIN DE CATEGORIA  -->

				</div>
			</div>
			<input type="hidden" name="mes_id" readonly="readonly" id="mes_id" value="">
			<input type="hidden" name="mes_nom" readonly="readonly" id="mes_nom" value="">
			<input type="hidden" name="pis_id" readonly="readonly" id="pis_id" value="">
			<input type="hidden" name="accion" readonly="readonly" id="accion" value="0">
			<input type="hidden" name="ped_id" id="ped_id" readonly="readonly" id="ped_id">
			<input type="hidden" name="tipo" id="tipo" readonly="readonly" value="">
			<input hidden="hidden" type="date" name="ped_fec" id="ped_fec" value="{{now()->format('Y-m-d')}}">

			<input type="hidden" readonly="readonly" class="form-control" name="_id" id="_id">
			<input type="hidden" readonly="readonly" class="form-control" name="_producto" id="_producto">
				<input type="hidden" readonly="readonly" class="form-control" name="_icbper" id="_icbper">
			<input type="hidden" class="form-control" name="_precio" id="_precio">
			<input type="hidden" readonly="readonly" class="form-control" name="_acompa" id="_acompa">
			<input type="hidden" readonly="readonly" class="form-control" name="_stock" id="_stock">
				<input type="hidden" readonly="readonly"  class="form-control" name="icbper_val" id="icbper_val" value="{{$empresa->icbper}}">
			

			{!!Form::close()!!}
		</div>

		@endsection
