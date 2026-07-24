
			<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr>
									<th colspan="13"><center><strong>REPORTE DE VENTAS DESDE: {{$fecin}} HASTA {{$fecfin}}  | {{$hora_rep}}</strong></center>
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

								<tr>
									<td colspan="13"><center><strong>FACTURAS</strong></center></td>
								</tr>
								@foreach($facturas as $comprobante)
								
								<tr>
								
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$comprobante->tdocod}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<!--<td>{{$comprobante->documentoidentidad}}</td>-->
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->moneda}}</td>
									<td>{{number_format($comprobante->gravado,'2','.',',')}}</td>
									
									<td>{{number_format($comprobante->ccatexo,'2','.',',')}}</td>
										<td>{{number_format($comprobante->tot_icbper,'2','.',',')}}</td>
									<td>{{number_format($comprobante->igv,'2','.',',')}}</td>
								
									<td>{{number_format($comprobante->total,'2','.',',')}}</td>
										@if($comprobante->ccacodsun=='0')
										<td style="background-color:green;">
											<font color="white"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='8')
										<td style="background-color:red;">
											<font color="white"><strong>ANULADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='7')
										<td style="background-color:red;">
											<font color="white"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($comprobante->ccacodsun >'100' && $comprobante->ccacodsun <'1999')

										<td style="background-color:orange;">
											<font color="white"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($comprobante->ccacodsun > '2000' && $comprobante->ccacodsun <'3999')
										<td style="background-color:red;">
											<font color="white"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun > '4000')
										<td style="background-color:green;">
											<font color="white"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
									
								</tr>
								@endforeach

								<tr>
									<td colspan="13"><center><STRONG>BOLETAS</STRONG></center></td>
								</tr>
								@foreach($boletas as $comprobante)
								
								<tr>
								
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$comprobante->tdocod}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<!--<td>{{$comprobante->documentoidentidad}}</td>-->
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->moneda}}</td>
									<td>{{number_format($comprobante->gravado,'2','.',',')}}</td>
									
									<td>{{number_format($comprobante->ccatexo,'2','.',',')}}</td>
										<td>{{number_format($comprobante->icbper,'2','.',',')}}</td>
									<td>{{number_format($comprobante->igv,'2','.',',')}}</td>
								
									<td>{{number_format($comprobante->total,'2','.',',')}}</td>
										@if($comprobante->ccacodsun=='0')
										<td style="background-color:green;">
											<font color="white"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='8')
										<td style="background-color:red;">
											<font color="white"><strong>ANULADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='7')
										<td style="background-color:red;">
											<font color="white"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($comprobante->ccacodsun >'100' && $comprobante->ccacodsun <'1999')

										<td style="background-color:orange;">
											<font color="white"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($comprobante->ccacodsun > '2000' && $comprobante->ccacodsun <'3999')
										<td style="background-color:red;">
											<font color="white"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun > '4000')
										<td style="background-color:green;">
											<font color="white"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
									
								</tr>
								@endforeach

								<tr>
									<td colspan="13"><center><STRONG>NOTAS DE CREDITOS</STRONG></center></td>
								</tr>
								@foreach($notascreditos as $comprobante)
								
								<tr>
								
								 	<td>{{Carbon::parse($comprobante->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$comprobante->tdocod}}</td>
									<td>{{$comprobante->serie}}</td>
									<td>{{$comprobante->numero}}</td>
									<!--<td>{{$comprobante->documentoidentidad}}</td>-->
									<td>{{$comprobante->numerodocumento}}</td>
									<td>{{$comprobante->cliente}}</td>
									<td>{{$comprobante->moneda}}</td>
									<td>{{number_format($comprobante->gravado,'2','.',',')}}</td>
									
									<td>{{number_format($comprobante->ccatexo,'2','.',',')}}</td>
										<td>{{number_format($comprobante->icbper,'2','.',',')}}</td>
									<td>{{number_format($comprobante->igv,'2','.',',')}}</td>
								
									<td>{{number_format($comprobante->total,'2','.',',')}}</td>
										@if($comprobante->ccacodsun=='0')
										<td style="background-color:green;">
											<font color="white"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='8')
										<td style="background-color:red;">
											<font color="white"><strong>ANULADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun=='7')
										<td style="background-color:red;">
											<font color="white"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($comprobante->ccacodsun >'100' && $comprobante->ccacodsun <'1999')

										<td style="background-color:orange;">
											<font color="white"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($comprobante->ccacodsun > '2000' && $comprobante->ccacodsun <'3999')
										<td style="background-color:red;">
											<font color="white"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($comprobante->ccacodsun > '4000')
										<td style="background-color:green;">
											<font color="white"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
									
								</tr>
								@endforeach

							</tbody>
						</table><br>

