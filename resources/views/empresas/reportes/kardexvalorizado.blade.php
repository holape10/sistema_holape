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
        
            $("#btnSubmit").click(function() {

          
          var accion = $(this).attr('dir');

          $('#frmReporte').attr('action', accion);
          $('#frmReporte').submit();
        });
          


             $("#btnGenExcel").click(function() {

          
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
        		<div class="box-header box-success" style="background-color:#00a65a;">
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
	            	<div class="box-body">
						<table id="dtHorizontalExample" style="font-size:9pt;"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr style="background:blue;">
									<th  colspan="16"><font color="white"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">PERIODO: </th>
									<th colspan="13">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">RUC: </th>
									<th colspan="13">{{$dat_suc->IdEmpresa}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">NOMBRE Y/O RAZON SOCIAL: </th>
									<th colspan="13">{{$dat_emp->NomEmpresa}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">ESTABLECIMIENTO: </th>
									<th colspan="13">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">METODO DE VALUACION: </th>
									<th colspan="13"></th>
								</tr>
								<tr style="background:blue;">
									<th rowspan='2' colspan='4'><center><font color="white"><strong>DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</strong></font></center></th>
								
									<TH style="width:10%;" rowspan='2' ><font color="white">TIPO <br> OPERACION (12)</font></TH>
									<th colspan="2" style="width:10%;" colspan="3" rowspan="2"></th>
									<th style="width:10%;" colspan="3" rowspan="2"><font color="white">ENTRADAS</font></th>
									<th style="width:10%;" colspan="3" rowspan="2"><font color="white">SALIDAS</font></th>
									<th style="width:10%;" colspan="3" rowspan="2" ><font color="white">SALDOS</font></th>
									
								</tr>
							
							</thead>
							<thead>
									<tr>
									<th>FECHA</th>
									<th>TIPO</th>
									<TH>SERIE</TH>
									<TH>NUMERO</TH>
									<TH style="width:10%;"></TH>
								    <th style="width:5%;">LOTE</th>
									<th style="width:5%;">VENCIMIENTO</th>
									<th style="width:10%;">CANTIDAD</th>
									<th style="width:10%;">COSTO UNITARIO</th>
									<th style="width:10%;" >COSTO TOTAL</th>

									<th style="width:10%;">CANTIDAD</th>
									<th style="width:10%;">COSTO UNITARIO</th>
									<th style="width:10%;" >COSTO TOTAL</th>

									<th style="width:10%;">CANTIDAD</th>
									<th style="width:10%;">COSTO UNITARIO</th>
									<th style="width:10%;" >COSTO TOTAL</th>

									
								</tr>
							</thead>
								<tbody>
								

									@foreach($array_productos as $detalles)
								
								<tr >

									<td ><strong>CODIGO </strong></td>
									<td colspan="16" style="width:10%;text-align:left;" ><strong>{{$detalles['codigo']}}</strong></td>
								</tr>
									<tr>
									<td><strong>DESCRIPCION</strong></td>
									<td colspan="16" style="width:10%;"><strong>{{$detalles['producto']}}</strong></td>
								</tr>
									<tr>
									<td><strong>U.M</strong></td>
									<td colspan="16" style="width:10%;"><strong>{{$detalles['unidad']}}</strong></td>
								</tr>
						
								

								@foreach($detalles['movimientos'] as $i => $mov)

									@php
									$i=0;
									$stock=0;
									$saldo=0;
									$contar = count($mov);
									@endphp

    								@for($j=0;$j<$contar;$j++)	

										    @if($mov[$j]['mov_tip']=='I')
										    @php
										            $stock = $saldo+$mov[$j]['cantidad'];
										    @endphp
										   @endif

										    @if($mov[$j]['mov_tip']=='E')
										    @php
										        $stock = $saldo-$mov[$j]['cantidad'];
										      	@endphp
										    @endif
									        	

											<tr>
											<td style="width:60px;">{{Carbon::parse($mov[$j]['fecha'])->format('d-m-Y')}}</td>
											<td style="width:60px;">{{$mov[$j]['tdocod']}}</td>
											<td style="width:60px;">{{$mov[$j]['serie']}}</td>
											<td style="width:60px;">{{$mov[$j]['numero']}}</td>
												<td style="width:5%;text-align:center;">
													{{$mov[$j]['cod_tip_ope']}}	
												</td>
													<td>{{$mov[$j]['mov_lote']}}</td>
											<td>{{$mov[$j]['mov_vencimiento']}}</td>
											@if($mov[$j]['mov_tip']=='E')
											
												<td style="width:10%;text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
											
												<td style="width:10%;text-align:right;">{{number_format($mov[$j]['cantidad'],'3','.',',')}}</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{$mov[$j]['costo']}} @endif</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{number_format($mov[$j]['costo']*$mov[$j]['cantidad'],'2','.',',')}} @endif</td>

											@elseif($mov[$j]['mov_tip']=='I')

												<td style="width:10%;text-align:right;">{{number_format($mov[$j]['cantidad'],'3','.',',')}}</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{$mov[$j]['costo']}} @endif</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{number_format($mov[$j]['costo']*$mov[$j]['cantidad'],'2','.',',')}} @endif</td>
												
												<td style="width:10%;text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
												<td style="text-align:right;">0.000</td>
											@endif
												
										 	   	<td style="width:10%;text-align:right;">{{number_format($stock,'2','.',',')}}</td>
												<td style="text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{$mov[$j]['costo']}} @endif</td>
												<td style="width:10%;text-align:right;">@if(empty($mov[$j]['costo'])) 0.00 @else {{number_format($mov[$j]['costo']*$stock,'3','.',',')}} @endif</td>
											

										
										
										</tr>
										@php
										 	$saldo = $stock;
            							 	$i = $i+1;
										@endphp

									@endfor

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