

<table  id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
				<thead>
					<tr style="background:#3c8dbc;color:white;">
					<th colspan="10"><center>STOCK DE PRODUCTOS {{Carbon::now()->format('d-m-Y')}}</center></th>
				</tr>
				</thead>
				<thead>
					<tr>
					<th>CODIGO</th>
					<th>PRODUCTO</th>

					<th>UM</th>
					
					<th>STOCK</th>
				
				
					</tr>
				</thead>
				<tbody>
				@foreach ($productos as $pro)
				
				<tr>
					<td>{{$pro->procod}}</td>
					<td>{{$pro->pronom}}</td>
					
				
					<td>{{$pro->umenom}}</td>
					
					
					<td>
						@if($pro->stock<='0')
							<button type="button" class="btn btn-sm btn-block btn-danger">{{$pro->stock}}</button>
						@else

						<button type="button" class="btn btn-sm btn-block btn-success">{{$pro->stock}}</button>
						@endif
						
					</td>
					
					
					
				</tr>
			
				@endforeach
				</tbody>
			</table>