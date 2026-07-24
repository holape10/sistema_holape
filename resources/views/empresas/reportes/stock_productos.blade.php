<!-- ALMACEN - REPORTES - STOCK PRODUCTOS - REPORTE DE STOCK DE PRODUTOS -->

<table  id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
				<thead>
					<tr style="background:#3c8dbc;color:white;">
					<th colspan="11"><center>STOCK DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}} @if(!empty($datos)) - {{$datos->tipo_negocio}} @endif @if(!empty($datosalm)) - <br>{{$datosalm->descripcion}} @endif</center></th>
				</tr>
				</thead>
				<thead>
					<tr>
					<th style="text-align:center;vertical-align:middle;">CODIGO</th>
					<th style="text-align:center;vertical-align:middle;">CATEGORIA</th>
					<th style="text-align:center;vertical-align:middle;" style="width:500px;">PRODUCTO</th>
					<th style="text-align:center;vertical-align:middle;" hidden="hidden">MARCA</th>
					<th style="text-align:center;vertical-align:middle;" hidden="hidden">LOTE</th>
					<th style="text-align:center;vertical-align:middle;" hidden="hidden">VENCIMIENTO</th>
					<th style="text-align:center;vertical-align:middle;">UM</th>
					<!--<th style="text-align:center;vertical-align:middle;">STOCK INICIAL</th>-->
					<th style="text-align:center;vertical-align:middle;">STOCK PRINCIPAL</th>
					<th style="text-align:center;vertical-align:middle;">STOCK EQUIVALENTE</th>
					<th style="text-align:center;vertical-align:middle;">PRECIO VENTA</th>
					<!--<th style="text-align:center;vertical-align:middle;">COSTO</th>					
					<th style="text-align:center;vertical-align:middle;">VALOR INVENTARIO</th>-->
					<th style="text-align:center;vertical-align:middle;" hidden="hidden">OPCIONES</th>
					<th style="text-align:center;vertical-align:middle;" hidden="hidden">RECALCULAR STOCK</th>
					<th style="text-align:center;vertical-align:middle;" hidden="hidden">HISTORIAL</th>
					</tr>
				</thead>
				<tbody>
				@foreach ($productos as $pro)
				
				<tr>
					<td>{{$pro->procod}}</td>
					<td>{{$pro->categoria}}</td>
					<td style="width:500px;">{{$pro->pronom}}</td>					
					<td hidden="hidden">{{$pro->marca}}</td>
				<td hidden="hidden">{{$pro->lote}}</td>
					<td hidden="hidden">{{$pro->vencimiento}}</td>
					<td>{{$pro->umenom}}</td>
					
					<!--<td>{{$pro->stock_inicial}}</td>-->
					<td>
						@if($pro->stock<='0')
							<button type="button" class="btn btn-sm btn-block btn-danger">{{$pro->stock}}</button>
						@else

						<button type="button" class="btn btn-sm btn-block btn-success">{{$pro->stock}}</button>
						@endif
						
					</td>

					<td>
						
						@if($pro->stock_equivalencia<='0')
							<button type="button" class="btn btn-sm btn-block btn-danger">{{$pro->stock_equivalencia}}</button>
						@else

						<button type="button" class="btn btn-sm btn-block btn-success">{{$pro->stock_equivalencia}}</button>
						@endif
						
						
					</td>
					<td>{{$pro->precio}}</td>
					<!--<td>{{$pro->costo}}</td>
					<td>{{number_format($pro->costo*$pro->stock,2,'.','')}}</td>-->
					@if(Auth::user()->hasRole('admin'))
						<td hidden="hidden"> <a href="/ajustar_stock/{{$almacen}}/{{$pro->IdProducto}}"><button type="button" class="btn btn-sm btn-primary">AJUSTAR STOCK</button></a></td>
					@else
						<td hidden="hidden"><button disabled="disabled" type="button" class="btn btn-sm btn-primary">AJUSTAR STOCK</button></td>
					@endif
						<td hidden="hidden"> <a href="/recalcular_stock/{{$almacen}}/{{$pro->IdProducto}}"><button type="button" class="btn btn-sm btn-primary">RECALCULAR STOCK</button></a></td>
							<td hidden="hidden"><a href="/historial/{{$pro->IdProducto}}"><button class="btn btn-success btn-sm">Historial</button></a></td>
					
				</tr>
			
				@endforeach
				</tbody>
			</table>