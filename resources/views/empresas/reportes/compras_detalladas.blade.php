
						<table id="dtHorizontalExample"  class="table table-responsive table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="16" style="font-size:10pt;font-weight:bold;background:#E8E8E8;"><center><strong>REPORTE DE COMPRAS DESDE: {{$fecin}} HASTA {{$fecfin}}</strong></center>
								
								</tr>
								

								<tr>
							
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">ITEM</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">FECHA<BR>FACTURA</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">FECHA<BR>INGRESO MERCADERIA</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">CODIGO SUNAT<BR>COMPROBANTE</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">COMPROBANTE</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">SERIE</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">N°</th>
									<th style="width:50%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">PROVEEDOR</th>

									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">CANTIDAD</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">U.M</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;" style="width:50%;">PRODUCTO</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;" >LOTE</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;" >VENCIMIENTO</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">MONEDA</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">P.UNITARIO</th>
									<th style="width:10%;text-align:center;vertical-align:middle;font-size:10pt;font-weight:bold;background:#E8E8E8;">TOTAL</th>
												
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
						
								<td style="padding:5px 5px 5px 5px;text-align:center;font-size:10pt;font-weight:bold;">{{$i}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:10pt">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:10pt">{{Carbon::parse($comprobante->com_fec_ing)->format('d-m-Y')}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:10pt">{{$comprobante->tdocod}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:10pt">{{$comprobante->tdodes}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:10pt">{{$comprobante->com_doc_ser}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:10pt">{{$comprobante->com_doc_num}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:10pt">{{$comprobante->prov_raz}}</td>
									
									<td style="padding:5px 5px 5px 5px;text-align:right;">{{$comprobante->cantidad}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->umenom}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->pronom}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->lote}}</td>
									<td style="padding:5px 5px 5px 5px;">{{$comprobante->vencimiento}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;">{{$comprobante->mon_id}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;">{{number_format($comprobante->pre_uni,'2','.',',')}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;">{{number_format($comprobante->total,'2','.',',')}}</td>
								
								</tr>
								@endforeach
								<tr>
									<th colspan="15" style="text-align:right;font-weight:bold;text-align:right;vertical-align:middle;">TOTAL</th>
									<th style="padding:5px 5px 5px 5px;text-align:right;font-weight:bold;vertical-align:middle;">{{number_format($total,'2','.',',')}}</th>
									
								</tr>
							</tbody>
						</table><br>
					