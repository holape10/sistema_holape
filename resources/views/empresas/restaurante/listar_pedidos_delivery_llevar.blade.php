<script type="text/javascript">
		function consultar_pedido_llevar_delivery(id){

		$.ajax({
			type: "GET",
			dataType: 'json',
			url: "/buscarpedidollevardelivery/"+id,
		}).done(function(respuesta){
			if(respuesta.estado=='1'){

				$("#listar_pedido").html(respuesta.vista);
				$("#ped_num_doc").val(respuesta.cabecera['ped_num_doc']);
				$("#tdicod").val(respuesta.cabecera['tdicod']);
				$("#ped_cli_nom").val(respuesta.cabecera['ped_cli_nom']);
				$("#ped_dir").val(respuesta.cabecera['ped_dir'])
				$("#ped_tel").val(respuesta.cabecera['ped_tel'])
				$("#ped_id").val(respuesta.cabecera['ped_id']);
				$("#ped_ref").val(respuesta.cabecera['ped_ref']);
				$("#motorizado").val(respuesta.cabecera['motorizado']);
				$("#ped_pag_efe").val(respuesta.cabecera['ped_pag_efe']);
				$("#ped_obs").val(respuesta.cabecera['ped_obs']);
				$("#ped_fec").val(respuesta.cabecera['ped_fec']);
				if(respuesta.cabecera['ped_pag_tar']=='1'){
					$("#ped_pag_tar").prop('checked', true);
				}else{
					$("#ped_pag_tar").prop('checked', false);
				}

				if(respuesta.cabecera['ped_fac']=='1'){
					$("#ped_fac").prop('checked', true);
				}else{
					$("#ped_fac").prop('checked', false);
				}
				$("#accion").val("1");

			}else{
				$('#items_pedidos').empty();
				$("#accion").val("0");
				$("#ped_id").val("");
        	  	//alert('no encontrado');

        	  }
		calcular_total();
        	});

			$("#modal-pedidos-delivery").modal("hide");
			$("#modal-pedidos-llevar").modal("hide");


	}

</script>
<div class="row">
	<div class="col-lg-12 col-sm-12 col-md-12 col-xs-12" style="padding-top:10px;">
		<table class="table table-hover table-bordered table-striped">
			<thead style="background-color:#3c8dbc;color:#ffff;">
				<th><center>N° PEDIDO</center></th>
				<th><center>CLIENTE</center></th>
				<th><center>DIRECCION</center></th>
				<th><center>TELÉFONO</center></th>
				<th><center>REFERENCIA</center></th>
				<th><center>OPCIONES</center></th>
				<th><center>ENTREGAR</center></th>
			</thead>
			<tbody>
				@if(!empty($pedidos))
					@foreach($pedidos as $ped)
						<tr>
							<td>{{$ped->ped_id}}</td>
							<td>{{$ped->ped_cli_nom}}</td>
							<td>{{$ped->ped_dir}}</td>
							<td>{{$ped->ped_tel}}</td>
							<td>{{$ped->ped_ref}}</td>
							<td>
							 	<button type="button" onclick="consultar_pedido_llevar_delivery('{{$ped->ped_id}}')" class="btn btn-success" style="margin-left:5px;margin-top:5px;" title="EDITAR"><span class="fa fa-edit fa-lg"></span></button>
							 			<a href="/cobrarmesa/{{$ped->ped_id}}"><button type="button" class="btn btn-primary" style="margin-left:5px;margin-top:5px;" title="COBRAR"><span class="fas fa-money-bill fa-lg"></span></button></a>
							</td>
							<td style="text-align:center;margin-left:5px;margin-top:5px;">
								@if($ped->est_ped_id=='10')
									<a href="" data-target="#modal-entregar-{{$ped->ped_id}}" data-toggle="modal" ><img title="Entregar" width="35px" height="35px" src="/icon/entregado.png"></a>
								@else
									<img style="opacity: 0.5;" title="Entregar" width="35px" height="35px" src="/icon/entregado.png">
								@endif	
							</td>
						</tr>
						@include('empresas.puntosventas.modalentregar')
						@endforeach
					@endif
			</tbody>
		</table>
	</div>
</div>

<!--<div style="width:120px;height:120px; border-radius:6px; background:#989898;vertical-align:middle;font-size:10pt;">
					<button type="button" onclick="consultar_pedido_llevar_delivery('{{$ped->ped_id}}')" class="btn btn-success" style="position:absolute;margin-left:5px;margin-top:5px;"><span class="fa fa-edit fa-lg"></span></button>
					<br><br><br><font color="white"><strong><center>{{$ped->ped_cli_nom}}<br>S/. {{$ped->ped_tot}}</center></strong></font>
				</div>-->