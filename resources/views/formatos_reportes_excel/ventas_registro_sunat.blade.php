
						<table   style="font-size:8pt;">
							<thead>
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
									
								@if(count($facturas)>0)
									@foreach($facturas as $factura)
								<tr>
								
								 	<td>{{$factura->cod_mov}}</td>
								 	<td>{{Carbon::parse($factura->ccafem)->format('d-m-Y')}}</td>
									<td>{{Carbon::parse($factura->fechaven)->format('d-m-Y')}}</td>
									<td>{{$factura->tdocod}}</td>
									<td>{{$factura->serie}}</td>
									<td>{{$factura->numero}}</td>
									<td>{{$factura->tdicod}}</td>
									<td>{{$factura->numerodocumento}}</td>
									<td>{{$factura->cliente}}</td>
								 	<td></td>
									<td></td>
									<td style="text-align:right;">{{$factura->ccatexo}}</td>
									<td style="text-align:right;">{{$factura->ccatvi}}</td>
									<td style="text-align:right;">{{$factura->ccaigv}}</td>
									<td style="text-align:right;">{{$factura->icbper}}</td>
									<td style="text-align:right;">{{$factura->ccaitv}}</td>
									<td style="text-align:right;">{{$factura->tipcambio}}</td>
									<td>{{$factura->ccafem_ref}}</td>
									<td>{{$factura->tdocod_ref}}</td>
									<td>{{$factura->serie_ref}}</td>
									<td>{{$factura->num_ref}}</td>
									
								</tr>
								@endforeach
								<tr>
									<td colspan="11" style="font-weight:bold;">TOTAL FACTURAS</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasexo,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasinaf,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasigv,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturasicbper,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalfacturas,'2','.','')}}</td>
									<td colspan="5"></td>
								</tr>
								@endif


								@if(count($boletas)>0)
									@foreach($boletas as $boleta)
								<tr>
								
								 	<td>{{$boleta->cod_mov}}</td>
								 	<td>{{Carbon::parse($boleta->ccafem)->format('d-m-Y')}}</td>
									<td>{{Carbon::parse($boleta->fechaven)->format('d-m-Y')}}</td>
									<td>{{$boleta->tdocod}}</td>
									<td>{{$boleta->serie}}</td>
									<td>{{$boleta->numero}}</td>
									<td>{{$boleta->tdicod}}</td>
									<td>{{$boleta->numerodocumento}}</td>
									<td>{{$boleta->cliente}}</td>
								 	<td></td>
									<td></td>
									<td style="text-align:right;">{{$boleta->ccatexo}}</td>
									<td style="text-align:right;">{{$boleta->ccatvi}}</td>
									<td style="text-align:right;">{{$boleta->ccaigv}}</td>
									<td style="text-align:right;">{{$boleta->icbper}}</td>
									<td style="text-align:right;">{{$boleta->ccaitv}}</td>
									<td style="text-align:right;">{{$boleta->tipcambio}}</td>
									<td>{{$boleta->ccafem_ref}}</td>
									<td>{{$boleta->tdocod_ref}}</td>
									<td>{{$boleta->serie_ref}}</td>
									<td>{{$boleta->num_ref}}</td>
									
								</tr>
								@endforeach

								<tr>
									<td colspan="11" style="font-weight:bold;">TOTAL BOLETAS</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasexo,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasinaf,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasigv,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletasicbper,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold">{{number_format($totalboletas,'2','.','')}}</td>
									<td colspan="5"></td>
								</tr>
								@endif


								@if(count($notas)>0)
								@foreach($notas as $nota)
								<tr>
								
								 	<td>{{$nota->cod_mov}}</td>
								 	<td>{{Carbon::parse($nota->ccafem)->format('d-m-Y')}}</td>
									<td>{{Carbon::parse($nota->fechaven)->format('d-m-Y')}}</td>
									<td>{{$nota->tdocod}}</td>
									<td>{{$nota->serie}}</td>
									<td>{{$nota->numero}}</td>
									<td>{{$nota->tdicod}}</td>
									<td>{{$nota->numerodocumento}}</td>
									<td>{{$nota->cliente}}</td>
								 	<td></td>
									<td></td>
									<td style="text-align:right;">{{$nota->ccatexo}}</td>
									<td style="text-align:right;">{{$nota->ccatvi}}</td>
									<td style="text-align:right;">{{$nota->ccaigv}}</td>
									<td style="text-align:right;">{{$nota->icbper}}</td>
									<td style="text-align:right;">{{$nota->ccaitv}}</td>
									<td>{{$nota->tipcambio}}</td>
									<td>{{$nota->ccafem_ref}}</td>
									<td>{{$nota->tdocod_ref}}</td>
									<td>{{$nota->serie_ref}}</td>
									<td>{{$nota->num_ref}}</td>
									
								</tr>
								@endforeach
									<tr>
									<td colspan="11" style="font-weight:bold;">TOTAL NOTAS DE CRÉDITOS</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasexo,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasinaf,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasigv,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotasicbper,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($totalnotas,'2','.','')}}</td>
									<td colspan="5"></td>
								</tr>
								@endif

							</tbody>
						</table><br>
