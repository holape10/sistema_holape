
						<table id="dtHorizontalExample"   style="font-size:9pt;border-collapse:collapse;">
							
							<thead>
								<tr>
									<th colspan="3" style="text-align:left;">{{$empresa->NomEmpresa}}</th>
									<th colspan="3"></th>
									<th colspan="2" style="text-align:left;">Fecha: {{now()->format('d-m-Y')}}</th>
								</tr>
								<tr>
									<th colspan="3" style="text-align:left;">{{$empresa->DirEmpresa}}</th>
									<th colspan="3"></th>
									<th colspan="2" style="text-align:left;">Hora: {{now()->format('h:i:s')}}</th>
								</tr>
								<tr>
									<th colspan="3" style="text-align:left;">RUC: {{$empresa->IdEmpresa}}</th>
									<th colspan="3"></th>
									<th colspan="2" style="text-align:left;"></th>
								</tr>
								<tr>
									<th colspan="8"><br></th>
								</tr>
								<tr>
									<th colspan="8" style="text-align:center;">Inventario Valorado de Productos<br>Del {{$fec_ini}} Al {{$fec_fin}}</th>
									
								</tr>
							</thead>
							<thead  >
								
							
							
								<tr style="border-bottom: solid 2px black;border-top: solid 2px black;">
									<th style="text-align:center;color:#fffff;width:50;">CODIGO</th>
									<th style="text-align:center;color:#fffff;width:50;">PRODUCTO</th>
									<th style="text-align:center;color:#fffff;width:50;">UNIDAD <br>MEDIDA</th>
									<th style="text-align:center;color:#fffff;width:80;">SALDO</th>
									<th style="text-align:center;color:#fffff;width:80;">PRECIO<BR>PROMEDIO</th>
									<th style="text-align:center;color:#fffff;width:80;">FLETE</th>
									<th style="text-align:center;color:#fffff;width:80;">PRECIO<BR>COSTO</th>
									<th style="text-align:center;color:#fffff;width:80;">TOTAL</th>
									
								</tr>	
							</thead>
						
							<tbody>
								
								@if(!empty($inventario))
									@foreach($inventario as $inv)
									
     
      
										<tr>
											<td>{{$inv->procod}}</td>
											<td>{{$inv->pronom}}</td>
											<td style="text-align:center;">{{$inv->umecod}}</td>
											<td style="text-align:right;">{{number_format($inv->Ingresos-$inv->Egresos,2,'.','')}}</td>
											<td style="text-align:right;">{{$inv->costo}}</td>
											<td style="text-align:right;">{{$inv->flete}}</td>
											<td style="text-align:right;">{{$inv->costo}}</td>
											<td style="text-align:right;">{{number_format(($inv->costo+$inv->flete)*($inv->Ingresos-$inv->Egresos),2,'.','')}}</td>
											
											
											
										</tr>
									@endforeach
										 
								@endif
							</tbody>
						</table><br>
						


