
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:8pt;">
						
							<thead>

								<tr>
									<th style="width:20;text-align:center;">Campo</th>
									<th style="width:20;text-align:center;">Sub Diario</th>
									<th style="width:30;text-align:center;">N&uacute;mero de Comprobante</th>
									<th style="width:15;text-align:center;">Fecha de Comprobante</th>
									<th style="width:15;text-align:center;">C&oacute;digo de Moneda</th>
									<th style="width:30;text-align:center;">Glosa Principal</th>
									<th style="width:70;text-align:center;">Tipo de Cambio</th>
									<th style="width:30;text-align:center;">Tipo de Conversi&oacute;n</th>
									<th style="width:30;text-align:center;">Flag de Conversi&oacute;n de Moneda</th>
									<th style="width:30;text-align:center;">Fecha Tipo de Cambio</th>
									<th style="width:30;text-align:center;">Cuenta Contable</th>
									<th style="width:30;text-align:center;">C&oacute;digo de Anexo</th>
									<th style="width:30;text-align:center;">C&oacute;digo de Centro de Costo</th>
									<th style="width:30;text-align:center;">Debe / Haber</th>
									<th style="width:30;text-align:center;">Importe Original</th>
									<th style="width:30;text-align:center;">Importe en D&oacute;lares</th>
									<th style="width:30;text-align:center;">Importe en Soles</th>
									<th style="width:30;text-align:center;">Tipo de Documento</th>
									<th style="width:30;text-align:center;">N&uacute;mero de Documento</th>
									<th style="width:30;text-align:center;">Fecha de Documento</th>
									<th style="width:30;text-align:center;">Fecha de Vencimiento</th>
									<th style="width:30;text-align:center;">C&oacute;digo de Area</th>
									<th style="width:30;text-align:center;">Glosa Detalle</th>
									<th style="width:30;text-align:center;">C&oacute;digo de Anexo Auxiliar</th>
									<th style="width:30;text-align:center;">Medio de Pago</th>
									<th style="width:30;text-align:center;">Tipo de Documento de Referencia</th>
									<th style="width:30;text-align:center;">Número de Documento Referencia</th>
									<th style="width:30;text-align:center;">Fecha Documento Referencia</th>
									<th style="width:30;text-align:center;">Nro Máq. Registradora Tipo Doc. Ref.</th>
									<th style="width:30;text-align:center;">Base Imponible Documento Referencia</th>
									<th style="width:30;text-align:center;">IGV Documento Provisi&oacute;n</th>
									<th style="width:30;text-align:center;">Tipo Referencia en estado MQ</th>
									<th style="width:30;text-align:center;">Número Serie Caja Registradora</th>
									<th style="width:30;text-align:center;">Fecha de Operaci&oacute;n</th>
									<th style="width:30;text-align:center;">Tipo de Tasa</th>
									<th style="width:30;text-align:center;">Tasa Detracci&oacute;n/Percepci&oacute;n</th>
									<th style="width:30;text-align:center;">Importe Base Detracci&oacute;n/Percepci&oacute;n D&oacute;lares</th>
									<th style="width:30;text-align:center;">Importe Base Detracci&oacute;n/Percepci&oacute;n Soles</th>
									<th style="width:30;text-align:center;">Tipo Cambio para 'F'</th>
									<th style="width:30;text-align:center;">Importe de IGV sin derecho cr&eacute;dito fiscal</th>
								
								</tr>	
								
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp

								@foreach($compras as $cab)
									 	@php
									 	
									 		$mes =  substr($cab->fecha_emision,3,2);
								            $ano =  substr($cab->fecha_emision,6,4);
								            $dia =  substr($cab->fecha_emision,0,2);
								         
											$i=$i+1;

										@endphp
									 	<tr>
									 		<td style="width:15;text-align:center;"></td>
									 		<td style="width:15;text-align:center;">11</td>
									 		<td style="width:15;text-align:center;">{{$mes.str_pad($i,4,"0", STR_PAD_LEFT)}}</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:center;">@if($cab->moneda=='PEN') MN @else US @endif</td> 
											<td style="width:15;text-align:center;">COMPRAS {{$cab->des_doc}} {{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:15;text-align:right;">{{$cab->tipo_cambio}}</td>
											<td style="width:30;text-align:right;">V</td>
											<td style="width:70">S</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											@if($cab->tipo_doc=='07')
												<td style="width:30;text-align:right;">601101</td>
											@else
												<td style="width:30;text-align:right;">601101</td>
											@endif
											
											@if($cab->tipo_doc=='03')
												<td style="width:30;text-align:right;">0000</td>
											@else
												<td style="width:30;text-align:right;">{{$cab->nro_doc_identidad}}</td>
											@endif
											
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">D</td>
											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))

													@if($cab->moneda=='PEN')

														@if($cab->total_cp<0)
															{{number_format($cab->total_cp,2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
														
													@else
													   @if($cab->total_cp<0)
															{{number_format($cab->total_cp,2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif

														
													@endif
													
												@else 
													0.00 
												@endif
											</td>

											<td style="width:30;text-align:right;">


												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														0.00
													@else
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp/$cab->tipo_cambio,2,'.','')}}
														@else
															{{number_format($cab->total_cp/$cab->tipo_cambio,2,'.','')}}
														@endif
													@endif
													
												@else 
													0.00
												@endif
											</td>

											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
													@else
														0.00
													@endif
												@else 
													0.00 
												@endif

											</td>


											<td style="width:30;text-align:right;">{{$cab->des_doc}}</td>
											<td style="width:30;text-align:right;">{{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_vencimiento}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:left;">COMPRAS {{$cab->des_doc}} {{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">
											@if($cab->tipo_cp_mod=='01')
												FT
											@elseif($cab->tipo_cp_mod=='03')
												BV
											@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->serie_cp_mod}}-{{$cab->nro_cp_mod}}</td>
											<td style="width:30;text-align:right;">@if(!empty($cab->fecha_emision_doc_mod)){{$cab->fecha_emision_doc_mod}}@endif</td>
											<td style="width:30;text-align:right;"></td>
											
											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado,'2','.','')}}</td>
											@endif

											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm,'2','.','')}}</td>
											@endif
											
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>

										</tr>


										<tr>
									 		<td style="width:15;text-align:center;"></td>
									 		<td style="width:15;text-align:center;">11</td>
									 		<td style="width:15;text-align:center;">{{$mes.str_pad($i,4,"0", STR_PAD_LEFT)}}</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:center;">@if($cab->moneda=='PEN') MN @else US @endif</td> 
											<td style="width:15;text-align:center;">COMPRAS {{$cab->des_doc}} {{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:15;text-align:right;">{{$cab->tipo_cambio}}</td>
											<td style="width:30;text-align:right;">V</td>
											<td style="width:70">S</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											@if($cab->tipo_doc=='07')
												<td style="width:30;text-align:right;">421201</td>
											@else
												<td style="width:30;text-align:right;">421201</td>
											@endif
											@if($cab->tipo_doc=='03')
												<td style="width:30;text-align:right;">0000</td>
											@else
												<td style="width:30;text-align:right;">{{$cab->nro_doc_identidad}}</td>
											@endif
											
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">H</td>
											<td style="width:30;text-align:right;">
												
												@if(is_null($cab->ccabaj))

													@if($cab->moneda=='PEN')

														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
														
													@else
													   @if($cab->total_cp<0)
															{{number_format($cab->total_cp*$cab->tipo_cambio*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp*$cab->tipo_cambio,2,'.','')}}
														@endif

														
													@endif
													
												@else 
													0.00 
												@endif
											</td>

											<td style="width:30;text-align:right;">

												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														0.00
													@else
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
													@endif
													
												@else 
													0.00
												@endif
											</td>

											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
													@else
														0.00
													@endif
												@else 
													0.00 
												@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->des_doc}}</td>
											<td style="width:30;text-align:right;">{{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_vencimiento}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:left;">COMPRAS {{$cab->des_doc}} {{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">
											@if($cab->tipo_cp_mod=='01')
												FT
											@elseif($cab->tipo_cp_mod=='03')
												BV
											@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->serie_cp_mod}}-{{$cab->nro_cp_mod}}</td>
											<td style="width:30;text-align:right;">@if(!empty($cab->fecha_emision_doc_mod)){{$cab->fecha_emision_doc_mod}}@endif</td>
											<td style="width:30;text-align:right;"></td>
											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado,'2','.','')}}</td>
											@endif

											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm,'2','.','')}}</td>
											@endif
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>

										</tr>












										@if($cab->tipo_doc=='01' || $cab->tipo_doc=='03')
										<tr>
									 		<td style="width:15;text-align:center;"></td>
									 		<td style="width:15;text-align:center;">22</td>
									 		<td style="width:15;text-align:center;">{{$mes.str_pad($i,4,"0", STR_PAD_LEFT)}}</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:center;">@if($cab->moneda=='PEN') MN @else US @endif</td> 
											<td style="width:15;text-align:center;">PAGO PROVEEDOR</td>
											<td style="width:15;text-align:right;">{{$cab->tipo_cambio}}</td>
											<td style="width:30;text-align:right;">V</td>
											<td style="width:70">S</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:right;">421201</td>
											@if($cab->tipo_doc=='03')
												<td style="width:30;text-align:right;">0000</td>
											@else
												<td style="width:30;text-align:right;">{{$cab->nro_doc_identidad}}</td>
											@endif
											
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">D</td>
											<td style="width:30;text-align:right;">
												
												@if(is_null($cab->ccabaj))

													@if($cab->moneda=='PEN')

														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
														
													@else
													   @if($cab->total_cp<0)
															{{number_format($cab->total_cp*$cab->tipo_cambio*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp*$cab->tipo_cambio,2,'.','')}}
														@endif

														
													@endif
													
												@else 
													0.00 
												@endif
											</td>

											<td style="width:30;text-align:right;">

												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														0.00
													@else
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
													@endif
													
												@else 
													0.00
												@endif
											</td>

											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
													@else
														0.00
													@endif
												@else 
													0.00 
												@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->des_doc}}</td>
											<td style="width:30;text-align:right;">{{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_vencimiento}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:left;">PAGO PROVEEDOR {{$cab->des_doc}} {{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">
											@if($cab->tipo_cp_mod=='01')
												FT
											@elseif($cab->tipo_cp_mod=='03')
												BV
											@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->serie_cp_mod}}-{{$cab->nro_cp_mod}}</td>
											<td style="width:30;text-align:right;">@if(!empty($cab->fecha_emision_doc_mod)){{$cab->fecha_emision_doc_mod}}@endif</td>
											<td style="width:30;text-align:right;"></td>
											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado,'2','.','')}}</td>
											@endif

											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm,'2','.','')}}</td>
											@endif
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>

										</tr>


											<tr>
									 		<td style="width:15;text-align:center;"></td>
									 		<td style="width:15;text-align:center;">22</td>
									 		<td style="width:15;text-align:center;">{{$mes.str_pad($i,4,"0", STR_PAD_LEFT)}}</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:center;">@if($cab->moneda=='PEN') MN @else US @endif</td> 
											<td style="width:15;text-align:center;">PAGO PROVEEDOR</td>
											<td style="width:15;text-align:right;">{{$cab->tipo_cambio}}</td>
											<td style="width:30;text-align:right;">V</td>
											<td style="width:70">S</td>
											<td style="width:20;text-align:center;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:right;">101101</td>
											@if($cab->tipo_doc=='03')
												<td style="width:30;text-align:right;">001</td>
											@else
												<td style="width:30;text-align:right;">001</td>
											@endif
											
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">H</td>
											<td style="width:30;text-align:right;">
												
												@if(is_null($cab->ccabaj))

													@if($cab->moneda=='PEN')

														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
														
													@else
													   @if($cab->total_cp<0)
															{{number_format($cab->total_cp*$cab->tipo_cambio*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp*$cab->tipo_cambio,2,'.','')}}
														@endif

														
													@endif
													
												@else 
													0.00 
												@endif
											</td>

											<td style="width:30;text-align:right;">

												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														0.00
													@else
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
													@endif
													
												@else 
													0.00
												@endif
											</td>

											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))
													@if($cab->moneda=='PEN')
														@if($cab->total_cp<0)
															{{number_format($cab->total_cp*(-1),2,'.','')}}
														@else
															{{number_format($cab->total_cp,2,'.','')}}
														@endif
													@else
														0.00
													@endif
												@else 
													0.00 
												@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->des_doc}}</td>
											<td style="width:30;text-align:right;">{{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_emision}}</td>
											<td style="width:30;text-align:right;">{{$cab->fecha_vencimiento}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:left;">PAGO PROVEEDOR {{$cab->des_doc}} {{$cab->serie}}-{{$cab->numero_inicial}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">
											@if($cab->tipo_cp_mod=='01')
												FT
											@elseif($cab->tipo_cp_mod=='03')
												BV
											@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->serie_cp_mod}}-{{$cab->nro_cp_mod}}</td>
											<td style="width:30;text-align:right;">@if(!empty($cab->fecha_emision_doc_mod)){{$cab->fecha_emision_doc_mod}}@endif</td>
											<td style="width:30;text-align:right;"></td>
											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->valor_gravada+$cab->mto_exonerado,'2','.','')}}</td>
											@endif

											@if($cab->total_cp<0)
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->igv_ipm,'2','.','')}}</td>
											@endif
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>

										</tr>

										@endif
								@endforeach

							</tbody>
						</table><br>
						


