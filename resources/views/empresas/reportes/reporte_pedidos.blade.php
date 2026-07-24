@extends('layouts.empresas')
@section('contenido')

<section class="content">	
	<div class="row">
        <div class="col-xs-12">
        	<div class="box">
        		<div class="box-header box-success" style="background-color:#00a65a;">
        			<font color="white" size="4"><center><strong>REPORTES DE PEDIDOS</strong></center></font>
        		</div>
	           	<div class="box-body">
	           		@include('empresas.reportes.buscar_pedidos')
	           	</div>
	         </div>
	    </div>
	</div> 

	<div class="row">
		<div class="col-xs-12">
			<div class="box">
	            	<div class="box-body">
						
					</div>	
				
				</div>
		</div>
	</div>
</section>




@endsection