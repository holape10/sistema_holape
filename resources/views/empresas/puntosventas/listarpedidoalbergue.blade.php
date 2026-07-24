@extends('layouts.empresas')
@section('contenido')

<style>
#b1
{
	/*sirve para los caracteres cuando es una palabra grande se salte a la otra linea */
	white-space: normal;
}
#scroll
{
	height: 650px;
	width: 800px;
	overflow: scroll;
}

.ui-autocomplete {
	z-index: 9999 !important;

}



</style>

<body>


	<script>

		function mostrarservicio(){

			
			var formulario = $("#formservicio").serializeArray();
			$("#divservicios").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
			$.ajax({
				type: "POST",
				dataType: 'json',
				url: "/buscarpedidoalbergue",
				data: formulario
			}).done(function(respuesta){
				$("#divservicios").html(respuesta.vista);
			});

		}

		$(document).ready(function()
		{

				      $("#part_suc").change(function() {

              


                var part_suc = $("#part_suc").val();
                $("#partida").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+part_suc,

                }).done(function(respuesta){
                  $("#partida").html(respuesta.vista);

                });

              });

              $("#almacen").change(function() {


                $("#btnCategorias").click();

              });




			$("#producto").focus();

			$('#clinum').val('00000000');
			$('#clinom').val('Varios');



		});



	</script>

	<script>

		$(document).ready(function()
		{

			$("#formfact").keypress(function(e) {
				if (e.which == 13) {
					return false;
				}
			})




		});

		function mostrar(){
			var id = $("#servicio").val();

			$("#detmenu").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
			$.ajax({
				type: "GET",
				dataType: 'json',
				url: "/consultarservicio/"+id,

			}).done(function(respuesta){
				$("#detalle").html(respuesta.vista);
			});

		}



		function deleteRow(btn) {
			var row = btn.parentNode.parentNode;
			row.parentNode.removeChild(row);
			calculartotal();
		};





		function  buscarcliente(){


			var formulario = $("#clinumn").val();
			$("#imgloadcliente").show();

			$.ajax({
				type: "get",
				dataType: 'json',
				url: '/autocomplete/'+formulario,

			}).done(function(respuesta){



				$('#clinomn').val(respuesta[0].nom);
				$('#clidirn').val(respuesta[0].dir);
				$('#cliteln').val(respuesta[0].telefono);
				$('#clicorn').val(respuesta[0].cor);
				$('#clicodn').val(respuesta[0].clicod);
				$("#tdicodn").val(respuesta[0].tdicod).attr('selected', 'selected');

				$("#imgloadcliente").hide();
				$(".botones").show();

			});



		}





		$("#btnRegCliente").on("click", function() {


			var formulario = $("#frmcliente").serializeArray();
			$("#imgloadcliente").show();
			$(".botonescliente").hide();
			$.ajax({
				type: "POST",
				dataType: 'json',
				url: '/registrarcliente',
				data: formulario,
			}).done(function(respuesta){

				$("#divcliente").html(respuesta.vista);
				$("#imgloadcliente").hide();

				$(".botonescliente").show();
				$('#modal-cliente').modal('toggle');
			});



		});


		function seleccionarcliente(){

			if($('#clicod').find(':selected').attr('data-clinum')==''){
				$('#clinum').val('00000000');
			}else{
				$('#clinum').val($('#clicod').find(':selected').attr('data-clinum'));
			}


			if($('#clicod').find(':selected').attr('data-documento')==''){
				$("#tdicod").val('1');
			}else{
				$("#tdicod").val($('#clicod').find(':selected').attr('data-documento'));
			}



			$('#clinom').val($('#clicod').find(':selected').attr('data-clinom'));
			$('#clidir').val($('#clicod').find(':selected').attr('data-direccion'));
			$('#clicor').val($('#clicod').find(':selected').attr('data-correo'));


			$("#clitel").val($('#clicod').find(':selected').attr('data-telefono'));

			if($('#tdicod').val() =='6' ){
				$('#factura').prop("checked",true);
			}

			if($('#tdicod').val() =='1' ){
				$('#boleta').prop("checked",true);
			}



		}



	</script>




<div class="container-fluid">

	<div class="row">
		{!!Form::open(array('url'=>'/SisFact','autocomplete'=>'off','method'=>'POST','id'=>'formservicio','role'=>'form','files'=>'true'))!!}
		{{Form::token()}}
		<div class="col-lg-12">
			<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><strong><center>{{$datos->tipo_negocio}}</center></strong></font>
					<div class="box-tools pull-right">
						<a href="/pedidoalbergue"><button type="button" class="btn btn-success btn-sm">NUEVO PEDIDO</button></a>
					</div>
				</div>
				
				<div class="box-header with-border form-group-sm">
					<div class="col-lg-2">
						<div class="form-group form-group-sm">
							<label>DESDE</label>
							<input  type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
						</div>
					</div>
					<div class="col-lg-2">
						<div class="form-group form-group-sm">
							<label>HASTA</label>
							<input  type="date" id="fecFin" name="fecFin" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">
						</div>
					</div>
					 <div class="col-lg-3" >
      <div class="form-group form-group-sm">
        <LABEL>SUCURSAL</LABEL>
        <select name="part_suc" id="part_suc" class="form-control">
          @foreach($negocios as $neg)
          <option value="{{$neg->id_empresa_negocio}}">{{$neg->IdEmpresa}} - {{$neg->tipo_negocio}}</option>
          @endforeach
        </select>
      </div>
    </div>

    <div class="col-lg-3" id="partida">
      <div class="form-group form-group-sm">
        <LABEL>ALMACEN</LABEL>
        <select name="almacen" id="almacen" class="form-control">
          @foreach($almacenes as $alm)
          <option value="{{$alm->id_almacen}}"">{{$alm->descripcion}}</option>
          @endforeach
        </select>
      </div>
    </div>

					     <div  class="col-lg-3">

                    <div class="form-group form-group-sm">
                      <label>PROGRAMAS</label>
                       <select style=" font-weight: bold;" class="form-control selectpicker input-sm" onkeypress="if(event.keyCode == 13) mostrarservicio();" onchange="mostrarservicio();" data-show-subtext="true" data-live-search="true" name="prog_id" id="servicio">
                          <option></option>
                          @foreach($programas as $prog)
                          <option style="font-weight:bold;color:black;font-size:10pt;" value="{{$prog->prog_cod}}">{{$prog->prog_cod}}</option>
                          @endforeach
                     </select>
                    </div>
                   
                  </div>
				
			
				</div>
			</div>
		</div>
		{!!Form::close()!!}
	</div>

<div class="row">
		<div class="col-lg-12" id="divservicios">
			<div class="box">
				<div class="box-header" style="background-color:blue;">
					<font color="white"><center><strong>PEDIDOS</strong></center></font>
				</div>
				<div class="box-body">
					<table class="table table-hover">
						<thead>
							<th>FECHA</th>
							<th>SUCURSAL</th>
							<th>ALMACEN</th>
							<th>OPCIONES</th>
						</thead>
						<tbody>
							@foreach($pedidos as $p)
							
							<tr>
								<td>{{$p->fec_ped}}</td>
								<td>{{$p->tipo_negocio}}</td>
								<td>{{$p->descripcion}}</td>
								<td>{{$p->prog_cod}}</td>
								<td>
									<a href="/detallepedidoalbergue/{{$p->ped_ser_id}}"><buttton type="button" class="btn btn-sm btn-success">Detalle</buttton></a>
									 <a href="/modificarpedidosalbergue/{{$p->ped_ser_id}}" data-toggle="modal"><buttton type="button" class="btn btn-sm btn-warning">Modificar</buttton></a> 
								<a data-target="#modal-delete-{{$p->ped_ser_id}}" data-toggle="modal"><buttton type="button" class="btn btn-sm btn-danger">Eliminar</buttton></a>

								<a href="/generarguia/{{$p->ped_ser_id}}" data-toggle="modal"><buttton type="button" class="btn btn-sm btn-primary">Generar Guía</buttton></a></td>
							</tr>
							@include('empresas.puntosventas.modaleliminarpedido')
							@endforeach
						</tbody>
					</table>
				</div>
			</div>
		</div>

	</div>
</div>



@endsection
