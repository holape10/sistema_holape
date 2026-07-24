
						<table >
							<thead >
								<tr>
									<th colspan="6"><center><strong>CONSOLIDADO - BOLETAS DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} </strong></center></th>
									
								</tr>
							
								<tr>
									<th style="text-align:center;"  class="border-table" >FECHA</th>
									<th style="text-align:center;"  class="border-table" >COMPROBANTE</th>
									<th style="text-align:center;"  class="border-table" >SERIE</th>
									<th style="text-align:center;"  class="border-table" >INICIO</th>
									<th style="text-align:center;"  class="border-table" >FIN</th>
									<th style="text-align:center;"  class="border-table" >TOTAL</th>
									
								</tr>
							</thead>
							<tbody>
								@foreach($boletas as $bol)
								<tr>
									<td style="text-align:center;">{{Carbon::parse($bol->FECHA)->format('d-m-Y')}}</td>
									<td style="text-align:right;">{{$bol->COMPROBANTE}}</td>
									<td style="text-align:right;">{{$bol->SERIE}}</td>
									<td style="text-align:right;">{{$bol->INICIO}}</td>
									<td style="text-align:right;">{{$bol->FIN}}</td>
									<td style="text-align:right;">{{number_format($bol->TOTAL,'2','.','')}}</td>

								</tr>
								@endforeach
								<tr>
									<td colspan="5" style="text-align:center;font-weight:bold;text-align:right;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($total_boletas,'2','.','')}}</td>
								</tr>
							</tbody>
						</table>


						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead >
								<tr>
									<th colspan="5"><center><strong>FACTURAS DE VENTAS DESDE {{$fecin}} HASTA {{$fecfin}} </strong></center></th>
								</tr>
								<tr>
									<th style="text-align:center;"  class="border-table" >FECHA</th>
									<th style="text-align:center;"  class="border-table" >COMPROBANTE</th>
									<th style="text-align:center;"  class="border-table" >SERIE</th>
									<th style="text-align:center;"  class="border-table" >NUMERO</th>
									<th style="text-align:center;"  class="border-table" >TOTAL</th>
								</tr>
							</thead>
							<tbody>
								@foreach($facturas as $fac)
								<tr>
									<td style="text-align:center;">{{Carbon::parse($fac->fecha)->format('d-m-Y')}}</td>
									<td style="text-align:right;">{{$fac->comprobante}}</td>
									<td style="text-align:right;">{{$fac->serie}}</td>
									<td style="text-align:right;">{{$fac->numero}}</td>
									<td style="text-align:right;">{{number_format($fac->total,'2','.','')}}</td>

								</tr>
								@endforeach
								<tr>
									<td colspan="4" style="text-align:center;font-weight:bold;text-align:right;">TOTAL</td>
									<td style="text-align:right;font-weight:bold;">{{number_format($total_facturas,'2','.','')}}</td>
								</tr>
								
							</tbody>
						</table>