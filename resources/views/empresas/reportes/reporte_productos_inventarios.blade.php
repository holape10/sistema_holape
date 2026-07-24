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
								<th>ID</th>
								<th>CODIGO</th>
								<th>PRODUCTO</th>
								<th>UNIDAD_MEDIDA</th>
								<th>STOCK_INICIAL</th>
								<th>STOCK_ACTUAL</th>
								<th>PRECIO PUBLICO</th>
								<th>PRECIO MAYOR</th>
								<th>PRECIO ESPECIAL</th>
								<th>CANTIDAD</th>
								<th>COSTO</th>
								
								
							</tr>
						</thead>
						<tbody>
							@foreach($productos as $pro)
							<tr>
								<td>{{$pro->IdProducto}}</td>
								<td>{{$pro->procod}}</td>
								<td>{{$pro->pronom}}</td>
								<td>{{$pro->umecod}}</td>
								<td>{{$pro->stock_inicial}}</td>
								<td>{{$pro->stock}}</td>
								<td>{{$pro->precio}}</td>
								<td>{{$pro->precio2}}</td>
								<td>{{$pro->precio3}}</td>
								<td></td>
								<td>{{$pro->costo}}</td>
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