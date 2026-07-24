@extends('layouts.empresas')
@section('contenido')

<section class="content">
	
  <div class="row">
             <div class="col-xs-12">
                <a href="/cuentaspagar"><button class="btn btn-success">CUENTAS POR PAGAR</button></a>
            </div>
        </div><br>
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
    		<div class="box-header" style="background-color:blue;">
    			<font color="white"><STRONG><center>DETALLE CUENTA POR PAGAR: {{$cuenta->tdodes}} {{$cuenta->com_doc_ser}}-{{$cuenta->com_doc_num}} - PROVEEDOR: {{$cuenta->prov_raz}}</center></STRONG></font>
    		</div>
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				
				<thead>
				
					<th style="width:20%;">FECHA DEPOSITO</th>
					<th style="width:20%;">ABONO</th>
					<!--<TH>CUENTA BANCARIA</TH>
					<th>OPERACION</th>-->
					<th>COMENTARIO</th>
					<th style="text-align:center;">OPCIONES</th>
					
				</thead>
				@foreach($detalle as $det)
				<tr>
					<td>{{$det->fec_dep}}</td>
					<td>{{$det->abono}}</td>
					<!--<td>{{strtoupper($det->ban_nom)}} - CUENTA {{strtoupper($det->tip_cuen_nom)}} {{strtoupper($det->monnom)}} {{strtoupper($det->cuen_ban_num)}}</td>
					<td>{{$det->num_oper}}</td>-->
					<td>{{$det->comentario}}</td>
					<td>
						<a href="/editarpago/{{$det->cue_pag_det_id}}"><button type="button" class="btn btn-success btn-sm">EDITAR</button></a> 
						<a href="" data-target="#modal-delete-{{$det->cue_pag_det_id}}" data-toggle="modal"><button type="button"  class="btn btn-danger btn-sm">ELIMINAR</button></a>
					</td>
				
				</tr>	
					@include('empresas.cuentaspagar.modaleliminar')
				@endforeach

			</table>
		</div>
	
	</div>
</div>
</div>
</section>
@endsection
