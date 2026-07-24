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
             $('#frmReporte').attr('target', '_blank');
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
	            	<div class="box-body">
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr style="background:#337ab7;">
									<th  colspan="10"><font color="white"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">PERIODO: </th>
									<th colspan="8">De {{Carbon::parse($fecin)->format('d-m-Y')}}  HASTA {{Carbon::parse($fecfin)->format('d-m-Y')}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">RUC: </th>
									<th colspan="8">{{$dat_suc->IdEmpresa}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">NOMBRE Y/O RAZON SOCIAL: </th>
									<th colspan="8">{{$dat_emp->NomEmpresa}}</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">ESTABLECIMIENTO: </th>
									<th colspan="8">@if(!empty($dat_alm)) {{$dat_alm->descripcion}} @else Todos @endif</th>
								</tr>
								<tr>
									<th style="width:10%;" colspan="3">METODO DE VALUACION: </th>
									<th colspan="8"></th>
								</tr>
								<tr style="background:#337ab7;">
									<th colspan="10"><center><font color="white"><strong>DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</strong></font></center></th>	
								</tr>
								<tr>
									<th>FECHA</th>
									<TH>SERIE</TH>
									<TH>NUMERO</TH>
									<Th style="width:10%;">DESCRIPCION</th>
									<th style="width:10%;">ENTRADAS</th>
									<th style="width:10%;">SALIDAS</th>
									<th style="width:10%;" >SALDOS</th>
									
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
											<td ><strong>CODIGO </strong></td>
											<td colspan="10" style="width:10%;text-align:left;" ><strong>{{$pro->procod}}</strong></td>
										</tr>
											<tr>
											<td><strong>DESCRIPCION</strong></td>
											<td colspan="10" style="width:10%;"><strong>{{$pro->pronom}}</strong></td>
										</tr>
											<tr>
											<td><strong>U.M</strong></td>
											<td colspan="10" style="width:10%;"><strong>{{$pro->umecod}}</strong></td>
										</tr>
						
									
										@foreach($movimientos->chunk(10) as $chunk1)
											@foreach ($chunk1 as $mov)
												<tr>
													<td>{{Carbon::parse($mov->fecha_mov)->format('d-m-Y')}}</td>
													<td>{{$mov->serie}}</td>
													<td>{{$mov->numero}}</td>
													<td style="width:5%;">@if(!empty($mov->cliente)){{$mov->cliente}}@else{{$mov->descripcion}} @endif</td>

													@if($mov->mov_tip =='E')
													<td style="width:10%;text-align:right;">0.000</td>
													<td style="width:10%;text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
													@elseif($mov->mov_tip =='I')
													<td style="width:10%;text-align:right;">{{number_format($mov->cantidad,'3','.',',')}}</td>
													<td style="width:10%;text-align:right;">0.000</td>	
													@endif
														
													@if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR')
													<td style="width:10%;text-align:right;">{{number_format($mov->cantidad,'2','.',',')}}</td>	
													@else
													<td style="width:10%;text-align:right;">{{number_format($mov->stock,'2','.',',')}}</td>
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