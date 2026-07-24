
			<table id="dtHorizontalExample"  class="table table-responsive  table-bordered table-sm" style="width:100%;font-size:8pt;">
							<thead>
								<tr>
									<th colspan="18"  style="background-color:#337ab7;color:#fff;text-align:center;"><strong>REPORTE DE VENTAS DESDE: {{$fec_ini}} HASTA {{$fec_fin}}</strong></th>
								</tr>
								
								<tr>
									<th colspan="14"  style="background-color:#337ab7;color:#fff;text-align:center;font-weight:bold;">COMPROBANTE</th>
									<th colspan="4"  style="background-color:#337ab7;color:#fff;text-align:center;font-weight:bold;">DOCUMENTO DE REFERENCIA</th>
								</tr>

								<tr>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">FECHA</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">CODIGO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">SERIE</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">NUMERO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">TIPO<br>DOCUMENTOS</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">RUC/DNI</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">RAZON SOCIAL</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">MONEDA</th>
									
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">GRAVADO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">EXONERADO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">ICBPER</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">IGV</th>
									
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">TOTAL</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">ESTADO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">FECHA</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">TIPO<BR>DOCUMENTO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">SERIE</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">NUMERO</th>
									
								</tr>
							</thead>
							
							<tbody>

								<tr>
									<td colspan="18" style="text-align:center;font-weight:bold;background-color:#f4f4f4;"><center><strong>FACTURAS</strong></center></td>
								</tr>
								@foreach($factura as $fact)
								
								<tr>
								
								 	<td>{{Carbon::parse($fact->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$fact->tdocod}}</td>
									<td>{{$fact->serie}}</td>
									<td>{{$fact->numero}}</td>
									<td>{{$fact->documentoidentidad}}</td>
									<td>{{$fact->numerodocumento}}</td>
									<td>{{$fact->cliente}}</td>
									<td>{{$fact->moneda}}</td>
									<td style="text-align:right;">{{number_format($fact->gravado,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->exonerado - $fact->tot_icbper,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->tot_icbper,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->igv,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->total,'2','.',',')}}</td>
										
									@if($fact->ccacodsun=='0')
										<td style="background-color:#57B103;">
											<font color="white"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($fact->ccacodsun=='8')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>ANULADO</strong></font>
										</td>
										@elseif($fact->ccacodsun=='7')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($fact->ccacodsun >'100' && $fact->ccacodsun <'1999')

										<td style="background-color:#EA9A0C;">
											<font color="white"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($fact->ccacodsun > '2000' && $fact->ccacodsun <'3999')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($fact->ccacodsun > '4000')
										<td style="background-color:#EA9A0C;">
											<font color="white"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
										

										<td>@if(!empty($fact->fecha_ref)){{Carbon::parse($fact->fecha_ref)->format('d-m-Y')}} @endif</td>
										<td>{{$fact->tdocod_ref}}</td>
										<td>{{$fact->serie_ref}}</td>
										<td>{{$fact->num_ref}}</td>

									
								</tr>
								@endforeach

								<tr>
									<td colspan="18" style="text-align:center;font-weight:bold;background-color:#f4f4f4;"><center><STRONG>BOLETAS</STRONG></center></td>
								</tr>
								@foreach($boleta as $bol)
								
								<tr>
									
								 	<td>{{Carbon::parse($bol->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$bol->tdocod}}</td>
									<td>{{$bol->serie}}</td>
									<td>{{$bol->numero}}</td>
									<td>{{$bol->documentoidentidad}}</td>
									<td>{{$bol->numerodocumento}}</td>
									<td>{{$bol->cliente}}</td>
									<td>{{$bol->moneda}}</td>
									<td style="text-align:right;">{{number_format($bol->gravado,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($bol->exonerado - $bol->tot_icbper,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($bol->tot_icbper,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($bol->igv,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($bol->total,'2','.',',')}}</td>
										
										@if($bol->ccacodsun=='0')
										<td style="background-color:#57B103;">
											<font color="white"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($bol->ccacodsun=='8')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>ANULADO</strong></font>
										</td>
										@elseif($bol->ccacodsun=='7')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($bol->ccacodsun >'100' && $bol->ccacodsun <'1999')

										<td style="background-color:#EA9A0C;">
											<font color="white"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($bol->ccacodsun > '2000' && $bol->ccacodsun <'3999')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($bol->ccacodsun > '4000')
										<td style="background-color:#EA9A0C;">
											<font color="white"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
										

										<td>@if(!empty($fact->fecha_ref)){{Carbon::parse($bol->fecha_ref)->format('d-m-Y')}}@endif</td>
										<td>{{$bol->tdocod_ref}}</td>
										<td>{{$bol->serie_ref}}</td>
										<td>{{$bol->num_ref}}</td>

								</tr>
								@endforeach

								<tr>
									<td colspan="18" style="text-align:center;font-weight:bold;background-color:#f4f4f4;"><center><STRONG>NOTAS DE CREDITOS</STRONG></center></td>
								</tr>
								@foreach($nota_credito as $nc)
								
								<tr>
								
								 	
								 	<td>{{Carbon::parse($nc->fecha)->format('d-m-Y')}}</td>
								 	<td>{{$nc->tdocod}}</td>
									<td>{{$nc->serie}}</td>
									<td>{{$nc->numero}}</td>
									<td>{{$nc->documentoidentidad}}</td>
									<td>{{$nc->numerodocumento}}</td>
									<td>{{$nc->cliente}}</td>
									<td>{{$nc->moneda}}</td>
									<td style="text-align:right;">{{number_format($nc->gravado,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($nc->exonerado,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($nc->tot_icbper,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($nc->igv,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($nc->total,'2','.',',')}}</td>
										
										@if($nc->ccacodsun=='0')
										<td style="background-color:#57B103;">
											<font color="white"><strong>ACEPTADO</strong></font>
										</td>
										@elseif($nc->ccacodsun=='8')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>ANULADO</strong></font>
										</td>
										@elseif($nc->ccacodsun=='7')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>ANULACION EN PROCESO</strong></font>
										</td>

										@elseif($nc->ccacodsun >'100' && $nc->ccacodsun <'1999')

										<td style="background-color:#EA9A0C;">
											<font color="white"><strong>CORREGIR Y ENVIAR</strong></font>
										</td>

										@elseif($nc->ccacodsun > '2000' && $nc->ccacodsun <'3999')
										<td style="background-color:#D83E0D;">
											<font color="white"><strong>RECHAZADO</strong></font>
										</td>
										@elseif($nc->ccacodsun > '4000')
										<td style="background-color:#EA9A0C;">
											<font color="white"><strong>OBSERVADO</strong></font>
										</td>
										@else
										<td>
											
										</td>
										@endif
										
										<td></td>
										<td></td>
										<td></td>
										<td></td>
								</tr>
								@endforeach
								
								<tr>
									<td colspan="18"  style="font-weight:bold;background-color:#f4f4f4;"></td>
								</tr>
								<tr>
									<td colspan="9"></td>
									<td style="font-weight:bold;background-color:#337ab7;color:#fff;"></td>
									<td style="font-weight:bold;background-color:#337ab7;color:#fff;">SUBTOTAL</td>
									<td style="font-weight:bold;background-color:#337ab7;color:#fff;">IGV</td>
									<td style="font-weight:bold;background-color:#337ab7;color:#fff;">TOTAL</td>
									<td></td>
								</tr>
								<tr>
									<td colspan="9"></td>
									<td style="font-weight:bold;background-color:#337ab7;color:#fff;">VENTAS</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($total_ventas_sunat-$igv_ventas,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{$igv_ventas}}</td>
									<td style="text-align:right;font-weight:bold;">{{$total_ventas_sunat}}</td>
									<td></td>
								</tr>
								<tr>
									<td colspan="9"></td>
									<td style="font-weight:bold;background-color:#337ab7;color:#fff;">NOTAS CREDITOS</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($total_notas_creditos-$igv_notas_creditos,'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;">{{$igv_notas_creditos}}</td>
									<td style="text-align:right;font-weight:bold;">{{$total_notas_creditos}}</td>
									<td></td>
								</tr>
								<tr>
									<td colspan="9"></td>
									<td style="font-weight:bold;background-color:#337ab7;color:#fff;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;background-color:#337ab7;color:#fff;">{{number_format(($total_ventas_sunat-$igv_ventas)-($total_notas_creditos-$igv_notas_creditos),'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;background-color:#337ab7;color:#fff;">{{number_format(($igv_ventas-$igv_notas_creditos),'2','.','')}}</td>
									<td style="text-align:right;font-weight:bold;background-color:#337ab7;color:#fff;">{{number_format(($total_ventas_sunat-$total_notas_creditos),'2','.','')}}</td>
									<td></td>
								</tr>
							</tbody>
						</table><br>

