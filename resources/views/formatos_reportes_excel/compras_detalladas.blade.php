
						<table >
							<thead>
								<tr>
									<th colspan="16" style="font-size:10;font-weight:bold;background:#E8E8E8;text-align:center">REPORTE DE COMPRAS DESDE: {{$fecin}} HASTA {{$fecfin}}</th>
								
								</tr>
								

								<tr>
							
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">ITEM</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">FECHA  FACTURA</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">FECHA  INGRESO MERCADERIA</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">CODIGO SUNAT  COMPROBANTE</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">COMPROBANTE</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">SERIE</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">N°</th>
									<th style="width:50;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">PROVEEDOR</th>

									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">CANTIDAD</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">U.M</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;" style="width:50;">PRODUCTO</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;" >LOTE</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;" >VENCIMIENTO</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">MONEDA</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">P.UNITARIO</th>
									<th style="width:20;text-align:center;font-size:10;font-weight:bold;background:#E8E8E8;">TOTAL</th>
												
								</tr>
							</thead>
							
							<tbody>
									@php
									$i=0;
								@endphp
								@foreach($comprobantes as $comprobante)
									@php 
										$i = $i + 1;
									@endphp
								<tr>
						
								<td style="text-align:center;font-size:10;font-weight:bold;">{{$i}}</td>
								 	<td style="text-align:center;font-size:10">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
								 	<td style="text-align:center;font-size:10">{{Carbon::parse($comprobante->com_fec_ing)->format('d-m-Y')}}</td>
									<td style="text-align:center;font-size:10">{{$comprobante->tdocod}}</td>
									<td style="text-align:center;font-size:10">{{$comprobante->tdodes}}</td>
									<td style="text-align:left;font-size:10">{{$comprobante->com_doc_ser}}</td>
									<td style="text-align:right;font-size:10">{{$comprobante->com_doc_num}}</td>
									<td style="text-align:left;font-size:10">{{$comprobante->prov_raz}}</td>
									
									<td style="text-align:right;">{{$comprobante->cantidad}}</td>
									<td style="">{{$comprobante->umenom}}</td>
									<td style="">{{$comprobante->pronom}}</td>
									<td style="">{{$comprobante->lote}}</td>
									<td style="">{{$comprobante->vencimiento}}</td>
									<td style="text-align:center;">{{$comprobante->mon_id}}</td>
									<td style="text-align:right;">{{number_format($comprobante->pre_uni,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($comprobante->total,'2','.',',')}}</td>
								
								</tr>
								@endforeach
								<tr>
									<th colspan="15" style="text-align:right;font-weight:bold;text-align:right;">TOTAL</th>
									<th style="text-align:right;font-weight:bold;">{{number_format($total,'2','.',',')}}</th>
									
								</tr>
							</tbody>
						</table>
					