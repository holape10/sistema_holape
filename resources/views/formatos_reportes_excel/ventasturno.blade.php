
          		
							<table i>
							<thead>
							<tr style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;">
								<th width="50"  style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;" colspan='2'><center>INGRESOS</center></th>
								<th width="50"  style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;"  colspan='2'><center>SALIDAS</center></th>
								<th width="50"  style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;" colspan='2'><center>ESTADO DE PAGO</center></th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<th width="80" >Fondo de Caja</th>
								<th width="80" style="text-align:right;">S/. {{number_format($datos->monto,'2','.',',')}}</th>
								<th width="80" >Gastos Caja</th>
								<th width="80" style="text-align:right;">S/. {{number_format($totalgas,'2','.',',')}}</th>
								<th width="80" >Ventas Credito</th>
								<th width="80" style="text-align:right;">S/. {{number_format($credito,'2','.',',')}}</th>
							</tr>
								
													
							<tr>
								<th >Ingresos Caja</th>
								<th style="text-align:right;">S/. {{number_format($totaling,'2','.',',')}}</th>
								<th >Compras</th>
								<th style="text-align:right;">S/. {{number_format($compras,'2','.',',')}}</th>
								<th >Ventas Contado</th>
								<th style="text-align:right;">S/. {{number_format($total,'2','.',',')}}</th>
							</tr>

							@if(!empty($sum_mp))
									@foreach($sum_mp as $mp)
									
									<tr>
										<th >{{ucwords(strtolower($mp->nom_med_pag))}}</th>
										<th style="text-align:right;">S/. {{number_format($mp->monto_total,'2','.','')}}</th>
										<th ></th>
										<th></th>
										<th></th>
										<th></th>
								</tr>

									@endforeach
									@endif

						
							</tbody>
							
									
								
									
									
							</table>
							<br>



							<table id=""  class="table table-bordered table-hover table-striped" >
							<thead>
							<tr style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;">
								<th  style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;" colspan='6'><center>RESUMEN</center></th>
							
							</tr>
							</thead>
						
							<tbody>
							<tr>
								<th  style="font-size:10pt;font-weight:bold;width:250px;">(+) TOTAL EFECTIVO</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo+$datos->monto+$totaling),'2','.',',')}}</th>
								<th  style="font-size:10pt;font-weight:bold;width:150px;"></th>
								<th  style="font-size:10pt;font-weight:bold;width:250px;">(+) VENTAS EFECTIVO</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo),'2','.',',')}}</th>
							</tr>
							<tr>
								<th style="font-size:10pt;font-weight:bold;width:250px;">(-) TOTAL GASTOS</th>
								<th style="text-align:right;">S/. {{number_format(($totalgas+$compras),'2','.',',')}}</th>
									<th  style="font-size:10pt;font-weight:bold;width:150px;"></th>
								<th  style="font-size:10pt;font-weight:bold;width:250px;">(+) VENTAS OTROS M. PAGOS</th>
								<th style="text-align:right;">S/. {{number_format(($otros_medios),'2','.',',')}}</th>
								
							<tr>
							
								<th style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;width:250px;">SALDO</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo+$datos->monto)-($totalgas+$compras),'2','.',',')}}</th>
									<th  style="font-size:10pt;font-weight:bold;width:150px;"></th>
								<th  style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;width:250px;">TOTAL VENTAS</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo+$otros_medios),'2','.',',')}}</th>
							</tr>
								

							</tbody>
							
									
								
									
									
							</table>
							<br>


							<table id=""  class="table table-bordered table-hover">
							<thead>
						
								<tr style="font-size:10pt;font-weight:bold;color:#fff;background: #808080;">
									<th width="30" style="text-align:center;vertical-align:middle;">Fec. Emision</th>
									<th width="30" style="text-align:center;vertical-align:middle;">Tipo</th>
									<th width="30" style="text-align:center;vertical-align:middle;">Serie</th>
									<th width="15" style="text-align:center;vertical-align:middle;">N°</th>
									<th width="15" style="text-align:center;vertical-align:middle;">PEDIDO</th>
									<th width="15" style="text-align:center;vertical-align:middle;">RUC / DNI / Otros</th>
									<th width="15" style="text-align:center;vertical-align:middle;" style="width:210px;">Nombre o Razón Social</th>
									<th width="15" style="text-align:center;vertical-align:middle;">Moneda</th>
									<th width="15" style="text-align:center;vertical-align:middle;">Total</th>
									<th width="15" style="text-align:center;vertical-align:middle;">NOTAS</th>
									<th width="15" style="text-align:center;vertical-align:middle;">BAJAS</th>
						
								
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->tdodes}}</td>
									<td>{{$comp->serdoc}}</td>
									<td style="background:#11B115">{{$comp->numdoc}}</td>
									<td style="background:#E7BC0D">{{$comp->ped_id}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
									<td>{{$comp->monnom}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
								
								


									
										@if($comp->ccanot=="")
										   <td ><center>---</center></td>
										@else
											<td >{{$comp->ccanot}}</td>
										@endif

										@if($comp->ccabaj=="")
										    <td ><center>---</center></td>
										@else
										 	<td ><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
										@endif
									

									
									

										
								</tr>
									
								@endforeach
							</tbody>
						</table><br>