@extends('layouts.empresas')
@section('contenido')


	<section class="content">
	<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
	    @if(session()->has('info'))
	    	<div class="alert alert-danger">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Alerta!</strong> {{ session('info') }}
			</div>
	    @endif


	    @if(session()->has('success'))
	    	<div class="alert alert-success">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Información!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>
<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            		<div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR SERIES <a href="{{route('series.create')}}"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.series.buscar')
			</div>
		     	</div>
	            </div>
	        </div>
	</div>            
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
						<table id=""  class="table table-striped table-bordered table-condensed table-hover">
							<thead>
								<tr>
									<th hidden="hidden">Id Serie</th>
									<th>RUC</th>
									<th>Tipo Documento</th>
									<th>Serie</th>
									<th>Correlativo</th>
									<th>Estado</th>
									<th>Opciones</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($list_series as $series)
								<tr>
									<td hidden="hidden">{{$series->IdSerie}}</td>
									<td>{{$series->IdEmpresa}}</td>
									<td>{{$series->tdodes}}</td>
									<td>{{$series->Numero_Serie}}</td>
									<td>{{$series->Num_Correlativo}}</td>
									@if($series->Estado =='1')
									<td>Activo</td>
									@else
									<td>Inactivo</td>
									@endif
									<td>
										<a href="{{URL::action('SeriesController@edit',$series->IdSerie)}}"><button class="btn btn-info">Editar</button></a>
										@if($series->Estado =='1')
										 <a href="" data-target="#modal-delete-{{$series->IdSerie}}-{{$series->Estado}}" data-toggle="modal"><button class="btn btn-danger">Desactivar</button></a><br>
										@else
				                         <a href="" data-target="#modal-delete-{{$series->IdSerie}}-{{$series->Estado}}" data-toggle="modal"><button class="btn btn-success">ACTIVAR</button></a><br>
				                        @endif

									</td>
								</tr>
								@include('empresas.series.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection