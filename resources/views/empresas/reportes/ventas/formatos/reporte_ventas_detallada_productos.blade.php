
			<table id="dtHorizontalExample"  class="table table-responsive  table-bordered table-sm" style="width:100%;font-size:8pt;">
							<thead>
								<tr>
									<th colspan="10"  style="background-color:#337ab7;color:#fff;text-align:center;"><strong>REPORTE DE VENTAS DETALLADO DESDE: {{$fec_ini}} HASTA {{$fec_fin}}</strong></th>
								</tr>
								
							
								<tr>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">FECHA</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">SERIE</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">NUMERO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">RUC/DNI</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">RAZON SOCIAL</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">MONEDA</th>
									
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">PRODUCTO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">CANTIDAD</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">P. UNITARIO</th>
									<th style="text-align:center;font-weight:bold;background-color:#337ab7;color:#fff;width:15;">TOTAL</th>
									
	
									
								</tr>
							</thead>
							
							<tbody>

								<tr>
									<td colspan="10" style="text-align:center;font-weight:bold;background-color:#f4f4f4;"><center><strong>VENTAS</strong></center></td>
								</tr>

								@if(count($ventas_det_prod)>0)
								@foreach($ventas_det_prod as $fact)
								
								<tr>
								
								 	<td>{{Carbon::parse($fact->ccafem)->format('d-m-Y')}}</td>
									<td>{{$fact->serdoc}}</td>
									<td>{{$fact->numdoc}}</td>
									<td>{{$fact->ccandi}}</td>
									<td>{{$fact->ccanom}}</td>
									<td>{{$fact->moncod}}</td>
									<td>{{$fact->cdedes}}</td>
									<td style="text-align:right;">{{number_format($fact->cantidad*$fact->cpe_det_factor,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->cdepuni,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->cdevve,'2','.',',')}}</td>

								</tr>
								@endforeach
								@else
								<tr>
									<td><br></td>
								</tr>
								@endif
								<tr>
									<td colspan="10" style="text-align:center;font-weight:bold;background-color:#f4f4f4;"><center><STRONG>NOTAS CREDITO</STRONG></center></td>
								</tr>
								@if(count($notas_cre_det_prod)>0)
									@foreach($notas_cre_det_prod as $fact)
									
									<tr>
										<td>{{Carbon::parse($fact->ccafem)->format('d-m-Y')}}</td>
										<td>{{$fact->serdoc}}</td>
										<td>{{$fact->numdoc}}</td>
										<td>{{$fact->ccandi}}</td>
										<td>{{$fact->ccanom}}</td>
										<td>{{$fact->moncod}}</td>
										<td>{{$fact->cdedes}}</td>
										<td style="text-align:right;">{{number_format($fact->cantidad*$fact->cpe_det_factor,'2','.',',')}}</td>
										<td style="text-align:right;">{{number_format($fact->cdepuni,'2','.',',')}}</td>
										<td style="text-align:right;">{{number_format($fact->cdevve,'2','.',',')}}</td>

									</tr>
									@endforeach
								@else
								<tr>
									<td><br></td>
								</tr>
								@endif

								
								<tr>
									<td colspan="10" style="text-align:center;font-weight:bold;background-color:#f4f4f4;"><center><STRONG>ANULACIONES</STRONG></center></td>
								</tr>
								@if(count($anulaciones_det_prod)>0)
								@foreach($anulaciones_det_prod as $fact)
								
								<tr>
									<td>{{Carbon::parse($fact->ccafem)->format('d-m-Y')}}</td>
									<td>{{$fact->serdoc}}</td>
									<td>{{$fact->numdoc}}</td>
									<td>{{$fact->ccandi}}</td>
									<td>{{$fact->ccanom}}</td>
									<td>{{$fact->moncod}}</td>
									<td>{{$fact->cdedes}}</td>
									<td style="text-align:right;">{{number_format($fact->cantidad*$fact->cpe_det_factor,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->cdepuni,'2','.',',')}}</td>
									<td style="text-align:right;">{{number_format($fact->cdevve,'2','.',',')}}</td>

								</tr>
								@endforeach
								@else
								<tr>
									<td><br></td>
								</tr>
								@endif
							
							</tbody>
							<tfoot>
        <tr style="background-color: #337ab7; color: #fff;">
            <td colspan="9" style="text-align:right;"><strong>TOTAL GENERAL DEL REPORTE (S/.)</strong></td>
            <td style="text-align:right;"><strong>{{ number_format($total_final, 2, '.', ',') }}</strong></td>
        </tr>
    </tfoot>
						</table><br>

