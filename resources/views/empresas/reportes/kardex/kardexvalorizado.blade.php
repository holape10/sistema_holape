@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
       $(document).ready(function()
    {

      
        $("#btnGenPdf").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
            $('#frmReporte').attr('target', '_blank');
          $('#frmReporte').submit();
        });

             $("#btnGenExcel").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
        

        $("#btnSubmit").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
          


     $("#sucursal").change(function() {
         
                var sucursal = $("#sucursal").val();
               
                $("#divalmacen").html('<center><img src="/img/load.gif" width="100px" height="100px"></center>');
                $.ajax({
                  type: "GET",
                  dataType: 'json',
                  url: "/buscaralmacen/"+sucursal,

                }).done(function(respuesta){
                $("#divalmacen").html(respuesta.vista);
               
                });

      });
});
     </script>

<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#337ab7;">
        			<font color="white" size="4"><center><strong>KARDEX</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.reportes.buscarkardex')
	           	</div>
	         </div>
	    </div>
	</div> 
	
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
	            	<div class="box-body table-responsive">
						<table id="dtHorizontalExample"  class="table table-responsive table-bordered table-sm" style="font-size:8pt;width:100%;">
							<thead>
								<tr style="background:#337ab7;">
									<th  colspan="26"><font color="white"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
								</tr>
								
							</thead>
							<tbody>
						
							
	

							@foreach($productos->chunk(10) as $chunk)
								@foreach ($chunk as $pro)
									@php
								            $movimientos = DB::TABLE('movimientos_productos')
											->where(function ($query) use ($pro){
												$query->where('IdProducto','=',$pro->IdProducto)
											     	  ->orwhere('IdProducto_rel','=',$pro->IdProducto);          
												}) 
										
											->where('id_almacen',$almacen)
											->where('fecha_mov','>=',$fecin)
											->where('fecha_mov','<=',$fecfin)
											->orderby('fecha_mov','asc')
											->orderby('mov_tip','desc')
											->orderby('tipo','asc')
											->get();
							   	 		@endphp

							   	 		@if(count($movimientos)>0)	
								<tr>
								<td  colspan="3">PERIODO: </td>
								<td colspan="14">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</td>
								</tr>
								<tr>
									<td  colspan="3">RUC: </td>
									<td colspan="14">{{$dat_suc->IdEmpresa}}</td>
								</tr>
								<tr>
									<td  colspan="3">NOMBRE Y/O RAZON SOCIAL: </td>
									<td colspan="14">{{$dat_emp->NomEmpresa}}</td>
								</tr>
								<tr>
									<td  colspan="3">ESTABLECIMIENTO: </td>
									<td colspan="14">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</td>
								</tr>
								<tr>
									<td colspan="3">METODO DE VALUACION: </td>
									<td colspan="14"></td>
								</tr>
								<tr>
									<td ><strong>CODIGO </strong></td>
									<td colspan="14" style="text-align:left;" ><strong>{{$pro->procod}}</strong></td>
								</tr>
								<tr>
									<td><strong>DESCRIPCION</strong></td>
									<td colspan="14" ><strong>{{$pro->pronom}}</strong></td>
								</tr>
								<tr>
									<td><strong>U.M</strong></td>
									<td colspan="14" ><strong>{{$pro->umecod}}</strong></td>
								</tr>
								<tr style="background:#337ab7;">
									<td colspan="17"><center><font color="white"><strong>DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</strong></font></center></td>	
								</tr>
										
								<tr style="color:#fff;font-weight:bold;">
									<td rowspan="2" style="background-color:#9B9B9B;text-align:center;">FECHA DE EMISIÓN</td>
											<Td rowspan="2" style="background-color:#9B9B9B;text-align:center;">TIPO</Td>
									<Td rowspan="2" style="background-color:#9B9B9B;text-align:center;">SERIE</Td>
									<Td rowspan="2" style="background-color:#9B9B9B;text-align:center;">NUMERO</Td>
									<Td rowspan="2" style="background-color:#9B9B9B;text-align:center;">CLIENTE</Td>
									<td rowspan="2" style="background-color:#9B9B9B;text-align:center;">TIPO DE OPERACIÓN</td>
									<td style="background-color:#9B9B9B;text-align:center;;" colspan="3">ENTRADAS</td>
									<td style="background-color:#9B9B9B;text-align:center;;" colspan="3">SALIDAS</td>
									<td style="background-color:#9B9B9B;text-align:center;;" colspan="3">SALDOS</td>
									
								</tr>
								<tr style="color:#fff;font-weight:bold;">
									
									<th style="background-color:#9B9B9B;">CANTIDAD</th>
									<th style="background-color:#9B9B9B;">COSTO UNITARIO</th>
									<th style="background-color:#9B9B9B;">COSTO TOTAL</th>
									<th style="background-color:#9B9B9B;">CANTIDAD</th>
									<th style="background-color:#9B9B9B;">COSTO UNITARIO</th>
									<th style="background-color:#9B9B9B;">COSTO TOTAL</th>
									<th style="background-color:#9B9B9B;">CANTIDAD</th>
									<th style="background-color:#9B9B9B;">COSTO UNITARIO</th>
									<th style="background-color:#9B9B9B;">COSTO TOTAL</th>
								</tr>

										
										@foreach($movimientos->chunk(10) as $chunk1)
											@foreach ($chunk1 as $mov)
												<tr>


													<td style="text-align:center;">{{Carbon::parse($mov->fecha_mov)->format('d-m-Y')}}</td>
													<td>{{$mov->tdocod}}</td>
													<td>{{$mov->serie}}</td>
													<td>{{$mov->numero}}</td>
													<td style="width:5%;">@if(!empty($mov->cliente)){{$mov->cliente}}@else{{$mov->descripcion}} @endif</td>
													<td style="text-align:center;">{{$mov->cod_tip_ope}}</td>
													@if($mov->mov_tip =='E')
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
													@elseif($mov->mov_tip =='I')
													<td style="text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													<td style="text-align:right;">0.000</td>
													@endif
														
													@if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR')
													<td style="text-align:right;">{{number_format($mov->cantidad,'2','.',',')}}</td>	
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
										
													@else
													<td style="text-align:right;">{{number_format($mov->stock,'2','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->costo,'3','.',',')}}</td>
													<td style="text-align:right;">{{number_format($mov->cantidad*$mov->costo,'3','.',',')}}</td>
													
													@endif
												</tr>
											@endforeach
										@endforeach
										@endif
								@endforeach
							@endforeach
							</tbody>
						</table><br>
					</div>	
				</div>
		</div>
	</div>
</section>
@endsection