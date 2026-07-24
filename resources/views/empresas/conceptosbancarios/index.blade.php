@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR CONCEPTO BANCARIO<a href="conceptosbancarios/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.conceptosbancarios.search')
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
					<!--<th>CODIGO</th>-->
					<th>CONCEPTO BANCARIO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($conceptos as $concepto)
				<tr>
					<!--<td>{{$concepto->concepto_id}}</td>-->
					<td>{{$concepto->concepto_nom}}</td>
					<td>
						<a href="{{URL::action('ConceptosBancariosController@edit',$concepto->concepto_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$concepto->concepto_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.conceptosbancarios.modal')
				@endforeach
			</table>
		</div>
		{{$conceptos->render()}}
	</div>
</div>
</div>
</section>
@endsection
