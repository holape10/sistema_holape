@extends('layouts.reportes')
@section('contenido')



<section class="content">

        
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-body">
					<table id="tblCompra"  class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Producto</th>
								<th>Cantidad</th>
								<th>Promedio</th>
								
							
							</tr>
						</thead>
						<tbody>
							@foreach($compras as $comp)
							<tr>
								<td>{{$comp->pronom}}</td>
								<td>{{$comp->cantidad}}</td>
								<td>{{$comp->cantidad/$diferencia}}</td>
								
								
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