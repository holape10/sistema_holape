
			<table>
							<thead>
							
									<tr>
									<th colspan="13"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}}  | {{$hora_rep}}</strong></center></th>
									
								</tr>
								<tr >
									<th colspan="8" rowspan="2"><center><strong>TOTAL VENTAS</strong></center>
									<th>GRAVADOS</th>
									<th>EXONERADOS</th>
									<th>ICBPER</th>
									<th>IGV</th>
									<th >TOTAL</th>
								</tr>
								<tr>
									<th colspan="8"></th>
									<th>{{number_format($gravados,'2','.','')}}</th>
									<th>{{number_format($exoneradas,'2','.','')}}</th>
									<th></th>
									<th>{{number_format($totaligv,'2','.','')}}</th>
									<th>{{number_format($totalventas,'2','.','')}}</th>
								</tr>
								<tr >
									<th colspan="8" rowspan="2"><center><strong>TOTAL NOTAS DE CREDITO</strong></center>
									<th>GRAVADOS</th>
									<th>EXONERADOS</th>
									<th>ICBPER</th>
									<th>IGV</th>
									<th >TOTAL</th>
								</tr>
								<tr>
									<th colspan="8"></th>
									<th>{{number_format($gravadosnc,'2','.','')}}</th>
									<th>{{number_format($exoneradasnc,'2','.','')}}</th>
									<th></th>
									<th>{{number_format($totaligvnc,'2','.','')}}</th>
									

									<th>{{number_format($totalnc,'2','.','')}}</th>
								</tr>

								<tr>
									<th>FECHA</th>
									<th>CODIGO</th>
									<th>SERIE</th>
									<th>NUMERO</th>
									<th>RUC/DNI</th>
									<th>RAZON SOCIAL</th>
									<th>MONEDA</th>
									
									<th>GRAVADO</th>
									<th>EXONERADO</th>
									<th>ICBPER</th>
									<th>IGV</th>
									
									<th>TOTAL</th>
									<th>ESTADO</th>
									
									
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comprobante)
								<tr>
								
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$comprobante->tdocod}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<!--<td>{{$comprobante->documentoidentidad}}</td>-->
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->moneda}}</td>
									<td>{{number_format($comprobante->gravado,'2','.','')}}</td>
									
									<td>{{number_format($comprobante->ccatexo,'2','.','')}}</td>
										<td>{{number_format($comprobante->icbper,'2','.','')}}</td>
									<td>{{number_format($comprobante->igv,'2','.','')}}</td>
								
									<td>{{number_format($comprobante->total,'2','.','')}}</td>
										@if($comprobante->ccacodsun=='0')
										<td style="background-color:#0FD12A">
											<font style="color:#ffff"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='8')
										<td  style="background-color:#ff0000">
											<font style="color:#ffff"><strong>ANULADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='7')
										<td>
											<font style="color:#ffff"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($comprobante->ccacodsun >'100' && $comprobante->ccacodsun <'1999')

										<td >
											<font style="color:#ffff"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($comprobante->ccacodsun > '2000' && $comprobante->ccacodsun <'3999')
										<td >
											<font style="color:#ffff"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun > '4000')
										<td >
											<font style="color:#ffff"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
									
								</tr>
								@endforeach
							</tbody>
						</table><br>

