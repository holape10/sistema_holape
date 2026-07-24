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
	            	<div class="box-body">
						<table id="dtHorizontalExample"  class="table table-responsive table-striped table-bordered table-sm">
							<thead>
								
								<tr style="background:blue;">
									<th colspan="10"><center><font color="white"><strong>CODIGO: {{$data_prod->procod}} | PRODUCTO: {{$data_prod->pronom}}  | U.M. : {{$data_prod->umecod}}</strong></font></center></th>
									
								
								</tr>
								<tr>
									
									<th>FECHA</th>
									<TH>TIPO</TH>
									<TH>SERIE</TH>
									<TH>NUMERO</TH>
									<TH>DESCRIPCION</TH>
								
									
									<th>ENTRADAS</th>
								
									<th>SALIDAS</th>
								
									<th>SALDOS</th>
									<th>ORIGEN</th>
									<th>DESTINO</th>
								</tr>

								
							</thead>
							
							<tbody>
							@if(!empty($prod))
								
								@php

								  $bus_ven = DB::table('movimientos_productos')
								  ->where('IdProducto_rel',$prod)
								    ->where('id_almacen','=',$almacen)
								  ->where('fecha_mov','>=',$fecin)
								  ->where('fecha_mov','<=',$fecfin)
								  ->count();


				

								@endphp
								@if($bus_ven > '0')
								
									@if($saldo_anterior!=0)
								<tr>

									    <td></td>
									    <td></td>
										<td></td>
										<td></td>
										<td>SALDO ANTERIOR</td>
										<td hidden="hidden">0.000</td>
										

								
							
									

										<td>0.000</td>
									
									
										
										<td>0.000</td>
									
								 	   	<td>{{number_format($saldo_anterior,'3','.',',')}}</td>
						
								</tr>
								@endif
								@php

								$i=0;

								@endphp

								@foreach($movimientos as $c)
								@if(($c->IdProducto_rel == $prod) || ($c->IdProducto == $prod))
								
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

							            if($c->descripcion=='STOCK_INICIAL'){
											 	$saldo = $c->cantidad;
							                	$stock = $saldo;
											}else{
							            
							            if($c->mov_tip=='I'){

							                $stock = $saldo+$c->cantidad;


							            }

							            if($c->mov_tip=='E'){

							                $stock = $saldo - $c->cantidad;


							            }
							        }
							        }


								@endphp

								<tr>

									    <td>{{$c->fecha_mov}}</td>
									    <td>{{$c->tdocod}}</td>
										<td>{{$c->serie}}</td>
										<td>{{$c->numero}}</td>
										<td>
											@if($c->descripcion=='STOCK_INICIAL')
												STOCK INICIAL
											@else
												{{$c->cliente}}
											@endif
											
										</td>
										<td hidden="hidden">{{number_format($c->stock_inicial,'3','.',',')}}</td>
										

									@if($c->mov_tip=='E')
									
										<td>0.000</td>
									
										<td>{{number_format($c->cantidad,'3','.',',')}}</td>
									
									@elseif($c->mov_tip=='I')

										<td>{{number_format($c->cantidad,'3','.',',')}}</td>
									
									
										
										<td>0.000</td>
									@endif
									
								 	   	<td>{{number_format($stock,'3','.',',')}}</td>
										<td>{{$c->origen}}</td>
										<td>{{$c->destino}}</td>

								
									@php
										 $saldo = $stock;
            							 $i = $i+1;
									@endphp
								</tr>
									@endif
								@endforeach
							
								@endif
								
								@endif

							</tbody>
						</table><br>
					</div>	
				
				</div>
		</div>
	</div>
</section>



@endsection