
<table id="dtHorizontalExample"  class="table table-responsive  table-bordered table-sm" style="font-size:9pt;" style="width:100%;font-size:8pt;">
	<thead >
		<tr>
			<th colspan="6" style="background-color:#337ab7;color:#fff;text-align:center;font-weight:bold;">
				RESUMEN DE VENTAS DETALLADO DESDE {{$fec_ini}} HASTA {{$fec_fin}} @if(!empty($dato_vendedor))<br>{{$dato_vendedor->name}}  {{$dato_vendedor->apeusu}} @endif @if(!empty($dato_cliente)) <br> {{$dato_cliente->clinom}}  @endif
			</th>
		</tr>
		<tr>
			<th style="text-align:center;width:20;">CODIGO</th>
			<th style="text-align:center;width:100;">PRODUCTO</th>
			<th style="text-align:center;width:40;">UNIDAD</th>
			<th style="text-align:center;width:25;">CANTIDAD</th>
			<th style="text-align:center;width:25;">PRECIO</th>
			<th style="text-align:center;width:25;">TOTAL</th>
		</tr>
	</thead>
	<tbody>
		@foreach($venta as $vent)
		<tr>
			<td style="background-color:#f4f4f4;"><strong>{{Carbon::parse($vent->fecha)->format('d-m-Y')}}</strong></td>
			<td colspan="4" style="background-color:#f4f4f4;"><strong>{{$vent->tipo_comprobante}} {{$vent->serie}} {{$vent->numero}} {{$vent->cliente}} </strong></td>
			<td  style="text-align:right;background-color:#f4f4f4;"><strong>@if($vent->tipo_comprobante =='07') {{number_format((-1)*$vent->total,'2','.','')}} @else {{number_format($vent->total,'2','.','')}} @endif</strong></td>
		</tr>

		@php

		$obt_det = $detalle->where('IdCpe_cabecera',$vent->IdCpe_cabecera);
		
		@endphp

		@foreach($obt_det as $det)
		<tr>
			<td style="text-align:left;">{{$det->codigo}}</td>
			<td >{{$det->producto}}</td>
			<td>{{$det->unidad_medida}}</td>
			<td  style="text-align:right;">{{number_format($det->cantidad,'2','.','')}}</td>
			<td  style="text-align:right;">{{number_format($det->precio_unitario,'2','.','')}}</td>
			<td  style="text-align:right;">@if($det->tdocod =='07') {{number_format((-1)*$det->total_item,'2','.','')}} @else {{number_format($det->total_item,'2','.','')}} @endif</td>
		</tr>
		@endforeach
		<tr>
			<td colspan='6'></td>
		</tr>
		@endforeach
		<tr>
			<td colspan='5' style="text-align:right;font-weight:bold;background-color:#f4f4f4;">NOTAS DE CREDITO</td>
			<td style="text-align:right;font-weight:bold;background-color:#f4f4f4;">{{number_format((-1)*$total_notas_creditos,'2','.','')}}</td>
		</tr>
		<tr>
			<td colspan='5' style="text-align:right;font-weight:bold;background-color:#f4f4f4;">TOTAL</td>
			<td style="text-align:right;font-weight:bold;background-color:#f4f4f4;">{{number_format($total_ventas,'2','.','')}}</td>
		</tr>
		<tr>
			<td colspan='5' style="text-align:right;font-weight:bold;background-color:#f4f4f4;">SALDO</td>
			<td style="text-align:right;font-weight:bold;background-color:#f4f4f4;">{{number_format($total_ventas-$total_notas_creditos,'2','.','')}}</td>
		</tr>
		<tr>
			<td colspan='6'></td>
		</tr>
		<tr>
			<td colspan="4" style="width:35;text-align:left;background-color:#f4f4f4;">1) RESUMEN VENTAS</td>
			<td style="width:40;text-align:right;background-color:#f4f4f4;">CANTIDAD</td>
			<td style="width:40;text-align:right;background-color:#f4f4f4;">TOTAL</td>
		</tr>
		@foreach($vent_bolfac_res_prod as $vrp)
		<tr>
			<td colspan="4" style="width:35">{{$vrp->procod}} {{$vrp->cdedes}}</td>
			<td  style="text-align:right;width:40">{{number_format($vrp->cantidad,'2','.','')}}</td>
			<td  style="text-align:right;width:40">{{number_format($vrp->precio,'2','.','')}}</td>
		</tr>
		@endforeach

		@foreach($not_cre_res_prod as $ncrp)
		<tr>
			<td colspan="4" style="width:35">{{ $ncrp->procod}} {{$ncrp->cdedes}}</td>
			<td  style="text-align:right;width:40">{{number_format((-1)*$ncrp->cantidad,'2','.','')}}</td>
			<td  style="text-align:right;width:40">{{number_format((-1)*$ncrp->precio,'2','.','')}}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan='6' style="background-color:#f4f4f4;"></td>
		</tr>
		<tr>
			<td colspan='4' style="text-align:left;"></td>
			<td style="width:40;text-align: right;font-weight:bold;">TOTAL VENTAS</td>
			<td  style="text-align:right;width:40;"><strong>{{number_format($total_ventas_bolfac,'2','.','')}}</strong></td>
		</tr>
		<tr>
			<td colspan='4' style="text-align:left;"></td>
			<td  style="width:40;text-align: right;font-weight:bold;">TOTAL NOTAS DE CRÉDITO</td>
			<td  style="text-align:right;width:40;">{{number_format((-1)*$total_notas_creditos,'2','.','')}}</td>
		</tr>
		<tr>
			<td colspan='6'></td>
		</tr>
		<tr>
			<td colspan="4" style="width:35;text-align:left;background-color:#f4f4f4;">2) RESUMEN NOTAS</td>
			<td style="width:40;background-color:#f4f4f4;text-align:right">CANTIDAD</td>
			<td style="width:40;background-color:#f4f4f4;text-align:right;">TOTAL</td>
		</tr>
		<tr>
			<td colspan='6' style="background-color:#f4f4f4;"></td>
		</tr>
		@foreach($not_ven_res_prod as $pn)
		<tr>
			<td colspan="4" style="width:35">{{$pn->procod}} {{$pn->cdedes}}</td>
			<td  style="text-align:right;width:40">{{number_format($pn->cantidad,'2','.',',')}}</td>
			<td  style="text-align:right;width:40">{{number_format($pn->precio,'2','.',',')}}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan='4' style="text-align:left;"></td>
			<td style="width:40;text-align: right;font-weight:bold;">TOTAL NOTAS VENTAS</td>
			<td  style="text-align:right;width:40;"><strong>{{number_format($total_notas_ventas,'2','.',',')}}</strong></td>
		</tr>
		<tr>
			<td colspan='6'></td>
		</tr>

		<tr>
			<td colspan="4" style="width:35;text-align:left;background-color:#f4f4f4;">3) RESUMEN VALES DE CONSUMO</td>
			<td style="width:40;background-color:#f4f4f4;text-align:right">CANTIDAD</td>
			<td style="width:40;background-color:#f4f4f4;text-align:right;">TOTAL</td>
		</tr>
		<tr>
			<td colspan='6' style="background-color:#f4f4f4;"></td>
		</tr>
		@foreach($val_con_res_prod as $pc)
		<tr>
			<td colspan="4" style="width:35">{{$pc->procod}} {{$pc->cdedes}}</td>
			<td  style="text-align:right;width:40">{{number_format($pc->cantidad,'2','.',',')}}</td>
			<td  style="text-align:right;width:40">{{number_format($pc->precio,'2','.',',')}}</td>
		</tr>
		@endforeach
		<tr>
			<td colspan='4' style="text-align:left;"></td>
			<td style="width:40;text-align: right;font-weight:bold;"><strong>TOTAL VALES DE CONSUMO</strong></td>
			<td  style="text-align:right;width:40;"><strong>{{number_format($total_vales_cons,'2','.',',')}}</strong></td>
		</tr>

		<tr>
			<td colspan='6'></td>
		</tr>
		<tr>
			<td colspan='6' style="background-color:#f4f4f4;"></td>
		</tr>
		<tr>
			<td colspan='4' style="text-align:left;"></td>
			<td style="width:40;text-align: right;font-weight:bold;">SALDO</td>
			<td  style="text-align:right;width:40;"><strong>{{number_format($total_ventas-$total_notas_creditos,'2','.','')}}</strong></td>
		</tr>		
	</tbody>		
</table>



