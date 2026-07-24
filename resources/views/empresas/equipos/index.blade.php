@extends('layouts.empresas')
@section('contenido')

<script>
$(document).ready(function()
{       

	 $("#btnExcel").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
        
        
		

	      		$("#cmbCatId").change(function() {
         
	                var cat_id = $("#cmbCatId").val();
	                $("#subcat_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
	                $.ajax({
	                  type: "GET",
	                  dataType: 'json',
	                  url: "/buscarsubcategorias/"+cat_id,

	                }).done(function(respuesta){
	                $("#subcat_id").html(respuesta.vista);
	               
	                });

	                var subcat_id = $("#subcat_id").val();
		                $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
		                $.ajax({
		                  type: "GET",
		                  dataType: 'json',
		                  url: "/buscartipos/"+subcat_id,

		                }).done(function(respuesta){
		                $("#tip_pro_id").html(respuesta.vista);
		               
		                });
		        
		        

                 });


                $("#subcat_id").change(function() {
         
                var subcat_id = $("#subcat_id").val();
                $("#tip_pro_id").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscartipos/"+subcat_id,

                }).done(function(respuesta){
                $("#tip_pro_id").html(respuesta.vista);
               
                });
        

                 });

          


   

 });

</script>
<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>CONSULTAR EQUIPOS</strong></center></font>
          				<div class="box-tools pull-right">
                			<a href="equipos/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
          			</div>
	            	<div class="box-body">
	            	 <div class="col-lg-12 col-md-12 col-sm-8 col-xs-8">
				<!--<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR PRODUCTOS <a href="productos/create"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
                 </h4>-->
    
				@include('empresas.equipos.search')
			</div>
				     	</div>
	            </div>
	        </div>
	</div>

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>EQUIPOS - {{$data_suc->IdEmpresa}} - {{$data_suc->tipo_negocio}}</strong></center></font>
          				
          			</div>
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<!--<th>TIPO PRODUCTO</th>-->
					<th>CODIGO</th>
					<th>FAMILIA</th>
					<th>SUBFAMILIA</th>
					<th>TIPO PRODUCTO</th>
					<th>MARCA</th>
					<th>EQUIPO</th>
				
					<th>MODELO</th>
					<th>UM</th>
				
					<th>MONEDA</th>
					<th>P. UNITARIO</th>
					<th>IMAGEN</th>
				
					<th>OPCIONES</th>
				</thead>
				
				@foreach ($productos as $pro)
				<tr>
					<!--<td>@if($pro->promocion =='0') PRODUCTO @elseif($pro->promocion =='1') COMBO @elseif($pro->promocion =='2') PREPARADOS @elseif($pro->promocion =='4') INSUMO @endif</td>-->
					<td>{{$pro->procod}}</td>
					<td>{{$pro->cat_nom}}</td>
					<td>{{$pro->subcat_nom}}</td>

					<td>{{$pro->tip_pro_nom}}</td>
					<td>{{$pro->mar_nom}}</td>
					<td>{{$pro->pronom}}</td>
				
					<td>{{$pro->modelo}}</td>
					<td>{{$pro->umenom}}</td>
					
					<td>{{$pro->monnom}}</td>
					<td>{{$pro->precio}}</td>
					<td>
						  <a href="" data-target="#modal-imagen-{{$pro->IdProducto}}" data-toggle="modal"><button class="btn btn-sm btn-primary">Ver Imagen</button></a>
					</td>
				
					<td>

							<a href="/editarequipo/{{$pro->IdProducto}}/{{$data_suc->id_empresa_negocio}}"><img src="/icon/editar.png" title="EDITAR" height="30px" width="30px"></a>
							<!--<a href="/presentaciones/{{$pro->IdProducto}}"><button class="btn btn-sm btn-primary">Presentaciones</button></a>-->
						

                   		

                     <a href="" data-target="#modal-delete-{{$pro->IdProducto}}" data-toggle="modal"><img src="/icon/error.png" title="ELIMINAR" height="30px" width="30px"></a>

					</td>
				</tr>
				@include('empresas.equipos.modal')
				@include('empresas.equipos.modalimagen')

				@endforeach
			</table>
		</div>
		{{$productos->render()}}
	</div>
</div>
</div>
</section>
@endsection
