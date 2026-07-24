<div class="row">

	{!!Form::open(array('url'=>'/actualizarprecios','autocomplete'=>'off','method'=>'POST','id'=>'formcosteo','role'=>'form','files'=>'true'))!!}
    {{Form::token()}}
	<div class="col-lg-12">
		 <table class="table table-striped">
		 	<thead>
		 		<th style="background: gray;" ><font color="white"><center>PRODUCTO</center></font></th>
		 		<th colspan="2" style="background: gray;" ><font color="white"><center>COSTO</center></font></th>
		 		<th colspan="4" style="background:gray;"><font color="white"><center>PRECIOS DE VENTA</center></font></th>
		 	</thead>
		 	<thead>
		 		<th>Descripción</th>
		 		<th>Costo Actual</th>
		 		<th>Costo Nuevo</th>
		 		<th>Precio 1 Actual</th>
		 		<th>Precio 2 Actual</th>
		 		<th>Precio 1 Nuevo</th>
		 		<th>Precio 2 Nuevo</th>
		 	</thead>
		 	<tbody>
		 		@foreach($productos as $pro)
		 		<tr>
		 			<td hidden="hidden"><input type="hidden" name="idpro[]" value="{{$pro->IdProducto}}"></td>
		 			<td hidden="hidden"><input type="hidden" name="sucursal" value="{{$suc}}"></td>
		 			<td>{{$pro->pronom}}</td>
		 			<td>{{$pro->costo}}</td>
		 			<td><input class="form-control" type="number" step="any" name="costnue[]"></td>
		 			<td>{{$pro->precio}}</td>
		 			<td>{{$pro->precio2}}</td>
		 		
		 			<td><input class="form-control" type="number" step="any" name="precio[]"></td>
		 			<td><input class="form-control" type="number" step="any" name="precio2[]"></td>
		 		</tr>
		 		@endforeach
		 	</tbody>
		 	
	 	</table>
	</div>
    {!!Form::close()!!} 
</div>