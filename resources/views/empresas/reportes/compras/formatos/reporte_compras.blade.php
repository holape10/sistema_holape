
						<table id="dtHorizontalExample"  class="table table-responsive  table-bordered table-sm"  style="width:100;font-size:8pt;">
							<thead>
								<tr>
									<th colspan="14" style="text-align:center;background-color:#337ab7;color:#fff;"><center><strong>REPORTE DE COMPRAS DESDE: {{$fec_ini}} HASTA {{$fec_fin}}</strong></center></th>
									
								</tr>
								<tr>
								   <th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">ITEM</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">FECHA<BR>FACTURA</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">FECHA<BR>INGRESO MERCADERIA</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">CODIGO SUNAT<BR>COMPROBANTE</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">COMPROBANTE</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">SERIE</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">N°</th>
									<th style="width:50;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">PROVEEDOR</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">FECHA<br>DOC. REF</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">TIPO<br>DOC. REF</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">SERIE<br>DOC. REF</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">NUMERO<br>DOC. REF</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">MONEDA</th>
									<th style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;">TOTAL</th>		
								</tr>
							</thead>
							
							<tbody>

								@if(count($factura)>0)
								<tr>
									<td colspan="14" style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;background:#E8E8E8;">FACTURAS</td>
								</tr>
								@endif
								@php
									$i=0;

								@endphp
								@foreach($factura as $comprobante)
									@php 
										$i = $i + 1;
									@endphp
								<tr>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt;font-weight:bold;">{{$i}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec_ing)->format('d-m-Y')}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->tdodes}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->com_doc_ser}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->com_doc_num}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->prov_raz}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->fec_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->serie_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->num_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->mon_id}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{number_format($comprobante->total_com,'2','.',',')}}</td>
								
								
								</tr>
								@endforeach

								@if(count($boleta)>0)
								<tr>
									<td colspan="14" style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;background:#E8E8E8;">BOLETAS</td>
								</tr>
								@endif

								@php
									$i=0;
								@endphp
								@foreach($boleta as $comprobante)
									@php 
										$i = $i + 1;

									@endphp
								<tr>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt;font-weight:bold;">{{$i}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec_ing)->format('d-m-Y')}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->tdodes}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->com_doc_ser}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->com_doc_num}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->prov_raz}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->fec_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->serie_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->num_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->mon_id}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{number_format($comprobante->total_com,'2','.',',')}}</td>
								
								
								</tr>
								@endforeach

								@if(count($otras_compras)>0)
								<tr>
									<td colspan="14" style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;background:#E8E8E8;">OTROS COMPROBANTES</td>
								</tr>
								@endif

								@php
									$i=0;
								@endphp
								@foreach($otras_compras as $comprobante)
									@php 
										$i = $i + 1;

									@endphp
								<tr>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt;font-weight:bold;">{{$i}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec_ing)->format('d-m-Y')}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->tdodes}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->com_doc_ser}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->com_doc_num}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->prov_raz}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->fec_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->serie_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->num_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->mon_id}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{number_format($comprobante->total_com,'2','.',',')}}</td>
								
								
								</tr>
								@endforeach

								@if(count($vales_comp)>0)
								<tr>
									<td colspan="14" style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;background:#E8E8E8;">VALES</td>
								</tr>
								@endif

								@php
									$i=0;
								@endphp
								@foreach($vales_comp as $comprobante)
									@php 
										$i = $i + 1;

									@endphp
								<tr>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt;font-weight:bold;">{{$i}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec_ing)->format('d-m-Y')}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->tdodes}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->com_doc_ser}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->com_doc_num}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->prov_raz}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->fec_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->serie_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->num_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->mon_id}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{number_format($comprobante->total_com,'2','.',',')}}</td>
								
								
								</tr>
								@endforeach

								@if(count($nota_credito)>0)
								<tr>
									<td colspan="14" style="width:10;text-align:center;vertical-align:middle;font-size:8pt;font-weight:bold;background:#E8E8E8;">NOTAS DE CRÉDITO</td>
								</tr>
								@endif

								@php
									$i=0;
								@endphp
								@foreach($nota_credito as $comprobante)
									@php 
										$i = $i + 1;

									@endphp
								<tr>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt;font-weight:bold;">{{$i}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
								 	<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{Carbon::parse($comprobante->com_fec_ing)->format('d-m-Y')}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->tdodes}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->com_doc_ser}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->com_doc_num}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->prov_raz}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->fec_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->tdocod_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:left;font-size:8pt">{{$comprobante->serie_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{$comprobante->num_ref}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:center;font-size:8pt">{{$comprobante->mon_id}}</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt">{{number_format($comprobante->total_com,'2','.',',')}}</td>
								
								
								</tr>
								@endforeach
								<tr>
									<td colspan="14"></td>
								</tr>
								<tr>
									<td colspan="11"></td>
									<td colspan="3" style="text-align:center;font-weight:bold;vertical-align:middle;background-color:#337ab7;color:#fff;">RESUMEN</td>
								</tr>
								
								<tr>
									<td colspan="11"></td>
									<td colspan="2" style="text-align:left;font-weight:bold;vertical-align:middle;font-weight:bold;background:#E8E8E8;">TOTAL FACTURAS - BOLETAS</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt;font-weight:bold;vertical-align:middle;">{{number_format($total_compras_bolfac,'2','.',',')}}</td>
									
								</tr>
								<tr>
										<td colspan="11"></td>
									<td colspan="2" style="text-align:left;font-weight:bold;vertical-align:middle;font-weight:bold;background:#E8E8E8;">TOTAL OTROS COMPROBANTES</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt;font-weight:bold;vertical-align:middle;">{{number_format($total_otras_compras,'2','.',',')}}</td>
									
								</tr>

								<tr>
									<td colspan="11"></td>
									<td colspan="2" style="text-align:left;font-weight:bold;vertical-align:middle;font-weight:bold;background:#E8E8E8;">TOTAL</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt;font-weight:bold;vertical-align:middle;background:#E8E8E8;">{{number_format($total_compras,'2','.',',')}}</td>
									
								</tr>
								<tr>
										<td colspan="11"></td>
									<td colspan="2" style="text-align:left;font-weight:bold;vertical-align:middle;font-weight:bold;background:#E8E8E8;">TOTAL NOTAS DE CRÉDITO</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt;font-weight:bold;vertical-align:middle;">{{number_format($total_notas_creditos,'2','.',',')}}</td>
								</tr>

								<tr>
									<td colspan="11"></td>
									<td colspan="2" style="text-align:left;font-weight:bold;vertical-align:middle;font-weight:bold;background:#E8E8E8;">TOTAL VALES</td>
									<td style="padding:5px 5px 5px 5px;text-align:right;font-size:8pt;font-weight:bold;vertical-align:middle;">{{number_format($total_vales_comp,'2','.',',')}}</td>
								</tr>

							</tbody>
						</table><br>
					
					