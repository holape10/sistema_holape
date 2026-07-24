
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:8pt;">
						
							<thead>
								<tr>
									<th colspan="41" style="text-align:center;background-color:#337ab7;color:#fff;font-weight:bold;">VENTAS SIRE {{$data_emp->IdEmpresa}} - {{$data_emp->NomEmpresa}}</th>
									
								</tr>
							
							
								<tr>
									<th style="width:15;text-align:center;">Item</th>
									<th style="width:15;text-align:center;">Ruc</th>
									<th style="width:15;text-align:center;">Razon Social</th>
									<th style="width:15;text-align:center;">Periodo</th>
									<th style="width:15;text-align:center;">CAR SUNAT</th>	
									<th style="width:15;text-align:center;">Fecha de emisión</th>	
									<th style="width:15;text-align:center;">Fecha Vcto/Pago</th>	
									<th style="width:15;text-align:center;">Tipo CP/Doc.</th>	
									<th style="width:15;text-align:center;">Serie del CDP</th>	
									<th style="width:15;text-align:center;">Nro CP o Doc. Nro Inicial (Rango)</th>	
									<th style="width:15;text-align:center;">Nro Final (Rango)</th>	
									<th style="width:15;text-align:center;">Tipo Doc Identidad</th>	
									<th style="width:15;text-align:center;">Nro Doc Identidad</th>	
									<th style="width:15;text-align:center;">Apellidos Nombres/ Razón Social</th>	
									<th style="width:15;text-align:center;">Valor Facturado Exportación	</th>
									<th style="width:15;text-align:center;">BI Gravada</th>	
									<th style="width:15;text-align:center;">Dscto BI</th>	
									<th style="width:15;text-align:center;">IGV / IPM</th>	
									<th style="width:15;text-align:center;">Dscto IGV / IPM</th>	
									<th style="width:15;text-align:center;">Mto Exonerado</th>	
									<th style="width:15;text-align:center;">Mto Inafecto</th>	
									<th style="width:15;text-align:center;">ISC</th>	
									<th style="width:15;text-align:center;">BI Grav IVAP</th>	
									<th style="width:15;text-align:center;">IVAP</th>	
									<th style="width:15;text-align:center;">ICBPER</th>	
									<th style="width:15;text-align:center;">Otros Tributos</th>	
									<th style="width:15;text-align:center;">Total CP</th>	
									<th style="width:15;text-align:center;">Moneda</th>	
									<th style="width:15;text-align:center;">Tipo Cambio</th>
									<th style="width:15;text-align:center;">Fecha Emisión Doc Modificado</th>	
									<th style="width:15;text-align:center;">Tipo CP Modificado</th>	
									<th style="width:15;text-align:center;">Serie CP Modificado</th>	
									<th style="width:15;text-align:center;">Nro CP Modificado</th>	
									<th style="width:15;text-align:center;">ID Proyecto Operadores Atribución</th>	
									<th style="width:15;text-align:center;">Tipo de Nota</th>	
									<th style="width:15;text-align:center;">Est. Comp</th>	
									<th style="width:15;text-align:center;">Valor FOB Embarcado</th>	
									<th style="width:15;text-align:center;">Valor OP Gratuitas</th>	
									<th style="width:15;text-align:center;">Tipo Operación</th>	
									<th style="width:15;text-align:center;">DAM / CP</th>	
									<th style="width:15;text-align:center;">CLU</th>								
								</tr>	
							</thead>
						
							<tbody>
								
								@php
									$i=0;
								@endphp
								@foreach($ventas as $vent)
									 	@php
											$i=$i+1;
										@endphp
									 	<tr>
									 		<td style="width:15;text-align:center;">{{$i}}</td>
									 		<td style="width:15;text-align:center;">{{$vent->ruc}}</td>
											<td style="width:15;text-align:center;">{{$vent->razon_social}}</td>
											<td style="width:15;text-align:center;">{{$vent->periodo}}</td>
											<td style="width:15;text-align:center;">{{$vent->car_sunat}}</td>
											<td style="width:15;text-align:center;">{{$vent->fecha_emision}}</td>
											<td style="width:15;text-align:center;">{{$vent->fecha_vencimiento}}</td>
											<td style="width:15;text-align:center;">{{$vent->tipo_doc}}</td>
											<td style="width:15;text-align:center;">{{$vent->serie}}</td>
											<td style="width:15;text-align:center;">{{$vent->numero_inicial}}</td>
											<td style="width:15;text-align:center;">{{$vent->numero_final}}</td>
											<td style="width:15;text-align:center;">{{$vent->tipo_doc_identidad}}</td>
											<td style="width:15;text-align:center;">{{$vent->nro_doc_identidad}}</td>
											<td style="width:15;text-align:center;">{{$vent->cliente}}</td>
											<td style="width:15;text-align:center;">{{$vent->valor_facturacion}}</td>
											<td style="width:15;text-align:center;">{{$vent->valor_gravada}}</td>
											<td style="width:15;text-align:center;">{{$vent->descuento_BI}}</td>
											<td style="width:15;text-align:center;">{{$vent->igv_ipm}}</td>
											<td style="width:15;text-align:center;">{{$vent->dscto_igv_ipm}}</td>
											<td style="width:15;text-align:center;">{{$vent->mto_exonerado}}</td>
											<td style="width:15;text-align:center;">{{$vent->mto_inafecto}}</td>
											<td style="width:15;text-align:center;">{{$vent->isc}}</td>
											<td style="width:15;text-align:center;">{{$vent->bi_grav_ivap}}</td>
											<td style="width:15;text-align:center;">{{$vent->ivap}}</td>
											<td style="width:15;text-align:center;">{{$vent->icbper}}</td>
											<td style="width:15;text-align:center;">{{$vent->otros_tributos}}</td>
											<td style="width:15;text-align:center;">{{$vent->total_cp}}</td>
											<td style="width:15;text-align:center;">{{$vent->moneda}}</td>
											<td style="width:15;text-align:center;">{{$vent->tipo_cambio}}</td>
											<td style="width:15;text-align:center;">{{$vent->fecha_emision_doc_mod}}</td>
											<td style="width:15;text-align:center;">{{$vent->tipo_cp_mod}}</td>
											<td style="width:15;text-align:center;">{{$vent->serie_cp_mod}}</td>
											<td style="width:15;text-align:center;">{{$vent->nro_cp_mod}}</td>
											<td style="width:15;text-align:center;">{{$vent->id_proy_ope_atrib}}</td>
											<td style="width:15;text-align:center;">{{$vent->tipo_nota}}</td>
											<td style="width:15;text-align:center;">{{$vent->est_comp}}</td>
											<td style="width:15;text-align:center;">{{$vent->valor_fob_emb}}</td>
											<td style="width:15;text-align:center;">{{$vent->valor_op_grat}}</td>
											<td style="width:15;text-align:center;">{{$vent->tipo_operacion}}</td>
											<td style="width:15;text-align:center;">{{$vent->dam_cp}}</td>
											<td style="width:15;text-align:center;">{{$vent->clu}}</td>
										</tr>
								@endforeach
							</tbody>
						</table>
						


