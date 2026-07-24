
		<table >
				<thead>
				<tr>
					<th style="text-align:center;font-weight:bold;width:70" colspan="3" >{{$empresa->NomEmpresa}} - {{$empresa->IdEmpresa}}</th>
					<th style="text-align:center;font-weight:bold;width:70" colspan="4">Fecha: {{now()->format('d-m-Y')}}</th>
				</tr>
				<tr >
					<th style="text-align:center;font-weight:bold;" colspan="3">{{$sucursal->direccion}}</th>
					<th style="text-align:center;font-weight:bold;" colspan="4" >Hora: {{now()->format('H:i:s')}}</th>
				</tr>
				
				<tr style="background-color:#D9D9D9;">
					<th  colspan="7" style="text-align:center;font-weight:bold;" >ARQUEO DIARIO DE CAJA - {{Carbon::parse($fecha)->format('d-m-Y')}}</th>
				</tr>
		

				<tr style="background-color:#D9D9D9;">
					<th style="text-align:center;font-weight:bold;width:30">FECHA</th>
					<th style="text-align:center;font-weight:bold;width:30">COD. DOC.</th>
					<th style="text-align:center;font-weight:bold;width:30">SERIE</th>
					<th style="text-align:center;font-weight:bold;width:30">NUMERO</th>
					<th style="text-align:center;font-weight:bold;width:30">DESCRIPCION</th>
					<th style="text-align:center;font-weight:bold;width:30">EFECT.</th>
					<th style="text-align:center;font-weight:bold;width:30">DEPOS.</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="7" style="font-weight:bold;"  >01. (+) VENTAS DEL DIA</td>
				</tr>
				@foreach($registroventascontado as $comp)
				<tr>
					<td >{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
					<td >{{$comp->tdocod}}</td>
					<td style="text-align:left;">{{$comp->serdoc}}</td>
					<td >{{$comp->numdoc}}</td>
					<td >{{$comp->ccanom}}</td>
					<td >{{$comp->ccaitv}}</td>
					<td ></td>
				</tr>
				@endforeach

				<tr>
					<td colspan="5" style="font-weight:bold;" >TOTAL VENTAS DEL DÍA</td>
					<td style="font-weight:bold;">{{number_format($ventascontado,'2','.',',')}}</td>
					<td ></td>
				</tr>
				<tr>
				<td colspan="7"></td>
			</tr>
				<tr>
					<td colspan="7" style="font-weight:bold;">02. VENTAS AL CRÉDITO</td>

				</tr>
				@foreach($registroventascredito as $comp)
				<tr>
					<td >{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
					<td >{{$comp->tdocod}}</td>
					<td style="text-align:left;">{{$comp->serdoc}}</td>
					<td >{{$comp->numdoc}}</td>
					<td >{{$comp->ccanom}}</td>
					<td >{{$comp->ccaitv}}</td>
					<td ></td>
				</tr>
				@endforeach

				<tr>
					<td colspan="5" style="font-weight:bold;">TOTAL VENTAS AL CREDITO</td>
					<td style="font-weight:bold;">{{number_format($ventascredito,'2','.',',')}}</td>
					<td ></td>
				</tr>
				<tr>
				<td colspan="7"></td>
			</tr>
				<tr>
					<td colspan="7" style="font-weight:bold;">03. (+) COBRANZAS</td>

				</tr>
				@foreach($cobranzas as $comp)
				<tr>
					<td >{{Carbon::parse($comp->fec_reg)->format('d-m-Y')}}</td>
					<td >{{$comp->tdocod}}</td>
					<td style="text-align:left;">{{$comp->serdoc}}</td>
					<td >{{$comp->numdoc}}</td>
					<td >{{$comp->ccanom}}</td>
					<td >{{$comp->abono}}</td>
					<td >{{$comp->monto_deposito}}</td>
				</tr>
				@endforeach
			
				<tr>
					<td colspan="5" style="font-weight:bold;">TOTAL COBRANZAS</td>
					<td style="font-weight:bold;">{{number_format($totalcobranzas,'2','.',',')}}</td>
					<td style="font-weight:bold;">{{number_format($totalcobranzasdepositos,'2','.',',')}}</td>
				</tr>
				<tr>
				<td colspan="7"></td>
			</tr>
				<tr>
					<td colspan="7" style="font-weight:bold;">04. (-) PAGOS</td>

				</tr>
				@foreach($pagos as $comp)
				<tr>
					<td >{{Carbon::parse($comp->fec_reg)->format('d-m-Y')}}</td>
					<td >{{$comp->tdocod}}</td>
					<td style="text-align:left;">{{$comp->com_doc_ser}}</td>
					<td >{{$comp->com_doc_num}}</td>
					<td >{{$comp->proveedor}}</td>
					<td >{{$comp->abono}}</td>
					<td ></td>
				</tr>
				@endforeach

				<tr>
					<td colspan="5" style="font-weight:bold;">TOTAL PAGOS</td>
					<td style="font-weight:bold;">{{number_format($totalpagos,'2','.',',')}}</td>
					<td ></td>
				</tr>
				<tr>
				<td colspan="7"></td>
			</tr>

				<tr>
					<td colspan="7" style="font-weight:bold;">10. (-) GASTOS VARIOS DE GESTIÓN</td>

				</tr>
				@foreach($gastos as $gast)
				<tr>
					<td >{{Carbon::parse($gast->gast_fec)->format('d-m-Y')}}</td>
					<td >{{$gast->tdocod}}</td>
					<td style="text-align:left;">{{$gast->gast_doc_ser}}</td>
					<td >{{$gast->gast_doc_num}}</td>
					<td >{{$gast->det_gasto}}</td>
					<td >{{$gast->total}}</td>
					<td ></td>
				</tr>
				@endforeach

				<tr>
					<td colspan="5" style="font-weight:bold;">TOTAL GASTOS</td>
					<td style="font-weight:bold;">{{number_format($totalgast,'2','.',',')}}</Td>
					<td ></td>
				</tr>
				<tr>
				<td colspan="7"></td>
			</tr>
				<tr>
					<td colspan="7" style="font-weight:bold;">15. (+) OTROS INGRESOS</td>
				</tr>

				@foreach($ingresos as $ing)
					<tr>
						<td >{{$ing->gast_fec}}</td>
						<td >{{$ing->tdocod}}</td>
						<td style="text-align:left;">{{$ing->gast_doc_ser}}</td>
						<td >{{$ing->gast_doc_num}}</td>
						<td >{{$ing->det_gasto}}</td>
						<td >{{$ing->total}}</td>
						<td ></td>
					</tr>
					@endforeach

					<tr>
						<td colspan="5"style="font-weight:bold;" >TOTAL OTROS INGRESOS</td>
						<td style="font-weight:bold;">{{number_format($totalingreso,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr>
				<td colspan="7"></td>
			</tr>
					<tr>
						<td colspan="7" style="font-weight:bold;text-align:center;background-color:#D9D9D9;" >RESUMEN INGRESOS</td>
					</tr>

					<tr>
						<td colspan="5" style="font-weight:bold;">COBRANZAS DE DOCUMENTOS A CREDITO</td>
						<td style="font-weight:bold;">{{number_format($totalcobranzas,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr >
						<td colspan="5" style="font-weight:bold;">TOTAL FACTURAS EFECTIVO</td>
						<td style="font-weight:bold;">{{number_format($ventasfactura,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr>
						<td colspan="5" style="font-weight:bold;">TOTAL BOLETAS EFECTIVO</td>
						<td style="font-weight:bold;">{{number_format($ventasboleta,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr>
						<td colspan="5" style="font-weight:bold;" >TOTAL NOTAS EFECTIVO</td>
						<td >{{number_format($notascontado,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr>
						<td colspan="5" style="font-weight:bold;">OTROS INGRESOS</td>
						<td style="font-weight:bold;">{{number_format($totalingreso,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr >
						<td colspan="5" style="font-weight:bold;">TOTAL INGRESOS</td>
						<td style="font-weight:bold;">{{number_format($notascontado+$totalcobranzas+$ventasfactura+$ventasboleta+$totalingreso,'2','.',',')}}</td>
						<td ></td>
					</tr>
					<tr>
				<td colspan="7"></td>
			</tr>
					<tr>
						<td colspan="7" style="font-weight:bold;text-align:center;background-color:#D9D9D9;">RESUMEN EGRESOS</td>
					</tr>


					<tr>
						<td colspan="5" style="font-weight:bold;">TOTAL GASTOS - PAGOS</td>
						<td >{{number_format($totalgast+$totalpagos,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr >
						<td colspan="5" style="font-weight:bold;">TOTAL EGRESOS</td>
						<td style="font-weight:bold;">{{number_format($totalgast+$totalpagos,'2','.',',')}}</td>
						<td ></td>
					</tr>

					<tr>
				<td colspan="7"></td>
			</tr>
					<tr >
						<td colspan="5" style="font-weight:bold;">TOTAL SALDO ( INGRESOS-EGRESOS )</td>
						<td style="font-weight:bold;">{{number_format(($notascontado+$totalcobranzas+$ventasfactura+$ventasboleta+$totalingreso)-($totalgast+$totalpagos),'2','.',',')}}</td>
						<td ></td>
					</tr>


			</tbody>
				
			

			</table>

