@extends('layouts.empresas')
@section('contenido')


@if(isset($codfact))

         @php
          $pdf = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
        @endphp

      @endif
      
@include('empresas.puntosventas.modalpdf')
<style type="text/css">
	    #modal-pdf{
   z-index: 99999 !important;
}
</style>

<script>

	
$(document).ready(function(){


        $("#btnreg").on("click", function() {
          
           var placa = $("#placa").val();
                
         	  window.location.href = "/cotizaciones/"+placa;
 
         


          
        });

         $("#btnregot").on("click", function() {
          
           var placa = $("#placa").val();
                
         	  window.location.href = "/ordentrabajo/"+placa;
 
         


          
        });


        $("#btnregop").on("click", function() {
          
           var placa = $("#placa").val();
                
         	  window.location.href = "/ordenpedido/"+placa;
 
         


          
        });


	

});

</script>

@if(!empty($codfact))
    <script>

   $(document).ready(function()
   {

   $("#modal-pdf").modal("show");
 });
</script>
@endif


	<section class="content">


	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body">
	            		@include('empresas.puntosventas.buscarcotizaciones')
	            	</div>
	            </div>
	        </div>
	</div> 
             
    	<div class="row">
        	<div class="col-xs-12">
          		<div class="box">
	            	<div class="box-body table-responsive">
							<table id=""  class="table table-bordered table-hover">
							<thead>
								<tr>
									<th>Fec. Emision</th>
									<th>Tipo</th>
									<th>Serie</th>
									<th>N°</th>
									<th>RUC / DNI / Otros</th>
									<th style="width:210px;">Nombre o Razón Social</th>
									<th>Moneda</th>

									<th>Total</th>
									<th>PDF</th>
									<th>Referencia</th>
									<th>Estado</th>
									<th colspan="3">Opciones</th>
								</tr>
							</thead>
							
							<tbody>
								@foreach($comprobantes as $comp)
								<tr>
								 	<td>{{Carbon::parse($comp->fechacot)->format('d-m-Y')}}</td>
									<td>{{$comp->tdodes}}</td>
									<td>{{$comp->serdoc}}</td>
									<td>{{$comp->numdoc}}</td>
									<td title='{{$comp->tdides}}'>{{$comp->ccandi}}</td>
									<td style="width:210px;">{{$comp->ccanom}}</td>
									<td>{{$comp->monnom}}</td>
									<td align="right">{{number_format($comp->ccaitv,'2','.',',')}}</td>
									<td><a href="/descargar/{{$comp->IdCpe_cabecera}}/pdf"><center><i class="fa fa-file-excel-o fa-lg"></i></center></a></td>
								
									<td>{{$comp->referencia}}</td>
									<td>
										@if($comp->tdocod =='70')

											@if($comp->estado =='REGISTRADO')
											<span class=" btn-primary btn-sm btn-block">{{$comp->estado}}</span>
											@elseif($comp->estado =='COBRADO')
											<span class=" btn-success btn-sm btn-block">{{$comp->estado}}</span>
											@elseif($comp->estado =='ELIMINADO')
											<span class=" btn-danger btn-sm btn-block">{{$comp->estado}}</span>
											
											@endif

										@elseif($comp->tdocod =='80')

											@if($comp->estado =='REGISTRADO')
											<span class=" btn-primary btn-sm btn-block">{{$comp->estado}}</span>
											@elseif($comp->estado =='ACEPTADO')
											<span class=" btn-success btn-sm btn-block">{{$comp->estado}}</span>
											@elseif($comp->estado =='ELIMINADO')
											<span class="btn-danger btn-sm btn-block">{{$comp->estado}}</span>
											@endif

										@elseif($comp->tdocod =='90')
											@if($comp->estado =='REGISTRADO')
											<span class=" btn-primary btn-sm btn-block">{{$comp->estado}}</span>
											@elseif($comp->estado =='COBRADO')
											<span class=" btn-success btn-sm btn-block">{{$comp->estado}}</span>
											@elseif($comp->estado =='ELIMINADO')
											<span class=" btn-danger btn-sm btn-block">{{$comp->estado}}</span>
											@endif
										@else
											@if($comp->ccacodsun =='0')
											<span class=" btn-success btn-sm btn-block">{{$comp->ccadessun}}</span>
											@elseif($comp->estado !='0')
											<span class=" btn-primary btn-sm btn-block">{{$comp->ccadessun}}</span>
											@endif
										@endif

										
									</td>
									@if($comp->tdocod =='70')
										<td><a href="/editarot/{{$comp->IdCpe_cabecera}}"><button type="button" class="btn btn-sm btn-warning btn-block">Editar</button></a></td>
									@elseif($comp->tdocod =='80')
										<td><a href="/editarcotizacion/{{$comp->IdCpe_cabecera}}"><button type="button" class="btn btn-sm btn-warning btn-block">Editar</button></a></td>
									@elseif($comp->tdocod =='90')
										<td><a href="/editarop/{{$comp->IdCpe_cabecera}}"><button type="button" class="btn btn-sm btn-warning btn-block">Editar</button></a></td>


									@endif
									@if($comp->tdocod =='70')
										@if($comp->estado =='COBRADO')
											<td><button type="button" disabled="disabled" class="btn btn-sm btn-block btn-primary">COBRAR</button></td>
										@else
											<td><a href="/cobrar/{{$comp->IdCpe_cabecera}}"><button type="button"  class="btn btn-sm btn-primary btn-block">COBRAR</button></a></td>
										@endif
										
									@elseif($comp->tdocod =='90')
										@if($comp->estado =='COBRADO')
											<td><button type="button" disabled="disabled" class="btn btn-sm btn-block btn-primary">COBRAR</button></td>
										@else
											<td><a href="/cobrar/{{$comp->IdCpe_cabecera}}"><button type="button"  class="btn btn-sm btn-primary">COBRAR</button></a></td>
										@endif
										
									@else
										@if($comp->estado =='ACEPTADO')
											<td><button type="button" disabled="disabled" class="btn btn-sm btn-block btn-primary">Generar OT</button></td>
										@else
										 <td><a href="/generarot/{{$comp->IdCpe_cabecera}}"><button type="button" class="btn btn-sm btn-primary btn-block">Generar OT</button></a></td>
										@endif
										

									@endif
									
									<td><a href="/eliminar/{{$comp->IdCpe_cabecera}}"><button type="button" class="btn btn-sm btn-danger">Eliminar</button></a></td>
									
								</tr>
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