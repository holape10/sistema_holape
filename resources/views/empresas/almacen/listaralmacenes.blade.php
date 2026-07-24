@extends('layouts.empresas')
@section('contenido')
<script>

   $(document).ready(function(){

   	   	$("#sucursal").on("change", function() {

   	   		var sucursal = $("#sucursal").val(); 
	   
	            window.location.href = "/almacen/listaralmacenes/"+sucursal;
	         
	    });

	    $(".predeterminado").on("click", function() {
	        
	          var almacen = $(this).val();
	 		  var sucursal = $("#sucursal").val();    
	 		  
	 		   	$("#imgload").show();
      			$("#divlistado").hide(); 
	          $.ajax({
	            type: "GET",
	            dataType: 'json',
	            url: '/almacenpredeterminada/'+almacen+'/'+sucursal,
	            
	          }).done(function(respuesta){

	          	$("#imgload").hide();
            	$("#divlistado").show();
	          	alert(respuesta.mensaje);
	     
	          });

        });


   });

</script>

	<section class="content">
	<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">

	              
    	<div class="row">
        	<div class="col-xs-12">
        	
          		<div class="box">

          			<div class="box-header" style="background-color:#337ab7;">
          				<center><font color="white"><strong>ALMACENES<br>{{$datos->IdEmpresa}} - {{$datos->tipo_negocio}}</strong></font></center>
          				<div class="box-tools pull-right">
                			<a href="/crearalmacenes"><button  class="btn btn-success btn-lg"> Nuevo</button></a>
              			</div>
          			</div>
          			<div class="box-header with-border">
          				<div class="row">
          					<div class="col-lg-3">
          						<div class="form-group form-group-sm">
          							<label>Sucursales</label>
          							<select name="sucursal" id="sucursal" class="form-control">
          								@foreach($negocios as $suc)
	          								@if($suc->id_empresa_negocio == $sucursal)
	          									<option selected="selected" value="{{$suc->id_empresa_negocio}}">{{$suc->tipo_negocio}}</option>
	          								@else
	          									<option value="{{$suc->id_empresa_negocio}}">{{$suc->tipo_negocio}}</option>
	          								@endif
          								@endforeach
          							</select>
          						</div>
          					</div>
          				</div>
          			</div>
          				<center><img style="display:none;" width="80px" height="80px" src="/img/load.gif" name="imgload" id="imgload"></center>
	            	<div class="box-body" id="divlistado">
						<table id="tblCompra"  class="table table-bordered table-hover">
							<thead>
								
								<tr>
									<th>EMPRESA</th>
									<th>ALMACEN</th>
									<th>TIENDA</th>
									<th>OPCIONES</th>
								</tr>
							</thead>
							<tbody>
								@foreach($almacenes as $almacen)
								<tr>
									<td>{{$almacen->IdEmpresa}} - {{$almacen->tipo_negocio}}</td>
								 	<td>{{$almacen->descripcion}}</td>
								 	<td>
								 	@if($almacen->predeterminado =='1')
									<div class="form-check">
									  <input class="form-check-input predeterminado" type="radio" checked="checked" name="predeterminado" id="predeterminado" value="{{$almacen->id_almacen}}"  >
									  
									</div>
									@else
 										<div class="form-check">
									  <input class="form-check-input predeterminado" type="radio" name="predeterminado" id="predeterminado" value="{{$almacen->id_almacen}}"  >
									  
									</div>
									@endif
								</td>
								 	<td>
										<a href="/editaralmacenes/{{$almacen->id_almacen}}"><button class="btn btn-info">Editar</button></a>
									
										 <a href="" data-target="#modal-delete-{{$almacen->id_almacen}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
									</td>
									
									
								</tr>
								@include('empresas.almacen.eliminaralmacen')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection