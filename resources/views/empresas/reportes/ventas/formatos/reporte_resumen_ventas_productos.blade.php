<table id="dtHorizontalExample" style="width:100%;font-size:9pt;"  class="table table-responsive table-striped table-bordered table-sm" >
	<thead>
		<tr>
			<th colspan="6" style="text-align:center;background-color:#337ab7;color:#fff;font-weight:bold;">RESUMEN DE VENTAS POR PRODUCTO DESDE {{$fec_ini}} HASTA {{$fec_fin}} </th>
		</tr>
		<tr>
			<th style="width:10;text-align:center;">DIA</th>
			<th style="width:10;text-align:center;">CODIGO</th>
			<th style="width:50;text-align:center;">PRODUCTO</th>
			<th style="width:20;text-align:center;">CANTIDAD</th>
			<th style="width:20;text-align:center;">PRECIO</th>
			<th style="width:20;text-align:center;">TOTAL</th>
		</tr>
	</thead>
	<tbody>
		<?php $total_general = 0; ?>
		@foreach($vent_res_prod as $producto)
		<tr>
			<td style="width:10;">{{date('d/m/Y', strtotime($producto->dia))}}</td>
			<td style="width:10;">{{$producto->codigo}}</td>
			<td style="width:50;">{{$producto->producto}}</td>
			<td style="text-align:right;width:20;">{{number_format($producto->cantidad,'2','.',',')}}</td>
			<td style="text-align:right;width:20;">{{number_format($producto->precio,'2','.',',')}}</td>
			<td style="text-align:right;width:20;">{{number_format($producto->total,'2','.',',')}}</td>
		</tr>
		<?php $total_general += $producto->total; ?>
		@endforeach
		<tr>
			<td colspan='5' style="text-align:right;"><strong>TOTAL</strong></td>
			<td style="text-align:right;width:20;"><strong>{{number_format($total_general,'2','.',',')}}</strong></td>
		</tr>
	</tbody>
</table>