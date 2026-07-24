

	<table >
		<thead>
			<tr>
				<th colspan="3" style="text-align:left;font-weight:bold;width:70" >{{$empresa->NomEmpresa}} - {{$empresa->IdEmpresa}}</th>
				<th colspan="3" style="text-align:left;font-weight:bold;width:70" >Fecha: {{now()->format('d-m-Y')}}</th>
			</tr>
			<tr>
				<th colspan="3" style="text-align:left;font-weight:bold;width:70" >{{$sucursal->direccion}}</th>
				<th colspan="3" style="text-align:left;font-weight:bold;width:70" >Hora: {{now()->format('H:i:s')}}</th>
			</tr>
			
			<tr>
				<th colspan="6" style="text-align:center;font-weight:bold;background-color:#D9D9D9;">ARQUEO DIARIO DE CAJA - {{Carbon::parse($fecha)->format('d-m-Y')}}</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="6" style="text-align:center;background:#D9D9D9;font-weight:bold;">RESUMEN INGRESOS</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">COBRANZAS DE DOCUMENTOS A CREDITO</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($totalcobranzas,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL FACTURAS EFECTIVO</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($ventasfactura,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL BOLETAS EFECTIVO</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($ventasboleta,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">TOTAL NOTAS EFECTIVO</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($notascontado,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;">OTROS INGRESOS</td>
				<td style="text-align:left;font-weight:bold;text-align:right;">{{number_format($totalingreso,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;background-color:#D9D9D9;">TOTAL INGRESOS</td>
				<td style="text-align:left;font-weight:bold;text-align:right;background-color:#D9D9D9;">{{number_format($notascontado+$totalcobranzas+$ventasfactura+$ventasboleta+$totalingreso,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="6"></td>
			</tr>
			<tr>
				<td colspan="6" style="text-align:center;background:#D9D9D9;font-weight:bold;">RESUMEN EGRESOS</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;background-color:#D9D9D9;">TOTAL GASTOS - PAGOS</td>
				<td style="text-align:left;font-weight:bold;text-align:right;background-color:#D9D9D9;">{{number_format($totalgast+$totalpagos,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;background-color:#D9D9D9;">TOTAL EGRESOS</td>
				<td style="text-align:left;font-weight:bold;text-align:right;background-color:#D9D9D9;">{{number_format($totalgast+$totalpagos,'2','.',',')}}</td>
			</tr>
			<tr>
				<td colspan="6"></td>
			</tr>
			<tr>
				<td colspan="5" style="text-align:left;font-weight:bold;background-color:#D9D9D9;">TOTAL SALDO ( INGRESOS-EGRESOS )</td>
				<td style="text-align:left;font-weight:bold;text-align:right;background-color:#D9D9D9;">{{number_format(($notascontado+$totalcobranzas+$ventasfactura+$ventasboleta+$totalingreso)-($totalgast+$totalpagos),'2','.',',')}}</td>
			</tr>



		</tbody>


	</table>
