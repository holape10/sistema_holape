
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:8pt;">
						
							<thead>

								<tr>
									
									<th style="width:20;">Sub Diario</th>
									<th style="width:30;">N&uacute;mero de Comprobante</th>
									<th style="width:15;">Fecha de Comprobante</th>
									<th style="width:15;">C&oacute;digo de Moneda</th>
									<th style="width:30;">Glosa Principal</th>
									<th style="width:70;">Tipo de Cambio</th>
									<th style="width:30;">Tipo de Conversi&oacute;n</th>
									<th style="width:30;">Flag de Conversi&oacute;n de Moneda</th>
									<th style="width:30;">Fecha Tipo de Cambio</th>
									<th style="width:30;">Cuenta Contable</th>
									<th style="width:30;">C&oacute;digo de Anexo</th>
									<th style="width:30;">C&oacute;digo de Centro de Costo</th>
									<th style="width:30;">Debe / Haber</th>
									<th style="width:30;">Importe Original</th>
									<th style="width:30;">Importe en D&oacute;lares</th>
									<th style="width:30;">Importe en Soles</th>
									<th style="width:30;">Tipo de Documento</th>
									<th style="width:30;">N&uacute;mero de Documento</th>
									<th style="width:30;">Fecha de Documento</th>
									<th style="width:30;">Fecha de Vencimiento</th>
									<th style="width:30;">C&oacute;digo de Area</th>
									<th style="width:30;">Glosa Detalle</th>
									<th style="width:30;">C&oacute;digo de Anexo Auxiliar</th>
									<th style="width:30;">Medio de Pago</th>
									<th style="width:30;">Tipo de Documento de Referencia</th>
									<th style="width:30;">Número de Documento Referencia</th>
									<th style="width:30;">Fecha Documento Referencia</th>
									<th style="width:30;">Nro Máq. Registradora Tipo Doc. Ref.</th>
									<th style="width:30;">Base Imponible Documento Referencia</th>
									<th style="width:30;">IGV Documento Provisi&oacute;n</th>
									<th style="width:30;">Tipo Referencia en estado MQ</th>
									<th style="width:30;">Número Serie Caja Registradora</th>
									<th style="width:30;">Fecha de Operaci&oacute;n</th>
									<th style="width:30;">Tipo de Tasa</th>
									<th style="width:30;">Tasa Detracci&oacute;n/Percepci&oacute;n</th>
									<th style="width:30;">Importe Base Detracci&oacute;n/Percepci&oacute;n D&oacute;lares</th>
									<th style="width:30;">Importe Base Detracci&oacute;n/Percepci&oacute;n Soles</th>
									<th style="width:30;">Tipo Cambio para 'F'</th>
									<th style="width:30;">Importe de IGV sin derecho cr&eacute;dito fiscal</th>
								
								</tr>									
							</thead>


							<!--INICIO DEL PRIMERO-->



						
							<tbody>
								
								@php
									$i=0;
								@endphp

								@foreach($total_registros_concar as $cab)
									 	@php
									 	
									 		$i=$i+1;

										@endphp
									 	<tr>
									 		
									 		<td style="width:15;">05</td>
									 		<td style="width:15;">
									 		@php
        										$mes_comprobante = date('m', strtotime($cab->ccafem));
									        	$numero_comprobante = $mes_comprobante . str_pad($i, 4, "0", STR_PAD_LEFT);
									    	@endphp
									    		{{$numero_comprobante}}									 		
									 		</td>
											<td style="width:20;">{{ date('d/m/Y', strtotime($cab->ccafem)) }}</td>
											<td style="width:30;">@if($cab->moncod=='PEN') MN @else US @endif</td> 
											<td style="width:15;">VENTAS {{$cab->des_doc}} {{$cab->serdoc}}-{{$cab->numdoc}}</td>
											<td style="width:15;">{{$cab->tipcambio}}</td>
											<td style="width:30">V</td>
											<td style="width:70">S</td>
											<td style="width:20;">{{ date('d/m/Y', strtotime($cab->ccafem)) }}</td>
											@if($cab->todcod=='07')
												<td style="width:30;text-align:right;">12121</td>
											@else
												<td style="width:30;text-align:right;">12121</td>
											@endif
											
											@if($cab->tip_doc=='03')
												<td style="width:30;text-align:right;">00000000</td>
											@else
												<td style="width:30;text-align:right;">{{$cab->numerodocumento}}</td>
											@endif
											
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;">D</td>
											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))

													@if($cab->moncod=='PEN')

														@if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv,2,'.','')}}
														@endif
														
													@else
													   @if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*$cab->tipcambio*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv*$cab->tipcambio,2,'.','')}}
														@endif

														
													@endif
													
												@else 
													0.00 
												@endif
											</td>

											<td style="width:30;text-align:right;">


												@if(is_null($cab->ccabaj))
													@if($cab->moncod=='PEN')
														0.00
													@else
														@if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv,2,'.','')}}
														@endif
													@endif
													
												@else 
													0.00
												@endif
											</td>

											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))
													@if($cab->moncod=='PEN')
														@if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv,2,'.','')}}
														@endif
													@else
														0.00
													@endif
												@else 
													0.00 
												@endif

											</td>


											<td style="width:30;">{{$cab->des_doc}}</td>
											<td style="width:30;">{{$cab->serdoc}}-{{$cab->numdoc}}</td>
											<td style="width:30;">{{ date('d/m/Y', strtotime($cab->ccafem)) }}</td>
											<td style="width:30;">{{ date('d/m/Y', strtotime($cab->ccafve)) }}</td>
											<td style="width:30;"></td>
											<td style="width:30;">{{$cab->des_doc}} {{$cab->serdoc}}-{{$cab->numdoc}}</td>
											<td style="width:30;"></td>
											<td style="width:30;"></td>
											<td style="width:30;">
											@if($cab->tipo_cp_mod=='01')
												FT
											@elseif($cab->tipo_cp_mod=='03')
												BV
											@elseif($cab->tipo_cp_mod=='07')
												NA
											@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->serie_ref}}-{{$cab->num_ref}}</td>
											<td style="width:30;text-align:right;">@if(!empty($cab->ccafem_ref)){{$cab->ccafem_ref}}@endif</td>
											<td style="width:30;text-align:right;"></td>
											
											@if($cab->ccaitv<0)
												<td style="width:30;text-align:right;">{{number_format($cab->ccatvg+$cab->ccatexo*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->ccatvg+$cab->ccatexo,'2','.','')}}</td>
											@endif

											@if($cab->ccaitv<0)
												<td style="width:30;text-align:right;">{{number_format($cab->ccaigv*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->ccaigv,'2','.','')}}</td>
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

										<!--FIN DEL PRIMER-->

										<!--INICIO DEL SEGUNDO-->

										<tr>
									 		
									 		<td style="width:15;">05</td>
									 		<td style="width:15;">
									 		@php
        										$mes_comprobante = date('m', strtotime($cab->ccafem));
									        	$numero_comprobante = $mes_comprobante . str_pad($i, 4, "0", STR_PAD_LEFT);
									    	@endphp
									    		{{$numero_comprobante}}
									 		</td>
											<td style="width:20;">{{ date('d/m/Y', strtotime($cab->ccafem)) }}</td>
											<td style="width:30;">@if($cab->moncod=='PEN') MN @else US @endif</td> 
											<td style="width:15;">VENTAS {{$cab->des_doc}} {{$cab->serdoc}}-{{$cab->numdoc}}</td>
											<td style="width:15;">{{$cab->tipcambio}}</td>
											<td style="width:30;">V</td>
											<td style="width:70">S</td>
											<td style="width:20;">{{ date('d/m/Y', strtotime($cab->ccafem)) }}</td>
											@if($cab->todcod=='07')
												<td style="width:30;text-align:right;">70111</td>
											@else
												<td style="width:30;text-align:right;">70111</td>
											@endif
											@if($cab->tip_doc=='03')
												<td style="width:30;text-align:right;">0000</td>
											@else
												<td style="width:30;text-align:right;">{{$cab->numerodocumento}}</td>
											@endif
											
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;">H</td>
											<td style="width:30;text-align:right;">
												
												@if(is_null($cab->ccabaj))

													@if($cab->moncod=='PEN')

														@if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv,2,'.','')}}
														@endif
														
													@else
													   @if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*$cab->tipcambio*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv*$cab->tipcambio,2,'.','')}}
														@endif

														
													@endif
													
												@else 
													0.00 
												@endif
											</td>

											<td style="width:30;text-align:right;">

												@if(is_null($cab->ccabaj))
													@if($cab->moncod=='PEN')
														0.00
													@else
														@if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv,2,'.','')}}
														@endif
													@endif
													
												@else 
													0.00
												@endif
											</td>

											<td style="width:30;text-align:right;">
												@if(is_null($cab->ccabaj))
													@if($cab->moncod=='PEN')
														@if($cab->ccaitv<0)
															{{number_format($cab->ccaitv*(-1),2,'.','')}}
														@else
															{{number_format($cab->ccaitv,2,'.','')}}
														@endif
													@else
														0.00
													@endif
												@else 
													0.00 
												@endif
											</td>
											<td style="width:30;">{{$cab->des_doc}}</td>
											<td style="width:30;">{{$cab->serdoc}}-{{$cab->numdoc}}</td>
											<td style="width:30;">{{ date('d/m/Y', strtotime($cab->ccafem)) }}</td>
											<td style="width:30;">{{ date('d/m/Y', strtotime($cab->ccafve)) }}</td>
											<td style="width:30;"></td>
											<td style="width:30;">{{$cab->des_doc}} {{$cab->serdoc}}-{{$cab->numdoc}}</td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;"></td>
											<td style="width:30;text-align:right;">
											@if($cab->tipo_cp_mod=='01')
												FT
											@elseif($cab->tipo_cp_mod=='03')
												BV
											@elseif($cab->tipo_cp_mod=='07')
												NA
											@endif
											</td>
											<td style="width:30;text-align:right;">{{$cab->serie_ref}}-{{$cab->num_ref}}</td>
											<td style="width:30;text-align:right;">@if(!empty($cab->ccafem_ref)){{$cab->ccafem_ref}}@endif</td>
											<td style="width:30;text-align:right;"></td>
											@if($cab->ccaitv<0)
												<td style="width:30;text-align:right;">{{number_format($cab->ccatvg+$cab->ccatexo*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->ccatvg+$cab->ccatexo,'2','.','')}}</td>
											@endif

											@if($cab->ccaitv<0)
												<td style="width:30;text-align:right;">{{number_format($cab->ccaigv*(-1),'2','.','')}}</td>
											@else
												<td style="width:30;text-align:right;">{{number_format($cab->ccaigv,'2','.','')}}</td>
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

										<!--FIN DEL SEGUNDO-->


								@endforeach

							</tbody>
						</table><br>
						


