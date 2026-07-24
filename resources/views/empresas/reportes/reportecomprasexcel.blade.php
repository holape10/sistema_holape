@extends('layouts.reportes')
@section('contenido')



<section class="content">

        
	<div class="row">
		<div class="col-xs-12">
			<div class="box">
				<div class="box-body">
					<table id="tblCompra"  class="table table-bordered table-hover">
						<thead>
							<tr>
								<th>Fec. Compra</th>
								<th>Fec. Vencimiento</th>
								<th>Orden Compra</th>
								<th>Documento</th>
								<th>Serie</th>
								<th>N°</th>
								<th>RUC PROVEEDOR</th>
								<th style="width:210px;">Nombre o Razón Social</th>
								<th>Moneda</th>
								<th>Total</th>
								<th>Estado</th>
								
							</tr>
						</thead>
						<tbody>
							@foreach($compras as $comp)
							<tr>
								<td>{{$comp->com_fec}}</td>
								<td>{{$comp->com_fec_ven}}</td>
								<td>{{$comp->ser_oc}}-{{$comp->num_oc}}</td>
								<td>{{$comp->tdodes}}</td>
								<td>{{$comp->com_doc_ser}}</td>
								<td>{{$comp->com_doc_num}}</td>
								<td>{{$comp->prov_ruc}}</td>
								<td>{{$comp->prov_raz}}</td>
								<td>{{$comp->monnom}}</td>
								<td>{{number_format($comp->total_com,'2','.',',')}}</td>
								<td>{{$comp->est_compra}}</td>
							
					
							</tr>
							
							@endforeach
						</tbody>
					</table><br>
				</div>	
			</div>	
		</div>
	</div>
</section>

@endsection