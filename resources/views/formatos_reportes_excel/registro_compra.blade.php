
	<table >
							
							<thead>
								<tr >
									<th colspan="7" style="text-align:center;"><strong>LISTA DE INGRESO DE MERCADERIA</strong></th>
								</tr>
								<tr>
									<th style="text-align:left;">Proveedor:</th>
									<th >{{$cabpdf->prov_raz}}</th>
									<th style="text-align:left;">RUC:</th>
									<th >{{$cabpdf->prov_ruc}}</th>
								</tr>
								
								<tr>
									<th style="text-align:left;">Dirección:</th>
									<th >{{$cabpdf->prov_dir}}</th>
									<th style="text-align:left;" >Comprobante:</th>
									<th >{{$cabpdf->com_doc_ser}}-{{$cabpdf->com_doc_num}}</th>
								</tr>
								<tr>
									<th style="text-align:left;">Ag Transporte:</th>
									<th ></th>
									<th style="text-align:left;">N° Guía:</th>
									<th ></th>
								</tr>
								<tr>
									<th style="text-align:left;">N° Bultos:</th>
									<th ></th>
								
									<th style="text-align:left;">P. Neto:</th>
									<th ></th>
								</tr>
								<tr>
									<th style="text-align:left;">F. Emisión:</th>
									<th ></th>
									<th style="text-align:left;">F. Ingreso:</th>
									<th ></th>
									
								</tr>
								
							</thead>
	</table>
	<br>
	<table >
		<thead>
			<tr >
									<th style="border-color:black;"><center><font color="white" size='1'>ITEM</font></center></th>
									<th style="border-color:black;"><center><font color="white" size='1'>CODIGO.</font></center></th>
									<th style="border-color:black;"><center><font color="white" size='1'>CANTIDAD</font></center></th>
									<th style="border-color:black;"><center><font color="white" size='1'>U.M</font></center></th>
									<th style="border-color:black;"><center><font color="white" size='1'>DESCRIPCIÓN</font></center></th>
									<th style="border-color:black;" ><center><font color="white" size='1'>P.U</font></center></th>
									<th style="border-color:black;" ><center><font color="white" size='1'>P.VENTA</font></center></th>
								</tr>
		</thead>
							<tbody>
								<?php $i=0; ?>
									@foreach($detpdf as $det)
								<?php $i=$i+1; ?>
									<tr>
										<td style="border-color:black;text-align: right" ><center><font size='1'>{{$i}}</font></center></td>
										<td style="border-color:black;text-align: right"><center><font size='1'>{{$det->procod}}</font></center></td>
										<td style="border-color:black;text-align: right" ><center><font size='1'>{{$det->cantidad}}</font></center></td>
										<td style="border-color:black;text-align: right" ><center><font size='1'>{{$det->umecod}}</font></center></td>
										<td style="border-color:black;text-align: right"><font size='1'> {{$det->pronom}}</font></td>
										<td style="border-color:black;text-align: right" ><center><font size='1'>{{number_format($det->pre_uni,'2','.',',')}}</font></center></td>
										<td style="border-color:black;text-align: right" ><center><font size='1'>{{number_format($det->total,'2','.',',')}}</font></center></td>
									</tr>
											
									@endforeach
								

								<tr >	
										<td colspan="6"  style="font-size:10;border-top:black 1px solid;"><strong>SUBTOTAL</strong></td>
								
										<td  style="font-size:10;text-align: right;border-top:black 1px solid;">@if($cabpdf->moncod=='USD') $ @else S/ @endif {{number_format($cabpdf->subtot_com,'2','.',',')}}</td>
									</tr>
									<tr>	
										<td  colspan="6"  style="font-size:7"><strong>OP. GRAVADA</strong></td>
			
										<td class="width-table" style="font-size:10;text-align: right;">@if($cabpdf->moncod=='USD') $ @else S/ @endif @if($cabpdf->gravado=='1') {{number_format($cabpdf->subtot_com,'2','.',',')}} @else 0.00 @endif</td>
									</tr>
									<tr>	
										<td colspan="6"  style="font-size:7"><strong>OP. EXONERADA</strong></td>
									
										<td class="width-table" style="font-size:10;text-align: right;">@if($cabpdf->moncod=='USD') $ @else S/ @endif @if($cabpdf->gravado=='0') {{number_format($cabpdf->subtot_com,'2','.',',')}} @else 0.00 @endif</td>
									</tr>
									<tr>	
										<td colspan="6"  style="font-size:7"><strong>IGV</strong></td>
									
										<td  style="font-size:10;text-align: right;">@if($cabpdf->moncod=='USD') $ @else S/ @endif{{number_format($cabpdf->com_cab_igv,'2','.',',')}}</td>
									</tr>
							
									
								
									<tr style="border-bottom:black 1px solid;" >
										<td colspan="6"  style="font-size:7"><strong>IMPORTE TOTAL</strong></td>
						
										<td  style="font-size:10;text-align: right" ><strong>@if($cabpdf->moncod=='USD') $ @else S/ @endif {{number_format($cabpdf->total_com,'2','.',',')}}</strong></td>
									</tr>
									


							</tbody>
						</table>



					
