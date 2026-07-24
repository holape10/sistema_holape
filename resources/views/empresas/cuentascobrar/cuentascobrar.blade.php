@extends('layouts.empresas')
@section('contenido')
@include('empresas.cuentascobrar.modaldetallecuotas')

<script type="text/javascript">

	$( document ).ready(function() {
   
	$("#checkAll").click(function () {
    	$('.items').not(this).prop('checked', this.checked);
	});

});

function buscarcuotas(venta){
 
  
        $.ajax({
          type: "GET",
          dataType: 'json',
          url: "/cuotas/"+venta,

        }).done(function(respuesta){

          $("#modal-cuotas").modal("show");

          $("#divcuotas").html(respuesta.vista);

        });
   


}
</script>

<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-header" style="background:blue;">
					<center><font color="white" size="3"><strong>CUENTAS POR COBRAR</strong></font></center>
				</div>
				<div class="box-body">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						
						
						@include('empresas.cuentascobrar.buscarcuentacobra')
						
					</div>
				</div>
			</div>
		</div>
	</div>
	{!!Form::open(array('url'=>'/cuentascobrar/cuentas','method'=>'POST','autocomplete'=>'off','files'=>'true','name'=>'formfact','id'=>'formfact'))!!}
    {{Form::token()}}
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th colspan="10"><STRONG><center>CUENTAS POR COBRAR</center></STRONG></th>
					<th colspan="1"><STRONG><center><button id="btncobrarcuentas" type="submit" class="btn btn-sm btn-success">COBRAR</button></center></STRONG></th>

				</thead>
				<thead>
					<th><!--SELEC. <input type="checkbox" id="checkAll" >--></th>
					<th>CLIENTE</th>
					<th>N° DOC</th>
					<TH>FEC. DOC</TH>
					<th>FEC. VEN</th>
					<th>FEC. CANCELACIÓN</th>
					<th>IMPORTE</th>
					<th>ABONO</th>
					<th>SALDO</th>
					
				
					<th>ESTADO</th>
					<th>OPCIONES</th>
				</thead>
				@if(!empty($cuentas))
				@foreach($cuentas as $cuenta)
				<tr>
					@if($cuenta->estado_cob=='pendiente')
						<td><input type="radio" name="items[]" class="items" value="{{$cuenta->cue_cob_id}}"></td>	
					@else
						<td><input type="radio"  disabled="disabled" value="{{$cuenta->cue_cob_id}}"></td>	
							
					@endif
					
					<td>{{$cuenta->clinom}}</td>
					<td>{{$cuenta->serdoc}} - {{$cuenta->numdoc}}</td>
					<td>{{$cuenta->ccafem}}</td>
					<td>{{$cuenta->fec_ven}}</td>
					<td>{{$cuenta->fec_pago}}</td>
					<td>{{$cuenta->ccaitv}}</td>
					<td>{{$cuenta->abono}}</td>
					<td>{{$cuenta->saldo}}</td>
					
				
					<td>
						@if($cuenta->estado_cob=='pendiente')
							<button type="button" class="btn btn-danger btn-sm btn-block">{{$cuenta->estado_cob}}</button>
						@elseif($cuenta->estado_cob=='cancelado')
							<button type="button" class="btn btn-success btn-sm btn-block">{{$cuenta->estado_cob}}</button>

						@endif
					</td>
					<td>
						<!--@if($cuenta->estado_cob=='pendiente')
							<a href="/cuentascobrar/ingresar/{{$cuenta->cue_cob_id}}"><button type="button" class="btn btn-sm btn-success">Ingresar Pago</button></a>
								
						@else
							<a ><button disabled="disabled" class="btn btn-sm btn-success">Ingresar Pago</button></a>
							
						@endif-->
					
						<a href="/cuentascobrar/detalle/{{$cuenta->cue_cob_id}}"><button type="button" class="btn btn-sm btn-primary">Detalle Pagos</button></a>
						<button type="button" class="btn btn-sm btn-warning" onclick="buscarcuotas({{$cuenta->IdCpe_cabecera}})">CUOTAS</button>
					</td>
				</tr>	
			
				@endforeach
				@endif
			</table>
		</div>
	
	</div>
</div>
</div>
	{!!Form::close()!!}				
</section>
@endsection
