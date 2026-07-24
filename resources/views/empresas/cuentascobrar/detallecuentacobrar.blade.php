@extends('layouts.empresas')
@section('contenido')

<section class="content">
	
  <div class="row">
             <div class="col-xs-12">
                <a href="/cuentascobrar"><button class="btn btn-success">CUENTAS POR COBRAR</button></a>
            </div>
        </div><br>
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					
					<th colspan="13"><STRONG><center>DETALLE CUENTA POR COBRAR: {{$cuenta->tdodes}} {{$cuenta->serdoc}}-{{$cuenta->numdoc}} - CLIENTE: {{$cuenta->clinom}}</center></STRONG></th>
					
				</thead>
				<thead>
				
					<th style="text-align:center;">FECHA DE ABONO</th>
					<th style="text-align:center;"># RECIBO</th>
					<th style="text-align:center;">VENDEDOR</th>
					<th style="text-align:center;">ABONO</th>
					<th style="text-align:center;">COMENTARIO</th>
					<th style="text-align:center;">OPCIONES</th>
				</thead>
				@foreach($detalle as $det)
				<tr>
					<td>{{$det->fec_dep}}</td>
					<td>{{$det->numero_recibo}}</td>
					<td>{{$det->name}} {{$det->apeusu}}</td>
					<td>{{$det->abono}}</td>
					<td hidden="hidden">{{strtoupper($det->ban_nom)}} - CUENTA {{strtoupper($det->tip_cuen_nom)}} {{strtoupper($det->monnom)}} {{strtoupper($det->cuen_ban_num)}}</td>
					<td hidden="hidden">{{$det->num_oper}}</td>
					<td>{{$det->comentario}}</td>
					<td>
						<a href="/editarcobro/{{$det->cue_cob_det_id}}"><button type="button" class="btn btn-success btn-sm ">EDITAR</button></a> 
						<a href="" data-target="#modal-delete-{{$det->cue_cob_det_id}}" data-toggle="modal"><button type="button" class="btn btn-danger btn-sm">ELIMINAR</button></a>
					</td>
				
				</tr>	
				
					@include('empresas.cuentascobrar.modaleliminar')
				@endforeach

			</table>
		</div>
	
	</div>
</div>
</div>
</section>
@endsection
