@extends('layouts.empresas')
@section('contenido')

	<section class="content">
	
	<div class="container-fluid">
		<div class="col-lg-12">
			<div class="btn-toolbar" role="toolbar" aria-label="...">
				<div class="btn-group" >
			
						<a href="/compras"><button type="button"  class=" btn btn-success btn-sm"><span class="glyphicon glyphicon-search"></span> Consultar Ordenes de Compra</button></a>
				</div>
			</div>
		</div>
	</div>
	<br>

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
						<table id="tblCompra"  class="table table-bordered table-hover">
							<thead>
								<tr>
								<th>Cant.</th>
                                <th>Unidad</th>
                               
                                <th>Detalle</th>
                               
								<th>P.U Compra</th>
                                <th>Total</th>
								</tr>
							</thead>
							<tbody>
								@foreach($compra as $comp)
								<tr>
								 	<td>{{$comp->cantidad}}</td>
								 	<td>{{$comp->umenom}}</td>
								 	
								 	<td>{{$comp->pronom}}</td>
								
								 	<td>{{$comp->pre_uni}}</td>
								 	
								 	<td>{{$comp->total}}</td>
								 	
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