
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="font-size:8pt;">
								
							<thead>
								<tr>
									<th colspan="21"><center><strong>COMPRAS DESDE {{$fec_ini}} HASTA {{$fec_fin}} </strong></center></th>
									
								</tr>
								<tr>
									<th rowspan="2" style="vertical-align:middle;">N° CORREL. DEL REG. O COD. UNICO DE LA OPER.</th>
									<th rowspan="2" style="vertical-align:middle;">FECHA DE EMIS. DEL COMP. DE PGO. O DOC</th>
									<th rowspan="2" style="vertical-align:middle;">FECHA DE VENCIM. Y/O PAGO</th>
									<th colspan="3" style="vertical-align:middle;"></th>
									<th colspan="3" style="vertical-align:middle;">INFORMACION DEL CLIENTE</th>
									<th rowspan="2" style="vertical-align:middle;">VALOR FACTURADO DE LA EXPORTAC.</th>
									<th rowspan="2" style="vertical-align:middle;">BASE IMP. DE LA OPERAC. GRAVADA</th>
									<th colspan="2" style="vertical-align:middle;">IMPORTE TOTAL DE OPER. EXONERADA O INAFECTA</th>
									<th rowspan="2" style="vertical-align:middle;">IGV Y/O I.P.M</th>
									<th rowspan="2" style="vertical-align:middle;">ICBPER</th>
									<th rowspan="2" style="vertical-align:middle;">IMPORTE TOTAL DEL COMP. DE PAGO</th>
									<th rowspan="2" style="vertical-align:middle;">TIPO DE CAMB.</th>
									<th colspan="4" style="vertical-align:middle;">REF. DE COMP. DE PAGO O DOC. ORIGINAL QUE SE MODIFICA.</th>
								</tr>
					
							
								<tr>
									
									<th style="vertical-align:middle;">TIPO (10)</th>
									<th style="vertical-align:middle;">SERIE</th>
									<th style="vertical-align:middle;">N°</th>
									<th style="vertical-align:middle;">TIPO TABLA (2)</th>
									<th style="vertical-align:middle;">NUMERO</th>
									<th style="vertical-align:middle;">DENOMINACION O RAZON SOCIAL</th>
									<th style="vertical-align:middle;">EXONERADA</th>
									<th style="vertical-align:middle;">INAFECTA</th>
									<th style="vertical-align:middle;">FECHA</th>
									<th style="vertical-align:middle;">TIPO TABLA (10)</th>
									<th style="vertical-align:middle;">SERIE</th>
									<th style="vertical-align:middle;">N° DEL COMP. O DOC.</th>
									
								</tr>
							</thead>
							
							<tbody>
									
									@foreach($comprobantes as $comprobante)
								<tr>
								
								 	<td>{{$comprobante->cod_mov}}</td>
								 	<td>{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
									<td>{{Carbon::parse($comprobante->com_fec_ven)->format('d-m-Y')}}</td>
									<td>{{$comprobante->tdocod}}</td>
									<td>{{$comprobante->com_doc_ser}}</td>
									<td>{{$comprobante->com_doc_num}}</td>
									<td>{{$comprobante->tdicod}}</td>
									<td>{{$comprobante->prov_ruc}}</td>
									<td>{{$comprobante->prov_raz}}</td>
								 	<td>0.00</td>
									<td>{{$comprobante->com_grav}}</td>
									<td>{{$comprobante->com_exo}}</td>
									<td>{{$comprobante->com_inaf}}</td>
									<td>{{$comprobante->com_cab_igv}}</td>
									<td>0.00</td>
									<td>{{$comprobante->total_com}}</td>
									<td>{{$comprobante->tip_cam}}</td>
									<td>{{$comprobante->fec_ref}}</td>
									<td>{{$comprobante->tdocod_ref}}</td>
									<td>{{$comprobante->serie_ref}}</td>
									<td>{{$comprobante->num_ref}}</td>
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
