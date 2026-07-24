
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
						
							<thead>
								<tr style="background:#A0A0A0;">
									<th colspan="9" style="text-align:center;color:#fffff;"><center><strong>INVENTARIO VALORIZADO A LA FECHA DESDE {{$fec_ini}} HASTA {{$fec_fin}}</strong></center></th>
									
								</tr>
							
							
								<tr style="background:background:#A0A0A0;">
									<th style="text-align:center;color:#fffff;">CODIGO</th>
									<th style="text-align:center;color:#fffff;">PRODUCTO</th>
									<th style="text-align:center;color:#fffff;">UNIDAD <br>MEDIDA</th>
									<th style="text-align:center;color:#fffff;">SALDO</th>
									<th style="text-align:center;color:#fffff;">PRECIO<BR>PROMEDIO</th>
									<th style="text-align:center;color:#fffff;">FLETE</th>
									<th style="text-align:center;color:#fffff;">PRECIO<BR>COSTO</th>
									<th style="text-align:center;color:#fffff;">TOTAL</th>
									
								
									
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
						


