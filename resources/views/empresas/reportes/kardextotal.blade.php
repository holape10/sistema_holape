@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">
     $(document).ready(function()
    {
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
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								<tr style="background:#337ab7;">
									<th colspan="16"><font color="white"><strong><center>REGISTRO DE INVENTARIO PERMANENTE VALORIZADO</center></strong></font></th>
									
								</tr>
								<tr>
									<th colspan="3">PERIODO: </th>
									<th colspan="13">De {{$fecin}} HASTA {{$fecfin}}</th>
								</tr>
								<tr>
									<th colspan="3">RUC: </th>
									<th colspan="13">{{$dat_suc->IdEmpresa}}</th>
								</tr>
								<tr>
									<th colspan="3">NOMBRE Y/O RAZON SOCIAL: </th>
									<th colspan="13">{{$dat_emp->NomEmpresa}}</th>
								</tr>
								<tr>
									<th colspan="3">ESTABLECIMIENTO: </th>
									<th colspan="13">{{$dat_alm->descripcion}}</th>
								</tr>
								<tr>
									<th colspan="3">TIPO: </th>
									<th colspan="13"></th>
								</tr>
								<tr>
									<th colspan="3">METODO DE VALUACION: </th>
									<th colspan="13"></th>
								</tr>
								<tr style="background:#337ab7;">
									<th colspan="4"><center><font color="white"><strong>DOCUMENTO DE TRASLADO, COMPROBANTE DE PAGO, DOCUMENTO INTERNO O SIMILAR</center></strong></font></th>
									<th hidden="hidden"><font color="white"><strong><center>STOCK INICIAL</center></strong></font></th>
									<th colspan="3"><font color="white"><strong><center>ENTRADAS</center></strong></font></th>
									<th colspan="3"><font color="white"><strong><center>SALIDAS</center></strong></font></th>
									<th colspan="3"><font color="white"><strong><center>SALDO FINAL</center></strong></font></th>
									<th ><font color="white"><strong><center>ORIGEN</center></strong></font></th>
									<th ><font color="white"><strong><center>DESTINO</center></strong></font></th>
								
								</tr>
								<tr>
									
									<th>FECHA</th>
									<TH>SERIE</TH>
									<TH>TIPO</TH>
									<TH>NUMERO</TH>
									<th hidden="hidden">STOCK INICIAL</th>
									<th>CANTIDAD</th>
									<th>COSTO UNITARIO</th>
									<th>COSTO TOTAL</th>
									<th>CANTIDAD</th>
									<th>COSTO UNITARIO</th>
									<th>COSTO TOTAL</th>
									<th>CANTIDAD</th>
									<th>COSTO UNITARIO</th>
									<th>COSTO TOTAL</th>
									<th>ALMACEN ORIGEN</th>
									<th>ALMACEN DESTINO</th>
								</tr>

								
							</thead>
							
							<tbody>

					
								@foreach($productos as $pro)

								@php

								  $bus_ven = DB::table('movimientos_productos')
								  ->where('IdProducto_rel',$pro->IdProducto)
          						  ->where('id_almacen','=',$almacen)
								  ->where('fecha_mov','>=',$fecin)
								  ->where('fecha_mov','<=',$fecfin)
								  ->count();


				

								@endphp
								@if($bus_ven > '0')


							   @php
									

	            $calcular_saldo= DB::table("movimientos_productos")
	          ->select(
	                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
	                                WHERE mov_tip='I' AND fecha_mov<'".$fecin."' AND IdProducto_rel='".$prod."'  ) as Ingresos"),
	                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
	                                WHERE mov_tip='E' AND fecha_mov<'".$fecin."' AND IdProducto_rel='".$prod."' ) as Egresos"),
	                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
	                                WHERE mov_tip='EI' AND fecha_mov<'".$fecin."' AND IdProducto_rel='".$prod."' ) as Anula"))
	          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
	          ->where('IdProducto_rel',$pro->IdProducto)
	           ->where('id_almacen','=',$almacen)
	          ->groupby('IdProducto_rel')
	          ->first();

      if(!empty($calcular_saldo)){
          $saldo_anterior = ($calcular_saldo->Ingresos-($calcular_saldo->Egresos+$calcular_saldo->Anula)); 
      }

									@endphp

								<tr>

									<td><strong>CODIGO EXISTENCIA</strong></td>
									<td colspan="15"><strong>{{$pro->procod}}</strong></td>
								</tr>
									<tr>
									<td><strong>DESCRIPCION</strong></td>
									<td colspan="15"><strong>{{$pro->pronom}}</strong></td>
								</tr>
									<tr>
									<td><strong>CODIGO DE UNIDAD DE MEDIDA</strong></td>
									<td colspan="15"><strong>{{$pro->umecod}}</strong></td>
								</tr>
								
								@if($saldo_anterior!=0)
									<tr>

									    <td></td>
									    <td></td>
										<td></td>
										<td></td>
										
										<td hidden="hidden">0.000</td>
										
									
									

										<td>0.000</td>
										<td></td>
										<td></td>
										
										<td>0.000</td>
										<td></td>
										<td></td>
								 	   	<td>{{number_format($saldo_anterior,'3','.',',')}}</td>
						
								</tr>
								@endif
								@php

								$i=0;

								@endphp

								@foreach($movimientos as $c)
								@if(($c->IdProducto_rel == $pro->IdProducto) || ($c->IdProducto == $pro->IdProducto))
								
								@php

										if($i==0){
											if($c->descripcion=='STOCK_INICIAL'){
											 	$saldo = $c->cantidad;
							                	$stock = $saldo;
											}else{
												$saldo = $saldo_anterior;

									            if($c->mov_tip=='I'){
									                $stock = $saldo+$c->cantidad;
									            }

									            if($c->mov_tip=='E'){
									                $stock = $saldo - $c->cantidad;
									            }
							        		}

							            }else{

							            
							            if($c->mov_tip=='I'){

							                $stock = $saldo+$c->cantidad;


							            }

							            if($c->mov_tip=='E'){

							                $stock = $saldo - $c->cantidad;


							            }
							        }


								@endphp


								<tr>

									   <td>{{$c->fecha_mov}}</td>
									   <td>{{$c->tdocod}}</td>
										<td>{{$c->serie}}</td>
										<td>{{$c->numero}}</td>
										<td hidden="hidden">{{number_format($c->stock_inicial,'3','.',',')}}</td>
										

									@if($c->mov_tip=='E')
										<td></td>
										<td></td>
										<td></td>
										<td>{{number_format($c->cantidad,'3','.',',')}}</td>
										<td>{{number_format($c->costo,'3','.',',')}}</td>
										<td>{{number_format($c->cantidad*$c->costo,'3','.',',')}}</td>
									@elseif($c->mov_tip=='I')

										<td>{{number_format($c->cantidad,'3','.',',')}}</td>
										<td>{{number_format($c->costo,'3','.',',')}}</td>
										<td>{{number_format($c->cantidad*$c->costo,'3','.',',')}}</td>
										<td></td>
										<td></td>
										<td></td>
									@endif
									
								 	   	<td>{{number_format($stock,'3','.',',')}}</td>
										<td>{{number_format($c->costo,'3','.',',')}}</td>
										<td>{{number_format($stock*$c->costo,'3','.',',')}}</td>

								
										<td></td>
										<td></td>
									
									
									@php
										 $saldo = $stock;
            							 $i = $i+1;
									@endphp
								</tr>
									@endif
								@endforeach
							
								@endif
								@endforeach
								
							</tbody>
						</table><br>
					</div>	
				
				</div>
		</div>
	</div>
</section>



@endsection