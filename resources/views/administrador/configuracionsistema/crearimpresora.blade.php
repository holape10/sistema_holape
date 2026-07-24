@extends ('layouts.empresas')
@section ('contenido')
<script type="text/javascript">

    $(document).ready(function()
    {
        
    })

</script>

<section class="content">
    <div class="row">
            <div class="col-lg-12">
                <div class="box">
                    <div class="box-body">
                        <div class="row">
                            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                <h3>Nueva Empresa</h3>
                              
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>

    {!!Form::open(array('url'=>'/impresoras/registrarimpresora','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
	<div class="row">
        	<div class="col-lg-12">
          		<div class="box">
	            	<div class="box-body">
                        <div class="row">
                        	<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                        		<div class="form-group form-group-sm">
                    		       	<label for="impresora">IMPRESORA</label><font color="red">*</font>
                    		        <input type="text" name="impresora" id="impresora" value="{{old('impresora')}}" class="form-control" >
                               </div>
                        	</div>
                        	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
                        		<div class="form-group form-group-sm">
                    		       	<label for="ruta">RUTA</label><font color="red">*</font>
                    		        <input type="text" name="ruta" id="ruta" value="{{old('ruta')}}" class="form-control" >
                                   
                               </div>
                        	</div>
                            <input type="hidden" name="id_empresa_negocio" value="{{$id_empresa_negocio}}">
                        </div>
          
                        <div class="row">
                        	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        		 <div class="form-group form-group-sm">
                                	<button class="btn btn-primary" type="submit">Guardar</button>
                                	<a href="{{config('global.ruta')}}/administrador/empresas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
                                </div>
                        	</div>
                        </div>
	               </div>
	           </div>
	       </div>
    </div>
	{!!Form::close()!!}    
</section>
@endsection