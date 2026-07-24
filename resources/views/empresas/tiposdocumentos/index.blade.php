@extends('layouts.empresas')
@section('contenido')


<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR TIPO DOCUMENTO<a href="tiposdocumentos/create"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.tiposdocumentos.search')
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
					<th>DOCUMENTO</th>
					<th>OPCIONES</th>
				</thead>
				@foreach ($documentos as $documento)
				<tr>
				
					<td>{{$documento->doc_nom}}</td>
					<td>
						<a href="{{URL::action('TiposDocumentosController@edit',$documento->doc_id)}}"><button class="btn btn-info">Editar</button></a>
                        <a href="" data-target="#modal-delete-{{$documento->doc_id}}" data-toggle="modal"><button class="btn btn-danger">Eliminar</button></a>

					</td>
				</tr>
				@include('empresas.tiposdocumentos.modal')
				@endforeach
			</table>
		</div>
		{{$documentos->render()}}
	</div>
</div>
</div>
</section>
@endsection
