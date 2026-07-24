@extends('layouts.empresas')
@section('contenido')
@include('empresas.restaurante.modalcambiarmesa')
@include('empresas.restaurante.modal_pedidos_llevar')
@include('empresas.restaurante.modal_pedidos_delivery')
@include('empresas.puntosventas.modaldirecciones')
@include('empresas.puntosventas.modalclientes')


<script type="text/javascript">
	
	$(document).ready(function(){

		var comprobante = $("#comprobante").val();
		var documento = $("#documento").val();
		   $("#btnPrint").printPage({

		    url: "/voucher/"+comprobante,
		    attr: "href",
		    messageBox:false

		})
		   

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
				url: '/actualizarventa',
				data: formulario,
			}).done(function(respuesta){

				if(respuesta.estado =='error'){

					alert(respuesta.mensaje);

					$("#imgload").hide();
					$(".botones").show();

				}else{

					window.location.href = "/SisFact/"+respuesta.codfact;


				}



			});

		});


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
			'<td><input type="number" style="text-align:center;" step="any" class="form-control" name="txt_cantidad[]" value="1" min="1"></td>'+
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

		var total = 0;
		var total_icbper = 0;
		var icbper_val = $("#icbper_val").val();



		$("#tbl_detalle tbody tr").each(function(){

			if($(this).find("td:eq(6)> input").val()==1){
				 total_icbper = total_icbper + parseFloat($(this).find("td:eq(2)> input").val()*icbper_val);
			}

		  	total = total  + parseFloat($(this).find("td:eq(2)> input").val()*$(this).find("td:eq(4)>input").val());
		  
		   
		})

		

			total = parseFloat(total) + parseFloat(total_icbper);

			$('#tot_icbper').val(total_icbper.toFixed(2));

		$('#total_venta').val(total.toFixed(2));

	}

function agregar_medio_pago(){

	var med_pag = $("#med_pag").val();
	var mon_med_pag = $("#mon_med_pag_input").val();
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

	$("#mon_med_pag_input").val('0.00');

	calcular_vuelto();

}


function ElimMedPag(btn) {
	var row = btn.parentNode.parentNode;
	row.parentNode.removeChild(row);

	calcular_vuelto();

};



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

	<div class="row" >
		<div class="col-lg-5">
			<div class="box">
				<div class="box-header" style="background-color:#E8E8E8;">
					<strong><font style="font-size:10pt;font-weight:bold;"><center>DATOS DEL COMPROBANTE</center></font></strong>
					<div class="box-tools pull-right">
						<div class="form-check">
							<label class="form-check-label" for="flexCheckDefault">
								IMPRIMIR
							</label>
							<input class="form-check-input" name="imprimir" type="checkbox" value="1">

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
										@if($comp->tdocod == $cabecera->tdocod)
											<option selected="selected" value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
										@else
											<option value="{{$comp->tdocod}}">{{$comp->tdodes}}</option>
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
										@if($est_pag->cre_dia_id == $cabecera->cre_dia_id)
											<option selected="selected" value="{{$est_pag->cre_dia_id}}" data-medio="{{$est_pag->cre_dia_tip}}" data-dias="{{$est_pag->cre_dia_fac}}">{{$est_pag->cre_dia_nom}}</option>
										@else
											<option value="{{$est_pag->cre_dia_id}}" data-medio="{{$est_pag->cre_dia_tip}}" data-dias="{{$est_pag->cre_dia_fac}}">{{$est_pag->cre_dia_nom}}</option>
										@endif
									@endforeach
								</select>

							</div>
						</div>

						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>F. Emisión</label>
								<input  type="date" id="fecEmi" name="fecEmi" value="{{$cabecera->ccafem}}" class="form-control">

							</div>
						</div>


						<div class="col-lg-3"  id="divfecVen">
							<div class="form-group form-group-sm">
								<label>F. Vencim.</label>
								<input type="date" name="fecVen" id="fecVen" value="{{$cabecera->ccafve}}"  class="form-control">
							</div>
						</div>


						<div class="col-lg-3">
							<div class="form-group form-group-sm">
								<label>POR CONSUMO</label>
								<select class="form-control" name="consumo">
									@if($cabecera->consumo=='1')
										<option value="0">NO</option>
										<option selected="selected" value="1">SI</option>
									@else
										<option selected="selected" value="0">NO</option>
										<option value="1">SI</option>
									@endif
									
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
								<label>Documento</label>
								<select name="tdicod" id="tdicod" class="form-control">
									@foreach($documentos as $doc)
									@if($doc->tdicod == $cabecera->tdicod)
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

								<label>N° Documento</label>
								<div class="input-group input-group-sm">

									<input name="clinum" id="clinum" value="{{$cabecera->ccandi}}" class="form-control" onkeypress="if(event.keyCode == 13) buscarclienteruc();">
									<input type="hidden" name="clicod" id="clicod" value="{{$cabecera->clicod}}"  class="form-control">
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary btn-flat" onclick="buscarclienteruc();"><span class="fa fa-search"></span></button>
										
									</span>
								</div>
							</div>
						</div>




						<div class="col-lg-5">
							<div class="form-group">

								<label>Cliente</label>
								<div class="input-group input-group-sm">

									<input name="clinom" id="clinom" value="{{$cabecera->ccanom}}" class="form-control" onkeypress="if(event.keyCode == 13) buscarclientenombre();">
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

									<input name="clidir" id="clidir" value="{{$cabecera->direccion}}" class="form-control">
									<span class="input-group-btn">
										<button type="button" class="btn btn-primary btn-flat" id="clidiradic" onclick="seleccionardireccion();"><span class="fa fa-search"></span></button>
									</span>
								</div>
							</div>

						</div>


						<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
							<div class="form-group form-group-sm">
								<label>Correo</label>
								<input name="clicor" id="clicor" value="{{$cabecera->clicorcli}}" class="form-control">
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

	        <div class="row">
	        	<div class="col-lg-12">
	        		<label>Observaciones:</label>
	        		<textarea class="form-control" rows="5" name="observaciones">{{$cabecera->ccaobs}}</textarea>
	        	</div>
	        </div>


	    </div>

	</div>
</div>


<div class="col-lg-7">
	<div class="box">
		<div class="box-header" style="background-color:#E8E8E8;">
			<font style="font-size:10pt;">
				
				<strong>
					DETALLE VENTA
				</strong>
			</font>

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

										@foreach($detalle as $det)

									<tr>

										<td hidden="hidden" for="id"><input type="text" readonly="readonly" class="form-control" name="txt_id_producto[]" value="{{$det->IdProducto}}"></td>
											<td><input type="hidden" class="form-control" name="descripcion[]" value="{{$det->cdedes}}">{{$det->cdedes}}</td>
											<td><input type="number" style="text-align:center;" step="any" onkeyup="calcular_total();" class="form-control" name="txt_cantidad[]" value="{{$det->cdecan}}" min="1"></td>
											<td style="text-align:right;" hidden="hidden">{{$det->cdepuni}}</td>
											<td style="text-align:right;" ><input type="number" class="form-control" onkeyup="calcular_total();" step="any" name="precios[]" id="precios[]" value="{{$det->cdepuni}}"></td>
											<td style="text-align:right;"  ><input  class="form-control" type="text"   name="item_obs[]"></td>
											<td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="txt_icbper[]" value="{{$det->icbper}}"></td>
											<td  style="text-align:center;"><button type="button" onClick="eliminar_item_registrado(this,{{$det->IdProducto}});" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td>

</tr>
										@endforeach
									</tbody>
								</table>
							</div>
						</div>

					</div>

					<!-----------------------------------------------------fin segunda columna--------------------------------------------------------------->


					<!-----------------------------------------------------inicio tercer columna----------------------------------------------------------->

					<div class="col-lg-3" >
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
			<input type="hidden" name="IdCpe_cabecera" id="IdCpe_cabecera" readonly="readonly" id="ped_id" value="{{$cabecera->IdCpe_cabecera}}">
			
		</div>
	</div>
</div>

<div class="row">
	<div class="col-lg-12">
		<div class="box">
			<div class="box-header" style="background-color:#E8E8E8;">
				<strong><font style="font-size:10pt;"><center>PAGO</center></font></strong>

			</div>
			<div class="box-body">
				<div class="row">
					<div class="col-lg-2">
						<div class="form-group form-group-sm">
							<label>TOTAL S/.</label>
							<input type="number" class="form-control input-lg" style="height:60px;width:180px;font-size:22pt;font-weight:bold;" step="any" readonly="readonly" name="total_venta" id="total_venta" value="{{$cabecera->ccaitv}}">
						</div>

					</div>

					<div class="col-lg-2">
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
								<input name="mon_med_pag_input" id="mon_med_pag_input" value="0.00" class="form-control">
								<span class="input-group-btn">
									<button type="button" class="btn btn-primary btn-flat" onclick="agregar_medio_pago();"><span class="fa fa-plus-square"> Agregar Pago</span></button>
								</span>
							</div>
						</div>

						<div class="form-group form-group-sm">
							<table class="table table-responsive table-striped table-hover" id="tbl_med_pag">

								<tbody id="tbody_med_pag">
									@foreach($ventas_medios as $vm)
										<tr><td hidden="hidden"><input type="text" readonly="readonly" class="form-control" name="id_med_pag[]" value="{{$vm->id_med_pag}}"></td>
										<td hidden="hidden"><input type="number"  style="text-align:center;width:400px;"  class="form-control" name="mon_med_pag[]" value="{{$vm->monto}}"></td>
										<td hidden="hidden"><input type="text"  style="text-align:center;width:400px;"  class="form-control" name="predeterminado[]" value="{{$vm->predeterminado}}"></td>
										<td><button class="btn btn-success btn-sm btn-block">{{$vm->nom_med_pag}}  S/ {{$vm->monto}}</td>
										<td><button type="button" onClick="ElimMedPag(this);" class="btn btn-danger btn-sm remove"><span class="glyphicon glyphicon-minus"></span></button></td></tr>

									@endforeach
								</tbody>
							</table>
						</div>

					</div>

						<div class="col-lg-2">
			              	<div class="form-group form-group-sm">
			                	<label>PAGA CON:</label>
			                	<input type="number"  step="any" class="form-control" style="height:60px;width:180px;font-size:22pt;font-weight:bold;"  id="pagar" name="pagar" value="{{$cabecera->paga}}" onkeyup="calcular_vuelto();" >
			              	</div>
			          

						<div class="form-group form-group-sm">
							<label>VUELTO</label>
							<input type="text" class="form-control" style="height:60px;width:180px;font-size:22pt;font-weight:bold;"  id="vuelto" name="vuelto" value="{{$cabecera->vuelto}}" readonly="readonly">
						</div>
					</div>

					<div class="col-lg-2">
						<div class="form-group form-group-sm">
							<label>OPERACIONES</label>
							<button type="button" id="btnRegistrar" class="btn btn-success btn-lg btn-block botones">REGISTRAR</button>
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
</div>

</div>
	<input type="hidden" readonly="readonly" class="form-control" name="_id" id="_id">
			<input type="hidden" readonly="readonly" class="form-control" name="_producto" id="_producto">
			<input type="hidden" readonly="readonly"  class="form-control" name="_precio" id="_precio">
				<input type="hidden" readonly="readonly" class="form-control" name="_icbper" id="_icbper">
			<input type="hidden" readonly="readonly" class="form-control" name="_acompa" id="_acompa">
					<input type="text" readonly="readonly"  class="form-control" name="icbper_val" id="icbper_val" value="{{$empresa->icbper}}">

{!!Form::close()!!}
@endsection
