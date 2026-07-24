@extends('layouts.empresas')
@section('contenido')
<script>

   $(document).ready(function(){

    	$(".predeterminado").on("click", function() {
	        
	          var medio = $(this).val();

	        

	          $.ajax({
	            type: "GET",
	            dataType: 'json',
	            url: '/mediopredeterminado/'+medio,
	            
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
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> MEDIOS DE PAGOS <a href="/mediospagos/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.mediospagos.search')
			</div>
				     	</div>
	            </div>
	        </div>
	</div>

<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					
					<th>MEDIO PAGO</th>
					<th>CUENTA BANCARIA</th>
					<th>CONCEPTO BANCARIO</th>
					<th>COMISION %</th>
					<th>PREDETERMINADO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($mediospagos as $mp)
				<tr>
					
					<td>{{$mp->nom_med_pag}}</td>
					<td>{{strtoupper($mp->ban_nom)}} @if(!empty($mp->tiP_cuen_nom))- CUENTA {{strtoupper($mp->tip_cuen_nom)}} @endif {{strtoupper($mp->monnom)}} {{strtoupper($mp->cuen_ban_num)}}</td>
					<td>{{$mp->concepto_nom}}</td>
					<td>{{$mp->comision}}</td>
					<td>
						@if($mp->predeterminado =='1')
							<div class="form-check">
							  <input class="form-check-input predeterminado" type="radio" checked="checked" name="predeterminado" id="predeterminado" value="{{$mp->id_med_pag}}"  >		  
							</div>
						@else
 							<div class="form-check">
								<input class="form-check-input predeterminado" type="radio" name="predeterminado" id="predeterminado" value="{{$mp->id_med_pag}}"  >
									  
							</div>
						@endif
					</td>
					<td>
						<a href="{{URL::action('MediosPagosController@edit',$mp->id_med_pag)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$mp->id_med_pag}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.mediospagos.modal')
				@endforeach
			</table>
		</div>
		{{$mediospagos->render()}}
	</div>
</div>
</div>
</section>
@endsection
