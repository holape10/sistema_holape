@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')

<script type="text/javascript">
	
	$(document).ready(function(){

		limpiarpedido();

		

		$("#btnPrecuenta").click(function(){

			var ped_id = $("#ped_id").val();
	
    		$("<iframe>")                             
        	.hide()                              
        	.attr("src", "/precuenta/"+ped_id) 
        	.appendTo("body");                   
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
			 	$("#mes_id_act").val(mesa_actual);
			 	$("#ped_id_act").val(ped_id_actual);
			 	$("#mes_act").val(mesa_nom_actual);

			 	$.ajax({
					type: "GET",
					dataType: 'json',
					url: '/buscarmesasdesocupadas',
				}).done(function(respuesta){
					$("#modal-unir-mesas").modal("show");	
					$("#mesas_desocupadas_unir").html(respuesta.vista);	

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

	function agregar_item(id,producto,precio){

		var validar = checkId(id);

		
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

	function calcular_total(){

		var total = 0;

		$("#tbl_detalle tbody tr").each(function(){

		  total = total + parseFloat($(this).find("td:eq(2)> input").val()*$(this).find("td:eq(4)>input").val());
		  
		   
		})


		$('#total_venta').val(total.toFixed(2));

	}

	
</script>

<br>


<div class="container-fluid">
	{!!Form::open(array('url'=>'/registrar','autocomplete'=>'off','method'=>'POST','name'=>'frmcomandas','id'=>'frmcomandas','role'=>'form','files'=>'true'))!!}
	{{Form::token()}}
	<div class="box">
		<div class="box-body">
			<div class="row" style="text-align:center;">
				<div class="col-lg-12">
					<div class="form-group form-group-sm">

						<div class="btn-group" style="margin-bottom:10px;">
							<button type="button" name="btnComanda" id="btnComanda"  class=" btn btn-sm btn-success" ><strong>COMANDAR</strong></button>
						</div>
						<div class="btn-group" style="margin-bottom:10px;">
							 <button type="button" name="btnPrecuenta" id="btnPrecuenta"  class=" btn btn-sm btn-info" ><strong>PRECUENTA</strong></button>
						</div>
						<div class="btn-group" style="margin-bottom:10px;">
							 <button type="button" name="btnImpComanda" id="btnImpComanda"  class=" btn btn-sm btn-dafault" ><strong>IMPRIMIR COMANDA</strong></button>
						</div>
						<div class="btn-group" style="margin-bottom:10px;">
							<button type="button" name="btnCambiar" id="btnCambiar" class=" btn btn-sm btn-primary" ><strong>CAMBIAR MESA</strong></button>
						</div>
						<div class="btn-group" style="margin-bottom:10px;">
							<button type="button" name="btnElimPed" id="btnElimPed" class=" btn btn-sm btn-danger" onclick="eliminar_pedido();" ><strong>ELIMINAR PEDIDO</strong></button>
						</div>
						<div class="btn-group" style="margin-bottom:10px;">
							<button type="button" name="btnUnirMesas" id="btnUnirMesas" class=" btn btn-sm btn-danger" ><strong>UNIR MESA</strong></button>
						</div>
						<div class="btn-group" style="margin-bottom:10px;">
							<button type="button" name="btnLimpiar" id="btnLimpiar" class=" btn btn-sm btn-warning" onclick="limpiarpedido();" ><strong>LIMPIAR</strong></button>
						</div>

					</div>
				</div>
			</div>

			<div class="row">
				<!-------------------------------------------------------primera columna---------------------------------------------->
				<div class="col-lg-3">
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
									<button type="button" id="btnDelivery" class="btn btn-primary btn-md"><font><strong>DELIVERY</strong></font></button>
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


								<button type="button" class="btn" style="width:80px;height:80px; background:#E74C3C;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
									<strong >{{$mesas->mes_nom}}</strong>
								</button>

								

								@elseif($mesas->mes_est=='Libre')

								<button type="button" class="btn" style="width:80px;height:80px;background:#52BE80;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}','{{$mesas->mes_nom}}')">
									<strong>{{$mesas->mes_nom}}</strong>
								</button>



								@else

								<button type="button" class="btn" style="width:80px;height:80px;  background:#F4D03F;color:#FDFEFE;">
									<br><font style="color:#FDFEFE"><strong >{{$mesas->mes_nom}}</strong></font>
								</button>


								@endif
								<BR>				
							</div>
							@endforeach
							@endif
						</div>
					</div>
				</div>
				<!----------------------------------------------------fin primera columna----------------------------------------------------------------->

				<!-----------------------------------------------------inicio segunda columna----------------------------------------------------------->

				<div class="col-lg-6">
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
											<td colspan="4">
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
						<div class="col-lg-9">
							<div class="form-group form-group-sm">
								<label>TOTAL S/.</label>
								<input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="total_venta" id="total_venta" value="0">
							</div>
						</div>
						
					</div>

					<!-----------------------------------------------------fin segunda columna--------------------------------------------------------------->


					<!-----------------------------------------------------inicio tercer columna----------------------------------------------------------->

					<div class="col-lg-3">
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
			</div>
			<input type="hidden" name="mes_id" readonly="readonly" id="mes_id" value="">
			<input type="hidden" name="mes_nom" readonly="readonly" id="mes_nom" value="">
			<input type="hidden" name="pis_id" readonly="readonly" id="pis_id" value="">
			<input type="hidden" name="accion" readonly="readonly" id="accion" value="0">
			<input type="hidden" name="ped_id" id="ped_id" readonly="readonly" id="ped_id">
			<input type="hidden" name="tipo" id="tipo" readonly="readonly" value="">
			<input hidden="hidden" type="date" name="ped_fec" id="ped_fec" value="{{now()->format('Y-m-d')}}">
			{!!Form::close()!!}
		</div>

		@endsection
