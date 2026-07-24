
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="font-size:8pt;">
								
							<thead>
								<tr>
									<th colspan="21" style="text-align:center;">COMPRAS DESDE {{$fecin}} HASTA {{$fecfin}}</th>
									
								</tr>
								<tr>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:30;">N° CORREL. DEL REG. <br>O COD. UNICO DE LA OPER.</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:30;">FECHA DE EMIS. DEL <br>COMP. DE PGO. O DOC</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:30;">FECHA DE VENCIM. Y/O PAGO</th>
									<th colspan="3" style="vertical-align:middle;text-align:center; width:30;"></th>
									<th colspan="3" style="vertical-align:middle;text-align:center; width:30;">INFORMACION DEL CLIENTE</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:30;">VALOR FACTURADO <br>DE LA EXPORTAC.</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:30;">BASE IMP. DE LA <br>OPERAC. GRAVADA</th>
									<th colspan="2" style="vertical-align:middle;text-align:center; width:30;">IMPORTE TOTAL DE OPER. EXONERADA O INAFECTA</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:30;">IGV Y/O I.P.M</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:20;">ICBPER</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:20;">IMPORTE TOTAL DEL<br> COMP. DE PAGO</th>
									<th rowspan="2" style="vertical-align:middle;text-align:center; width:20;">TIPO DE CAMB.</th>
									<th colspan="4" style="vertical-align:middle;text-align:center; width:30;">REF. DE COMP. DE PAGO O DOC. ORIGINAL QUE SE MODIFICA.</th>
								</tr>
					
							
								<tr>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:10;text-align:center;">TIPO (10)</th>
									<th style="vertical-align:middle;width:10;text-align:center;">SERIE</th>
									<th style="vertical-align:middle;width:10;text-align:center;">N°</th>
									<th style="vertical-align:middle;width:15;text-align:center;">TIPO TABLA (2)</th>
									<th style="vertical-align:middle;width:30;text-align:center;">NUMERO</th>
									<th style="vertical-align:middle;width:30;text-align:center;">DENOMINACION O RAZON SOCIAL</th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:30;text-align:center;">EXONERADA</th>
									<th style="vertical-align:middle;width:30;text-align:center;">INAFECTA</th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:10;text-align:center;"></th>
									<th style="vertical-align:middle;width:30;text-align:center;">FECHA</th>
									<th style="vertical-align:middle;width:30;text-align:center;">TIPO TABLA (10)</th>
									<th style="vertical-align:middle;width:30;text-align:center;">SERIE</th>
									<th style="vertical-align:middle;width:30;text-align:center;">N° DEL COMP. O DOC.</th>
									
								</tr>
							</thead>
							
							<tbody>
									
									@foreach($comprobantes as $comprobante)
								<tr>
								
								 	<td style="width:30;">{{$comprobante->cod_mov}}</td>
								 	<td style="width:30;text-align:center;">{{Carbon::parse($comprobante->com_fec)->format('d-m-Y')}}</td>
									<td style="width:30;text-align:center;">{{Carbon::parse($comprobante->com_fec_ven)->format('d-m-Y')}}</td>
									<td style="text-align:center;">{{$comprobante->tdocod}}</td>
									<td style="text-align:center;">{{$comprobante->com_doc_ser}}</td>
									<td>{{$comprobante->com_doc_num}}</td>
									<td>{{$comprobante->tdicod}}</td>
									<td>{{$comprobante->prov_ruc}}</td>
									<td>{{$comprobante->prov_raz}}</td>
								 	<td>0.00</td>
									<td>{{$comprobante->com_grav}}</td>
									<td>{{$comprobante->com_exo}}</td>
									<td>{{$comprobante->com_inaf}}</td>
									<td style="width:20;">{{$comprobante->com_cab_igv}}</td>
									<td>0.00</td>
									<td style="width:30;">{{$comprobante->total_com}}</td>
									<td style="width:30;">{{$comprobante->tip_cam}}</td>
									<td >{{$comprobante->fec_ref}}</td>
									<td>{{$comprobante->tdocod_ref}}</td>
									<td>{{$comprobante->serie_ref}}</td>
									<td>{{$comprobante->num_ref}}</td>
									
								</tr>
								@endforeach
							</tbody>
						</table><br>
