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
          				<font color="white"><center><strong>CONSULTAR COMBOS</strong></center></font>
          				<div class="box-tools pull-right">
                			<a href="/combos/crear"><button  class="btn btn-success btn-sm"> Nuevo</button></a>
              			</div>
          			</div>
	            	<div class="box-body">
	            	 <div class="col-lg-12 col-md-12 col-sm-8 col-xs-8">
      
								@include('empresas.combos.search')
			</div>
				     	</div>
	            </div>
	        </div>
	</div>

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>COMBOS - {{$data_suc->IdEmpresa}} - {{$data_suc->tipo_negocio}}</strong></center></font>
          				
          			</div>
	       	<div class="box-body table-responsive" >
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th>CODIGO</th>
					<th>FAMILIA</th>
					<th>COMBO</th>
					<th>UM</th>
					<th>MONEDA</th>
					<th>PRECIO</th>
					<th>OPCIONES</th>
				</thead>
				
				@foreach ($productos as $pro)
				<tr>
					<td>{{$pro->procod}}</td>
					<td>{{$pro->cat_nom}}</td>
					<td>{{$pro->pronom}}</td>
					<td>{{$pro->umenom}}</td>
					<td>{{$pro->monnom}}</td>
					<td>{{$pro->precio}}</td>
					<td>
						<a href="/combos/editar/{{$pro->IdProducto}}/{{$data_suc->id_empresa_negocio}}"><img src="/icon/editar.png" title="EDITAR" height="30px" width="30px"></a>
            <a href="" data-target="#modal-delete-{{$pro->IdProducto}}" data-toggle="modal"><img src="/icon/error.png" title="ELIMINAR" height="30px" width="30px"></a>
					</td>
				</tr>
				@include('empresas.combos.modal')
				@include('empresas.combos.modalimagen')

				@endforeach
			</table>
		</div>
		{{$productos->render()}}
	</div>
</div>
</div>
</section>
@endsection
