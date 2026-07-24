
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
						
							<thead>

								<tr>
									<th style="width:15;text-align:center;">Item</th>
									<th style="width:15;text-align:center;">cod_suc</th>
									<th style="width:15;text-align:center;">cod_mov</th>
									<th style="width:15;text-align:center;">ccafem</th>
									<th style="width:15;text-align:center;">ccafve</th>
									<th style="width:15;text-align:center;">tdocod</th>
									<th style="width:15;text-align:center;">serdoc</th>
									<th style="width:15;text-align:center;">numdoc</th>
									<th style="width:15;text-align:center;">moncod</th>
									<th style="width:15;text-align:center;">tdicod</th>
									<th style="width:35;text-align:center;">ccandi</th>
									<th style="width:35;text-align:center;">direccion</th>
									<th style="width:35;text-align:center;">ccanom</th>
									<th style="width:15;text-align:center;">totalcontado</th>
									<th style="width:15;text-align:center;">totalcredito</th>
									<th style="width:15;text-align:center;">tipcambio</th>
									<th style="width:15;text-align:center;">ccatvg</th>
									<th style="width:15;text-align:center;">ccatexo</th>
									<th style="width:15;text-align:center;">ccaigv</th>
									<th style="width:15;text-align:center;">ccaitv</th>
									<th style="width:15;text-align:center;">procod</th>
									<th style="width:35;text-align:center;">cdecan</th>
									<th style="width:35;text-align:center;">umecod</th>
									<th style="width:35;text-align:center;">costo</th>
									<th style="width:35;text-align:center;">cdedes</th>
									<th style="width:15;text-align:center;">cpe_det_factor</th>
									<th style="width:15;text-align:center;">tigcod</th>
									<th style="width:15;text-align:center;">cdevun</th>
									<th style="width:15;text-align:center;">cdepuni</th>
									<th style="width:15;text-align:center;">valor_unitario</th>
									<th style="width:15;text-align:center;">precio_ref</th>
									<th style="width:15;text-align:center;">mon_icbper_det</th>
									<th style="width:15;text-align:center;">icbper_1</th>
									<th style="width:15;text-align:center;">icbper_det</th>
									<th style="width:15;text-align:center;">cdeigv</th>
									<th style="width:15;text-align:center;">cdepve</th>
									<th style="width:15;text-align:center;">cdevve</th>
									<th style="width:15;text-align:center;">ccabaj</th>
									<th style="width:15;text-align:right;">num_ref</th>
									<th style="width:15;text-align:right;">serie_ref</th>
									<th style="width:15;text-align:right;">tdocod_ref</th>
									<th style="width:15;text-align:right;">fecha_ref</th>
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($ventas_migrar as $cab)
									 	@php
											$i=$i+1;
										@endphp
									 	<tr>
									 		<td style="width:15;text-align:center;">{{$i}}</td>
									 		<td style="width:15;text-align:center;">{{$cab->cod_suc}}</td>
									 		<td style="width:15;text-align:center;">{{$cab->cod_mov}}</td>
											<td style="width:15;text-align:center;">{{Carbon::parse($cab->ccafem)->format('Y-m-d')}}</td>
											<td style="width:15;text-align:center;">{{Carbon::parse($cab->ccafve)->format('Y-m-d')}}</td>
											<td style="width:15;text-align:center;">{{$cab->tdocod}}</td> 
											<td style="width:15;text-align:center;">{{$cab->serdoc}}</td>
											<td style="width:15;text-align:right;">{{$cab->numdoc}}</td>
											<td style="width:15;text-align:right;">{{$cab->moncod}}</td>
											<td style="width:15;text-align:right;">{{$cab->tdicod}}</td>
											<td style="width:35;text-align:right;">{{$cab->ccandi}}</td>
											<td style="width:35;text-align:right;">{{$cab->direccion}}</td>
											<td style="width:35">{{$cab->ccanom}}</td>
											<td style="width:15;text-align:right;">{{$cab->totalcontado}}</td>
											<td style="width:15;text-align:right;">{{$cab->totalcredito}}</td>
											<td style="width:15;text-align:right;">{{$cab->tipcambio}}</td>
											<td style="width:15;text-align:right;">{{$cab->ccatvg}}</td>
											<td style="width:15;text-align:right;">{{$cab->ccatexo}}</td>
											<td style="width:15;text-align:right;">{{$cab->ccaigv}}</td>
											<td style="width:15;text-align:right;">{{$cab->ccaitv}}</td>
											<td style="width:15;text-align:right;">{{$cab->procod}}</td>
											<td style="width:15;text-align:right;">{{$cab->cdecan}}</td>
											<td style="width:15;text-align:right;">{{$cab->umecod}}</td>
											<td style="width:15;text-align:right;">{{$cab->costo}}</td>
											<td style="width:35;text-align:right;">{{$cab->cdedes}}</td>
											<td style="width:15;text-align:right;">{{$cab->cpe_det_factor}}</td>
											<td style="width:15;text-align:right;">{{$cab->tigcod}}</td>
											<td style="width:15;text-align:right;">{{$cab->cdevun}}</td>
											<td style="width:15;text-align:right;">{{$cab->cdepuni}}</td>
											<td style="width:15;text-align:right;">{{$cab->valor_unitario}}</td>
											<td style="width:15;text-align:right;">{{$cab->precio_ref}}</td>
											<td style="width:15;text-align:right;">{{$cab->mon_icbper_det}}</td>
											<td style="width:15;text-align:right;">{{$cab->icbper_1}}</td>
											<td style="width:15;text-align:right;">{{$cab->icbper_det}}</td>
											<td style="width:15;text-align:right;">{{$cab->cdeigv}}</td>
											<td style="width:15;text-align:right;">{{$cab->cdepve}}</td>
											<td style="width:15;text-align:right;">{{$cab->cdevve}}</td>
											<td style="width:15;text-align:right;">{{$cab->ccabaj}}</td>
											<td style="width:15;text-align:right;">{{$cab->num_ref}}</td>
											<td style="width:15;text-align:right;">{{$cab->serie_ref}}</td>
											<td style="width:15;text-align:right;">{{$cab->tdocod_ref}}</td>
											<td style="width:15;text-align:right;">{{$cab->fecha_ref}}</td>
										</tr>
								@endforeach
									<tr>
								
								</tr>
								
								
							</tbody>
						</table>
						


