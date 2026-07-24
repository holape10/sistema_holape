 <script type="text/javascript">
 		
 			$(document).ready(function(){
 		$("#ped_cli_nom").focus();   
});
 		$("#btnPedidosLlevar").click(function(){
			
			buscar_pedidos_llevar_delivery(3);
			$("#modal-pedidos-llevar").modal("show");

		});

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



		function buscar_pedidos_llevar_delivery(tipo){
			

			$.ajax({
				type: "GET",
				dataType: 'json',
				url: "/buscarpedidos/"+tipo,
			}).done(function(respuesta){
				$("#listar_pedidos_llevar").html(respuesta.vista);
			});


		}

 </script>
 <div class="col-lg-12">
 	<div class="form-group form-group-sm">
 		<button type="button"  id="btnPedidosLlevar" class="btn btn-success btn-md btn-block"><strong>VER PEDIDOS</strong></button>
 		
 	</div>
 </div>
 <div class="col-lg-4">
 	<div class="form-group form-group-sm">
 		<label>TIPO DOC.</label>
 		<select name="tdicod" id="tdicod" class="form-control">
 			@foreach($documentos as $doc)
 		 <option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
 		@endforeach
 		</select>
 		
 	</div>
 </div>

  <div class="col-lg-8">
 	<div class="form-group form-group-sm">
 		<label>DNI/RUC.</label>
 		<div class="input-group input-group-sm">
 		<input type="text" name="ped_num_doc" id="ped_num_doc" onKeypress="if(event.keyCode == 13) buscarcliente();" class="form-control input input-sm" value="00000000">
 		<span class="input-group-btn">
										<button type="button" class="btn btn-primary btn-flat" onclick="buscarcliente();"><span class="fa fa-search"></span></button>										
									</span>
 	</div>
 	</div>
 </div>

  <div class="col-lg-6">
 	<div class="form-group form-group-sm">
 		<label>CLIENTE</label>
 		     <div class="input-group input-group-sm">
        <input name="ped_cli_nom" id="ped_cli_nom" value="" class=" form-control input-xs" onkeypress="if(event.keyCode == 13) buscarclientenombre();">
        <span class="input-group-btn">
          <button type="button" class="btn btn-primary btn-flat" onclick="buscarclientenombre();"><span class="fa fa-search"></span></button>
        </span>
      </div>

 	</div>
 </div>
   <div class="col-lg-6">
 	<div class="form-group form-group-sm">
 		<label>TELÉFONO/LLEVAR</label>
 		  <div class="input-group input-group-sm">
        <input name="ped_tel" id="ped_tel" value="" class=" form-control input-xs" onkeypress="if(event.keyCode == 13) buscarclientetelefono();">
        <span class="input-group-btn">
          <button type="button" class="btn btn-primary btn-flat" onclick="buscarclientetelefono();"><span class="fa fa-search"></span></button>
        </span>
      </div>
 	</div>
 </div>

  <div class="col-lg-12">
 	<div class="form-group form-group-sm">
 		<label>DIRECCIÓN</label>
 		<input type="text" name="ped_dir" id="ped_dir" class="form-control input input-sm" value="">
 	</div>
 </div>
 <div class="col-lg-12">
    <div class="form-group form-group-sm">
        <label>DELIVERY</label>
        <input type="text" name="motorizado" id="motorizado"   class="form-control input input-sm" value="">
    </div>
 </div>
  <div class="col-lg-12">
 	<div class="form-group form-group-sm">
 		<label>OBSERVACIONES</label>
 		<textarea class="form-control" name="ped_obs" id="ped_obs" rows="4"></textarea>
 		
 	</div>
 </div>

