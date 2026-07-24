@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="container">
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                @if(session()->has('info'))
                   <div class="alert alert-danger">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Alerta!</strong> {{session('info')}}
                    </div>
                @endif


                @if(session()->has('success'))
                    <div class="alert alert-success">
                      <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                      <strong>Información!</strong> {{session('success') }}
                      
                    </div>
                @endif
             
            </div>
        </div>
    </div>
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            	 <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
	           		@if(Auth::user()->local=='1')
						<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR PRODUCTOS CONGELADOS </h4>
					@endif
					@if(Auth::user()->local=='2')
							<h4><i class='glyphicon glyphicon-search'></i> CONSULTAR RESERVA PRODUCTOS </h4>
					@endif
				
				@include('empresas.productos.searchstockreserva')
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
					
					<th>PRODUCTO</th>
					<th>UM</th>
					<th>STOCK</th>
					@if(Auth::user()->local=='1')
						<th>STOCK CONGELADO</th>
					@endif

					@if(Auth::user()->local=='2')
						<th>FECHA RESERVA</th>
						<th>RESERVA UNI.</th>
						<th>RESERVA KILOS</th>
						<th>PRECIO RESERVA</th>
						<th>TOTAL RESERVA</th>
					@endif
					
				
				</thead>
				@foreach ($productos as $pro)
				<tr>
					<td>{{$pro->pronom}}</td>
					<td>{{$pro->umenom}}</td>
					<td>{{$pro->stock}}</td>
					@if(Auth::user()->local=='1')
					<td>{{$pro->stock_congelado}}</td>
					@endif
					@if(Auth::user()->local=='2')
					<td>{{$pro->fecha_reserva}}</td>
					<td>{{$pro->stock_reserva}}</td>
					<td>{{$pro->stock_reserva_kilos}}</td>
					<td>{{$pro->precio_reserva}}</td>
					<td>{{$pro->total_reserva}}</td>
				
					@endif
				</tr>

			
				@endforeach
			</table>
		</div>
		{{$productos->render()}}
	</div>
</div>
</div>
</section>
@endsection
