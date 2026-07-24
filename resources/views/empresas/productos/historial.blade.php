@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
				<h4><i class='glyphicon glyphicon-search'></i> HISTORIAL DEL PRODUCTO {{$producto->pronom}} 
                      </h4>
              
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
					<th>COMPROBANTE</th>
					<th>CLIENTE</th>
					<th>PRODUCTO</th>
					<th>CANTIDAD</th>
					<th>U.M</th>
				</thead>
				
				@foreach ($detalle as $pro)
				<tr>
					<td>{{$pro->serdoc}}-{{$pro->numdoc}}</td>
					<td>{{$pro->clinom}}</td>
					<td>{{$pro->pronom}}</td>
					<td>{{$pro->cdecan}}</td>
					<td>{{$pro->umenom}}</td>
				</tr>
			
				@endforeach
			</table>
		</div>
		{{$detalle->render()}}
	</div>
</div>
</div>
</section>
@endsection
