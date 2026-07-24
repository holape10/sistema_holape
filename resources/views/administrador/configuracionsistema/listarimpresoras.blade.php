@extends('layouts.empresas')
@section('contenido')
<script>

   $(document).ready(function(){

    	$(".predeterminado").on("click", function() {
	        
	          var impresora = $(this).val();

	          $.ajax({
	            type: "GET",
	            dataType: 'json',
	            url: '/impresorapredeterminada/'+impresora,
	            
	          }).done(function(respuesta){


	            
	     
	          });

        });
    })

</script>
<section class="content">
		<div class="row">
				<div class="col-xs-12">
					<div class="box">
						<div class="box-body">
							<h3>Listado de Impresoras <a href="/impresoras/crear/{{$id_empresa_negocio}}"><button class="btn btn-success">Nuevo</button></a></h3>
							
						</div>
					</div>
				</div>
		</div>  
		<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
						<table id=""  class="table table-bordered table-hover">
							<thead>
								<th>IMPRESORA</th>
								<th>RUTA</th>
								<th>PREDETERMIADO CAJA</th>
								<th colspan="3">Opciones</th>
							</thead>
							@foreach ($impresoras as $impresora)
							<tr>
								<td>{{$impresora->descripcion}}</td>
								<td>{{$impresora->ruta}}</td>
								<td>
									@if($impresora->predeterminado =='1')
									<div class="form-check">
									  <input class="form-check-input predeterminado" type="radio" checked="checked" name="predeterminado" id="predeterminado" value="{{$impresora->Id}}"  >
									  
									</div>
									@else
 										<div class="form-check">
									  <input class="form-check-input predeterminado" type="radio" name="predeterminado" id="predeterminado" value="{{$impresora->Id}}"  >
									  
									</div>
									@endif
								</td>
								<td>
								@if($impresora->descripcion !='CAJA')
									<a href="/impresoras/editar/{{$impresora->Id}}/{{$id_empresa_negocio}}"><button class="btn btn-info">Editar</button></a>
								
									 <a href="" data-target="#modal-delete-{{$impresora->Id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>
									
								@else
										<a href="/impresoras/editar/{{$impresora->Id}}/{{$id_empresa_negocio}}"><button class="btn btn-info">Editar</button></a>
								
									 <a href="" data-target="" data-toggle="modal"><button disabled="disabled" class="btn btn-danger">Eliminar</button></a>
								@endif
								</td>
							
							</tr>
							@include('administrador.configuracionsistema.modaleliminarimpresora')
							@endforeach
						</table>
						</div>	
						{{$impresoras->render()}}
				</div>	
			</div>
		</div>
</section>
@endsection