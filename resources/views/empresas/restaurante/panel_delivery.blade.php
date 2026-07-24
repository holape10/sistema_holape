  <script type="text/javascript">
 	  
  
		$("#btnPedidosDelivery").click(function(){
			
			buscar_pedidos_llevar_delivery(2);
			$("#modal-pedidos-delivery").modal("show");

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
				$("#listar_pedidos_delivery").html(respuesta.vista);
			});

		}

 </script>
 <div class="col-lg-12">
 	<div class="form-group form-group-sm">
 		<button type="button" id="btnPedidosDelivery" class="btn btn-success btn-md btn-block"><strong>VER PEDIDOS</strong></button>

 		
 	</div>
 </div>

   <!--<div class="col-lg-6">
    <div class="form-group form-group-sm">
        <button type="button" id="btnPedidosDelivery" class="btn btn-success btn-md btn-block"><strong>VER PEDIDOS</strong></button>

               <a href="/delivery" target="_blank"> <button type="button" class="btn btn-success btn-md btn-block"><strong>LISTAR PEDIDOS</strong></button></a>
        
    </div>
 </div>-->

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
 		<label>TELÉFONO/DELIVERY</label>
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
 		<label>REFERENCIA</label>
 		<input type="text" name="ped_ref" id="ped_ref" class="form-control input input-sm" value="">
 	</div>
 </div>

  <div class="col-lg-6">
    <div class="form-group form-group-sm">
        <label>DELIVERY</label>
        <input type="text" name="motorizado" id="motorizado"   class="form-control input input-sm" value="">
    </div>
 </div>
  <div class="col-lg-6">
 	<div class="form-group form-group-sm">
 		<label>PAGA EFECTIVO CON:</label>
 		<input type="number" step="any" name="ped_pag_efe" id="ped_pag_efe" class="form-control input input-sm" value="">
 	</div>
 </div>

  <div class="col-lg-12">
 	<div class="form-group form-group-sm">
 		<label>PAGA CON TARJETA</label>
 		<input type="checkbox" name="ped_pag_tar" id="ped_pag_tar" value="1">
 	</div>
 </div>

 <div class="col-lg-12">
 	<div class="form-group form-group-sm">
 		<label>QUIERE FACTURA?</label>
 		<input type="checkbox"  name="ped_fac" id="ped_fac" value="1" >
 	</div>
 </div>

  <div class="col-lg-12">
 	<div class="form-group form-group-sm">
 		<label>OBSERVACIONES</label>
 		<textarea class="form-control" name="ped_obs" id="ped_obs" rows="4"></textarea>
 		
 	</div>
 </div>



