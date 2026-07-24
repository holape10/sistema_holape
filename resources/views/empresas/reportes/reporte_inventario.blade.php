
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm" style="width:100%;font-size:8pt;">
						
							<thead>
								<tr>
									<th colspan="11" style="text-align:center;background-color:#337ab7;color:#fff;"><center><strong>INVENTARIO VALORIZADO - PERIODO {{Carbon::parse($periodo)->format('m-Y')}}</strong></center></th>
									
								</tr>
							
							
								<tr style="background:background:#A0A0A0;">
									<th style="width:25px;text-align:center;">CODIGO</th>
									<th style="width:450px;text-align:center;">PRODUCTO</th>
									<th style="width:20px;text-align:center;">UNIDAD <br>MEDIDA</th>
									<th style="width:40px;text-align:center;">SALDO</th>
									<th style="width:70px;text-align:center;">PRECIO<BR>PROMEDIO</th>
									<th style="width:70px;text-align:center;">FLETE</th>
									<th style="width:70px;text-align:center;">PRECIO<BR>COSTO</th>
									<th style="width:70px;text-align:center;">TOTAL</th>
									
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
						


