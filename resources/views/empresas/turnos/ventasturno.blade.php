@extends('layouts.empresas')
@section('contenido')
<script>

	var href = $('#btnPrint').attr('href');
	
	$("#btnPrint").printPage({
		
		 
		  url: href,
		  attr: "href",
		  messageBox:false,
		  
	})
</script>

	<section class="content">
	<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
	    @if(session()->has('info'))
	    	<div class="alert alert-danger">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Alerta!</strong> {{ session('info') }}
			</div>
	    @endif


	    @if(session()->has('success'))
	    	<div class="alert alert-success">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>InformaciÃ³n!</strong> {{ session('success') }}
			</div>
	    @endif
	</div>
</div>
	
		 

             
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:#337ab7;">
          				<font color="white"><center><strong>ARQUEO DE TURNO  <br>CAJERO: {{$usuario->name}} {{$usuario->apeusu}} <Br>{{$datos->apertura}}   -   {{$datos->cierre}} </strong></center></font>
          			</div>
	            	<div class="box-body">
							<table id=""  class="table table-bordered table-hover">
							<thead>
							<tr style="font-size:10pt;font-weight:bold;color:white;background: #808080;">
								<th  style="font-size:10pt;font-weight:bold;color:white;background: #808080;" colspan='2'><center>INGRESOS</center></th>
								<th  style="font-size:10pt;font-weight:bold;color:white;background: #808080;"  colspan='2'><center>SALIDAS</center></th>
								<th  style="font-size:10pt;font-weight:bold;color:white;background: #808080;" colspan='2'><center>ESTADO DE PAGO</center></th>
							</tr>
							</thead>
							<tbody>
							<tr>
								<th >Fondo de Caja</th>
								<th style="text-align:right;">S/. {{number_format($datos->monto,'2','.',',')}}</th>
								<th >Gastos Caja</th>
								<th style="text-align:right;">S/. {{number_format($totalgas,'2','.',',')}}</th>
								<th >Ventas Credito</th>
								<th style="text-align:right;">S/. {{number_format($credito,'2','.',',')}}</th>
							</tr>
								
													
							<tr>
								<th >Ingresos Caja</th>
								<th style="text-align:right;">S/. {{number_format($totaling,'2','.',',')}}</th>
								<th >Compras</th>
								<th style="text-align:right;">S/. {{number_format($compras,'2','.',',')}}</th>
								<th >Ventas Contado</th>
								<th style="text-align:right;">S/. {{number_format($total,'2','.',',')}}</th>
							</tr>

							@if(!empty($sum_mp))
									@foreach($sum_mp as $mp)
									
									<tr>
										<th >{{ucwords(strtolower($mp->nom_med_pag))}}</th>
										<th style="text-align:right;">S/. {{number_format($mp->monto_total,'2','.','')}}</th>
										<th ></th>
										<th></th>
										<th></th>
										<th></th>
								</tr>

									@endforeach
									@endif

						
							</tbody>
							
									
								
									
									
							</table>
							<br>



							<table id=""  class="table table-bordered table-hover table-striped" >
							<thead>
							<tr style="font-size:10pt;font-weight:bold;color:white;background: #808080;">
								<th  style="font-size:10pt;font-weight:bold;color:white;background: #808080;" colspan='6'><center>RESUMEN</center></th>
							
							</tr>
							</thead>
						
							<tbody>
							<tr>
								<th  style="font-size:10pt;font-weight:bold;width:250px;">(+) TOTAL EFECTIVO</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo+$datos->monto+$totaling),'2','.',',')}}</th>
								<th  style="font-size:10pt;font-weight:bold;width:150px;"></th>
								<th  style="font-size:10pt;font-weight:bold;width:250px;">(+) VENTAS EFECTIVO</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo),'2','.',',')}}</th>
							</tr>
							<tr>
								<th style="font-size:10pt;font-weight:bold;width:250px;">(-) TOTAL GASTOS</th>
								<th style="text-align:right;">S/. {{number_format(($totalgas+$compras),'2','.',',')}}</th>
									<th  style="font-size:10pt;font-weight:bold;width:150px;"></th>
								<th  style="font-size:10pt;font-weight:bold;width:250px;">(+) VENTAS OTROS M. PAGOS</th>
								<th style="text-align:right;">S/. {{number_format(($otros_medios),'2','.',',')}}</th>
								
							<tr>
							
								<th style="font-size:10pt;font-weight:bold;color:white;background: #808080;width:250px;">SALDO</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo+$datos->monto)-($totalgas+$compras),'2','.',',')}}</th>
									<th  style="font-size:10pt;font-weight:bold;width:150px;"></th>
								<th  style="font-size:10pt;font-weight:bold;color:white;background: #808080;width:250px;">TOTAL VENTAS</th>
								<th style="text-align:right;">S/. {{number_format(($efectivo+$otros_medios),'2','.',',')}}</th>
							</tr>
								

							</tbody>
							
									
								
									
									
							</table>
							<br>


							<table id=""  class="table table-bordered table-hover">
							<thead>
						
								<tr style="font-size:10pt;font-weight:bold;color:white;background: #808080;">
									<th style="text-align:center;vertical-align:middle;">Item</th>
									<th style="text-align:center;vertical-align:middle;">Fec. Emision</th>
									<th style="text-align:center;vertical-align:middle;">Tipo</th>
									<th style="text-align:center;vertical-align:middle;">Serie</th>
									<th style="text-align:center;vertical-align:middle;">N°</th>
									<th style="text-align:center;vertical-align:middle;">PEDIDO</th>
									<th style="text-align:center;vertical-align:middle;">RUC / DNI / Otros</th>
									<th style="text-align:center;vertical-align:middle;" style="width:210px;">Nombre o Razón Social</th>
									<th style="text-align:center;vertical-align:middle;">Moneda</th>
									<th style="text-align:center;vertical-align:middle;">Total<br>Comprobantes</th>
									<th style="text-align:center;vertical-align:middle;">Total<br>Pedidos</th>
									<th style="text-align:center;vertical-align:middle;">NOTAS</th>
									<th style="text-align:center;vertical-align:middle;">BAJAS</th>
						
								
								</tr>
							</thead>
							
							<tbody>

								@php
									$i=0;
								@endphp
								@foreach($comprobantes as $comp)

								@php
									$i = $i+1;
								@endphp
								<tr>
									<th style="text-align:center;">{{$i}}</th>
								 	<td>{{Carbon::parse($comp->ccafem)->format('d-m-Y')}}</td>
									<td>{{$comp->tdodes}}</td>
									<td>{{$comp->serdoc}}</td>
									<td style="background:#11B115">{{$comp->numdoc}}</td>
									<td style="background:#E7BC0D">{{$comp->ped_id}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
									<td>{{$comp->monnom}}</td>
									<td align="right" style="font-weight:bold;background:#11B115">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									<td align="right" style="font-weight:bold;background:#E7BC0D">{{number_format($comp->ped_tot,'2','.',',')}}</td>
								
								


									
										@if($comp->ccanot=="")
										   <td><center>---</center></td>
										@else
											<td>{{$comp->ccanot}}</td>
										@endif

										@if($comp->ccabaj=="")
										    <td><center>---</center></td>
										@else
										 	<td><a href="/consultarticketbaja/{{$comp->IdCpe_cabecera}}">{{$comp->ccabaj}}</a></td>
										@endif
									

									
									

										
								</tr>
									@include('empresas.comprobantes.modal')
								@endforeach
							</tbody>
						</table><br>
					</div>	
					{{$comprobantes->render()}}
				</div>	
			</div>
		</div>
	</section>

@endsection