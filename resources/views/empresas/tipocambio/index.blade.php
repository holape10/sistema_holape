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
				<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR TIPO DE CAMBIO  <a href="{{route('tipocambio.create')}}"><button class="btn btn-success"> Nuevo</button></a></h4>
				@include('empresas.tipocambio.buscar')
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
									<th>RUC</th>
									<th>Fecha</th>
									<th>TC Compra</th>
									<th>TC Venta</th>
									<th>Opciones</th>
								</tr>
							</thead>
							<tbody>
								@foreach ($list_tc as $tc)
								<tr>
									<td>{{$tc->IdEmpresa}}</td>
									<td>{{$tc->FecTipCambio}}</td>
									<td>{{$tc->CamCompra}}</td>
									<td>{{$tc->CamVenta}}</td>
									<td>
										<a href="{{URL::action('TipoCambioController@edit',$tc->IdTipCambio)}}"><button class="btn btn-info">Editar</button></a>
									</td>
								</tr>
							
								@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>	
			</div>
		</div>
	</section>

@endsection