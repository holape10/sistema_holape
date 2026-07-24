<table  id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
				<thead>
					<th colspan="10"><center>STOCK DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}} @if(!empty($datos)) - {{$datos->tipo_negocio}} @endif @if(!empty($datosalm)) - <br>{{$datosalm->descripcion}} @endif</center></th>
				</thead>
				<thead>
					<th>CODIGO</th>
					<th>PRODUCTO</th>
					<th>MARCA</th>
				
					<th>UM</th>
					<th>STOCK INICIAL</th>
					<th>STOCK ACTUAL</th>
					<th>PRECIO VENTA</th>
					<th>COSTO</th>					
					<th>VALOR INVENTARIO</th>
					<th>OPCIONES</th>
			
				</thead>
				@foreach ($productos as $pro)
				<tr>
					<td>{{$pro->procod}}</td>
					<td>{{$pro->pronom}}</td>
					<td>{{$pro->marca}}</td>
			
					<td>{{$pro->umenom}}</td>
					
					<td>{{$pro->stock_inicial}}</td>
					<td>{{$pro->stock}}</td>
					<td>{{$pro->propun}}</td>
					<td>{{$pro->costo}}</td>
					<td>{{number_format($pro->costo*$pro->stock,2,'.','')}}</td>
					<td> @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin') )<a href="" data-target="#modal-ajuste-{{$pro->IdProducto}}" data-toggle="modal"><button class="btn btn-primary">Ajustes</button></a>
					@endif</td>
					
				</tr>
				@include('empresas.productos.modalajuste')
				@endforeach
			</table>