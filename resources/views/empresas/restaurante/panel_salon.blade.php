

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


	function elegir_mesa(mesa,id,nombre){

		var piso =  $("#piso option:selected").text();
		var pis_id = $("#piso option:selected").val();

		$("#mes_id").val(id);
		$("#mes_nom").val(nombre);
		$("#pis_id").val(pis_id);

		$("#lbl_pis_mes").text(piso+' / '+mesa);

		consultar_mesa_pedido(id);

	}

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


	function eliminar_item(btn) {
		var row = btn.parentNode.parentNode;
		row.parentNode.removeChild(row);

		calcular_total();
		
	};


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


	function eliminar_item_registrado(btn,item) {

		eliminar_item_pedido(item);

		var row = btn.parentNode.parentNode;
		row.parentNode.removeChild(row);

		calcular_total();

	};



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


	function calcular_total(){

		var total = 0;

		$("#tbl_detalle tbody tr").each(function(){

		  total = total + parseFloat($(this).find("td:eq(2)> input").val()*$(this).find("td:eq(4)>input").val());
		  
		   
		})


		$('#total_venta').val(total.toFixed(2));

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
			
</script>

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

		<button type="button" class="btn" style="width:80px;height:80px;background:#52BE80;color:#FDFEFE;" onclick="elegir_mesa('{{$mesas->mes_nom}}','{{$mesas->mes_id}}')">
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