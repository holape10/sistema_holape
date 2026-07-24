@extends('layouts.empresas')
@section('contenido')

<section class="content">

	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
          			<div class="box-header" style="background-color:blue;">
          				<font color="white"><center><strong>REGISTRO DE COMANDAS</strong></center></font>
          			</div>
	            	<div class="box-body">
	            		@include('empresas.restaurante.buscarcomandas')
	            	</div>
	            </div>
	        </div>
	</div>

    @if(Auth::User()->hasRole('admin') ||  Auth::User()->hasRole('superadmin'))
    <div class="row">
    <div class="col-lg-6 col-xs-6">
        <div class="small-box bg-yellow" style="border-radius: 10px;">
            <div class="inner" style="text-align: center; padding: 10px;">
                <p style="font-weight: bold; font-size: 28px; margin-bottom: 0;">PENDIENTES ({{ $cant_pendientes }})</p>
                <h3 style="font-size: 28px; margin: 5px 0;">S/ {{ number_format($monto_pendientes, 2) }}</h3>
                <span style="opacity: 0.8;">Por cobrar</span>
            </div>
            <div class="icon">
                <i class="fa fa-clock-o"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-6 col-xs-6">
        <div class="small-box bg-green" style="border-radius: 10px;">
            <div class="inner" style="text-align: center; padding: 10px;">
                <p style="font-weight: bold; font-size: 28px; margin-bottom: 0;">COBRADOS ({{ $cant_cobrados }})</p>
                <h3 style="font-size: 28px; margin: 5px 0;">S/ {{ number_format($monto_cobrados, 2) }}</h3>
                <span style="opacity: 0.8;">En caja hoy</span>
            </div>
            <div class="icon">
                <i class="fa fa-money"></i>
            </div>
        </div>
    </div>
</div>
@endif 
             
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body table-responsive">
    <table id="" class="table table-bordered table-hover table-responsive">
        <thead>
            <tr>
                <th style="text-align:center;width:150px;">Fecha</th>
                <th style="text-align:center;">Ubicación</th>
                <th style="text-align:center;">Cliente</th>
                <th style="text-align:center;">Productos</th> 
                <th style="text-align:center;">Estado</th>
            </tr>
        </thead>
        
        <tbody>
            @foreach($comandas as $com) 
            <tr>
                <td style="text-align:center;">
                    {{ \Carbon\Carbon::parse($com->fecha_hora)->format('d-m-Y g:i A') }}
                </td>
                <td>{{ $com->ped_tip }}</td>
                <td>
                    @if(!empty($com->mes_id)) 
                        {{ $com->mes_nom }} 
                    @else 
                        {{ $com->ped_cli_nom }} 
                    @endif
                </td>

                <td>
                    @if(isset($detalles_por_pedido[$com->ped_id]))
                        <ul style="padding-left: 15px; margin-bottom: 0; font-size: 0.9em;">
                            @foreach($detalles_por_pedido[$com->ped_id] as $det)
                                <li>
                                    <strong>{{ (float)$det->ped_det_can }}</strong> x 
                                    {{ $det->descripcion }}
                                    @if(!empty($det->item_obs))
                                        <small class="text-muted">({{ $det->item_obs }})</small>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <span class="text-muted">- Sin detalles -</span>
                    @endif
                </td>

                <td style="text-align:center;">
                    @if($com->ped_est=='Aperturado')
                        <span class="label label-warning" style="font-size:100%">{{$com->ped_est}}</span>
                    @elseif($com->ped_est=='Eliminado')
                        <span class="label label-danger" style="font-size:100%">{{$com->ped_est}}</span>
                    @elseif($com->ped_est=='Cerrado')
                        <span class="label label-success" style="font-size:100%">{{$com->ped_est}}</span>
                    @else
                        <span class="label label-default" style="font-size:100%">{{$com->ped_est}}</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>	
					
				</div>	
			</div>
		</div>
	</section>

@endsection