@extends('layouts.empresas')
@section('contenido')

<script type="text/javascript">

	$( document ).ready(function() {
   
	$("#checkAll").click(function () {
    	$('.items').not(this).prop('checked', this.checked);
	});

});

</script>

<section class="content">
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
			<div class="box-header" style="background-color:blue;">
          		<font size="2" color="white"><strong><center>CUENTAS POR PAGAR</center></strong></font>
          	</div>
				<div class="box-body">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
					
						
						@include('empresas.cuentaspagar.buscarcuentapagar')
						
					</div>
				</div>
			</div>
		</div>
	</div>
	{!!Form::open(array('url'=>'/cuentaspagar/cuentas','method'=>'POST','autocomplete'=>'off','files'=>'true','name'=>'formfact','id'=>'formfact'))!!}
    {{Form::token()}}
<div class="row">
    <div class="col-xs-12">
    	<div class="box">
	       	<div class="box-body">
			<table class="table table-striped table-bordered table-condensed table-hover">
				<thead>
					<th colspan="10"><STRONG><center>CUENTAS POR PAGAR</center></STRONG></th>
					<th colspan="1"><STRONG><center><button id="btnpagarcuentas" type="submit" class="btn btn-sm btn-success">PAGAR</button></center></STRONG></th>

				</thead>
				<thead>
					<th style="text-align:center;"></th>
					<th style="text-align:center;">CLIENTE</th>
					<th style="text-align:center;">N° DOC</th>
					<TH style="text-align:center;">FEC. DOC</TH>
					<th style="text-align:center;">FEC. VEN</th>
					<th style="text-align:center;">FEC. CANCEL</th>
					<th style="text-align:center;">IMPORTE</th>
					<th style="text-align:center;">ABONO</th>
					<th style="text-align:center;">SALDO</th>
					<th style="text-align:center;">ESTADO</th>
					<th style="text-align:center;">OPCIONES</th>
				</thead>
				@foreach($cuentas as $cuenta)
				<tr>
					@if($cuenta->estado_cob=='pendiente')
						<td><input type="checkbox" name="items[]" class="items" value="{{$cuenta->cue_pag_id}}"></td>	
					@else
						<td><input type="checkbox"  disabled="disabled" value="{{$cuenta->cue_pag_id}}"></td>	
							
					@endif
					
					<td>{{$cuenta->prov_raz}}</td>
					<td>{{$cuenta->com_doc_ser}} - {{$cuenta->com_doc_num}}</td>
					<td>{{$cuenta->com_fec}}</td>
					<td>{{$cuenta->fec_ven}}</td>
					<td>{{$cuenta->fec_pago}}</td>
					<td>{{$cuenta->total_com}}</td>
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
							<a href="/cuentaspagar/ingresar/{{$cuenta->cue_pag_id}}"><button type="button" class="btn btn-sm btn-success">Ingresar Pago</button></a>
								
						@else
							<a ><button disabled="disabled" class="btn btn-sm btn-success">Ingresar Pago</button></a>
							
						@endif-->
					
						<a href="/cuentaspagar/detalle/{{$cuenta->cue_pag_id}}"><button type="button" class="btn btn-sm btn-primary">Detalle Abonos</button></a>
					</td>
				</tr>	
			
				@endforeach

			</table>
		</div>
	
	</div>
</div>
</div>
	{!!Form::close()!!}				
</section>
@endsection
