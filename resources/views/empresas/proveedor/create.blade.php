@extends ('layouts.empresas')
@section ('contenido')
	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <h4><i class='glyphicon glyphicon-user'></i><strong> NUEVO PROVEEDOR</strong></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

	{!!Form::open(array('url'=>'proveedor','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="cmbTdi">Tipo Documento Ident.</label>
                <select class="form-control"  name="cmbTdi" id="cmbTdi">
                    <option></option>
                    @foreach($documentos as $doc)
                        <option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
                    @endforeach
                </select>
               
           </div>
        </div>
    	<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
    		<div class="form-group form-group-sm">
		       	<label for="txtProvRuc">Número Documento Ident.</label>
		        <input type="text" name="txtProvRuc" value="{{old('txtProvRuc')}}" class="form-control" >
          
           </div>
    	</div>

    	<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
    		<div class="form-group form-group-sm">
		       	<label for="txtProvRaz">Nombre de Cliente o Razón Social</label>
		        <input type="text" name="txtProvRaz" value="{{old('txtProvRaz')}}" class="form-control" >

           </div>
    	</div>
        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="txtProvDir">Dirección</label>
                <input type="text" name="txtProvDir" value="{{old('txtProvDir')}}" class="form-control" >
              
           </div>
        </div>
 
        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="txtProvCor">Correo Electrónico</label>
                <input type="text" name="txtProvCor" value="{{old('txtProvCor')}}" class="form-control" >
                  
           </div>
        </div>
            <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
            <div class="form-group form-group-sm">
                                <label>Persona de Contacto</label>
                                <input name="txtProvCon" id="txtProvCont" value="{{old('txtProvCont')}}" class="form-control">
                             
                            </div>
			</div>
            </div>

        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                    <label for="txtProvNumCon">Número de Contacto</label>
                                    <input type="text" name="txtProvNumCon" value="{{old('txtProvNumCon')}}" class="form-control" >
                                     
                         </div>
                    </div>
    </div>

    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="{{config('global.ruta')}}/clientes"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
            </div>
    	</div>
    </div>
</div>
       </div>
            </div>
       </div>
   </section>
	{!!Form::close()!!}
@endsection
