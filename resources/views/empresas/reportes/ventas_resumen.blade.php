
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead >
								<tr>
									<th colspan="8"><center><strong>RESUMEN DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} </strong></center></th>
									
								</tr>
							
								
								<tr  >
									<th style="text-align:center;" class="border-table" rowspan="2" >CODIGO DESRIPCION</th>
									<th style="text-align:center;" class="border-table" rowspan="2" >VALOR FACTURADO DE EXPORTAC.</th>
									<th style="text-align:center;" class="border-table" rowspan="2" >BASE IMPONIBLE DE LA OPERAC. GRAVADA</th>
									<th style="text-align:center;" class="border-table"  colspan="2">IMPORTE TOTAL DE OPER. EXONERAD O INAFECTA</th>
									<th style="text-align:center;" class="border-table" rowspan="2" >IGV Y/O I.P.M</th>
									<th style="text-align:center;" class="border-table" rowspan="2" >OTR. TRIB. QUE NO FORMAN PARTE DEL LAB .I</th>
									<th style="text-align:center;" class="border-table" rowspan="2" >IMPORTE TOTAL DEL COMP. DE PAGO</th>
								</tr>
								<tr>
								
									<th style="text-align:center;"  class="border-table" >EXONERADA</th>
									<th style="text-align:center;"  class="border-table" >INAFECTA</th>
									
								</tr>
							</thead>
							<tbody>
								
								<tr>
									<td>01 F/.</td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;">{{number_format($totalgravadasfacturas,'2','.','')}}</td>
									<td style="text-align:right;">{{number_format($totalexoneradasfacturas,'2','.','')}}</td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;"></td>
									<td style="text-align:right;">{{number_format($totalgravadasfacturas+$totalexoneradasfacturas,'2','.','')}}</td>

								</tr>
								
								<tr>
									<td>03 B/V</td>
									<td></td>
									<td style="text-align:right;">{{number_format($totalgravadasboletas,'2','.','')}}</td>
									<td style="text-align:right;">{{number_format($totalexoneradasboletas,'2','.','')}}</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="text-align:right;">{{number_format($totalgravadasboletas+$totalexoneradasboletas,'2','.','')}}</td>

								</tr>
								<tr>
									<td>07 N/C</td>
									<td></td>
									<td style="text-align:right;">{{number_format((-1)*$totalgravadasnotas,'2','.','')}}</td>
									<td style="text-align:right;">{{number_format((-1)*$totalexoneradasnotas,'2','.','')}}</td>
									<td></td>
									<td></td>
									<td></td>
									<td style="text-align:right;">{{number_format((-1)*($totalgravadasnotas+$totalexoneradasnotas),'2','.','')}}</td>

								</tr>
							

								<tr>
									<td  style="text-align:right;"><strong>TOTAL VENTA S/. </strong></td>
									<td></td>
									<td style="text-align:right;">{{number_format(($totalgravadasfacturas+$totalgravadasboletas)-($totalgravadasnotas),'2','.','')}}</td>
									<td style="text-align:right;">{{number_format(($totalexoneradasfacturas+$totalexoneradasboletas)-($totalexoneradasnotas),'2','.','')}}</td>
									<td></td>
									<td></td>
									<td></td>
									<td  style="text-align:right;"><strong>{{number_format(($totalgravadasfacturas+$totalgravadasboletas+$totalexoneradasfacturas+$totalexoneradasboletas)-($totalgravadasnotas+$totalexoneradasnotas),'2','.',',')}}</strong></td>
								</tr>
							</tbody>
						</table>