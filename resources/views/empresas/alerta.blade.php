@extends('layouts.empresas')
@section('contenido')

<section class="content">
	<div class="row">
    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
	    @if(session()->has('danger'))
	    	<div class="alert alert-danger">
	    	  <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			  <strong>Alerta!</strong> {{ session('danger') }}
			</div>
	    @endif


	    
	</div>
</div>


</section>
@endsection
