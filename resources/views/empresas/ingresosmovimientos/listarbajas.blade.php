@extends('layouts.empresas')
@section('contenido')

	<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            		@include('empresas.comprobantes.buscarbajas')
	            	</div>
	            </div>
	        </div>
	</div>          
	
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
					<table id="listbaja" class="table table-striped table-bordered">
						<thead>
							<th>Fec.Comunicación</th>
							<th>Núm. Baja.</th>
							<th>RUC</th>
							<th>Razón Social</th>
							<th>Tipo Documento</th>
							<th>Serie - Número</th>
							<th>Fec Emisión</th>
							<th>Motivo de Baja</th>
							<th>PDF</th>
							<th>Estado SUNAT</th>
						</thead>
						<tbody>

							@foreach($bajas as $baj)
							<tr>
								<td>{{Carbon::parse($baj->cbdfco)->format('d-m-Y')}}</td>
							 	<td>{{$baj->cbacor}}</td>
							 	<td>{{$baj->ccandi}}</td>
							 	<td>{{$baj->ccanom}}</td>
								<td>{{$baj->tdodes}}</td>
								<td>{{$baj->cbanum}}</td>
								<td>{{Carbon::parse($baj->cbafec)->format('d-m-Y')}}</td>
								<td>{{$baj->cbamot}}</td>
								<?php $enlace = str_replace("https://www.pse.pe/anulacion","https://econosystemsperu.pse.pe/see_voided",$baj->ccaenlace); ?>
								<td><a href="{{$enlace}}.pdf" target="_blank"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a></td>
									@if($baj->ccasunrescod=='0')
									<td>
										<a><center><img src="/icon/check.png" title="Enviado y Aceptado por SUNAT" height="20px" width="20px"></center></a>
									</td>
									@elseif($baj->ccasunrescod!='0')
									<td>
									<a href="/Bajas/estado/{{$baj->ccasuntick}}"><center><img src="/icon/update.png" title="En Proceso - Consultar" height="20px" width="20px"></center></a>
									</td>
									@else
									<td>
										<a><center><img src="/icon/help.png" title="Comunicarse a Soporte" height="20px" width="20px"></center></a>
									</td>
									@endif
							</tr>

							@endforeach
						</tbody>
					</table>
						</div>	
					{{$bajas->render()}}
				</div>	
			</div>
		</div>
	</section>

@endsection