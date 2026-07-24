@extends('layouts.empresas')
@section('contenido')
	
	<section class="content">
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            			Nota de Créditos y Débitos del comprobante:   Serie: {{$sndocmod->serdoc}} / Número: {{$sndocmod->numdoc}}
	            	</div>
	            </div>
	        </div>
	</div>  
	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
						<table id="listcomp"  class="table table-bordered table-hover">
						<thead>
							<th>Fec. Emision</th>
							<th>Tipo Doc.</th>
							<th>Serie</th>
							<th>Número</th>
							<th>RUC / DNI / Otros</th>
							<th>Nombre o Razón Social</th>
							<th>Moneda</th>
							<th>Total</th>
							<th>PDF</th>
							<th>BAJAS</th>
							<th>OPCIONES</th>
							<th>Estado SUNAT</th>
						</thead>
						<tbody>

							@foreach($notas as $not)
							<tr>
							 	<td>{{Carbon::parse($not->ccafem)->format('d-m-Y')}}</td>
								<td>{{$not->tdodes}}</td>
								<td>{{$not->serdoc}}</td>
								<td>{{$not->numdoc}}</td>
								<td>{{$not->ccandi}}</td>
								<td>{{$not->ccanom}}</td>
								<td>{{$not->monnom}}</td>
								<td align="right">{{number_format($not->ccaitv,'2','.',',')}}</td>
								<td><a href="{{$not->ccaenlace}}.pdf" target="_blank"><center><i class="fa fa-file-pdf-o fa-lg"></i></center></a>
								</td>
								@if($not->ccabaj==NULL)
								    <td><center>---</center></td>
								@else
								 	<td><a href="{{config('global.ruta')}}/listarbajas/{{$not->IdCpe_nota}}/{{$not->tdocod}}">{{$not->ccabaj}}</a></td>
								@endif
								<td> 	
									<div class="dropdown">
									  <button class="btn btn-default btn-xs dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
									    Opciones
									    <span class="caret"></span>
									  </button>
									  <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
										@if($not->ccabaj==NULL && $not->ccasunrescod=='0')
											<li><a class="dropdown-item" href="{{config('global.ruta')}}/formbajacomprobante/{{$not->serdoc}}/{{$not->numdoc}}/{{$not->tdocod}}/{{Carbon::parse($not->ccafem)->format('d-m-Y')}}/{{$not->tdodes}}">Com. de Baja</a></li>
										@else
										    <li><a class="dropdown-item">Com. de Baja</a></li>
										@endif
									  </ul>
									</div>
									</td>

									@if($not->ccasunrescod=='0')
									<td>
										<a><center><img src="{{config('global.ruta')}}/icon/check.png" title="Enviado y Aceptado por SUNAT" height="20px" width="20px"></center></a>
									</td>
									@elseif($not->ccasunrescod!='0')
									<td>
									<a href="{{config('global.ruta')}}"><center><img src="{{config('global.ruta')}}/icon/update.png" title="En Proceso - Consultar" height="20px" width="20px"></center></a>
									</td>
									@else
									<td>
										<a><center><img src="{{config('global.ruta')}}/icon/help.png" title="Comunicarse a Soporte" height="20px" width="20px"></center></a>
									</td>
									@endif				
							</tr>

							@endforeach
						</tbody>
					</table>
				</div>	
				{{$notas->render()}}
			</div>	
			</div>
		</div>
</section>
@endsection