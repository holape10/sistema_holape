@extends ('layouts.empresas')
@section ('contenido')

<section class="content">
    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class='glyphicon glyphicon-user'></i><strong> EDITAR PROVEEDOR</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
      </div>

      <div class="row">
        <div class="col-xs-12">
          <div class="box">
            <div class="box-body">
          {!!Form::model($proveedor,['method'=>'PATCH','route'=>['proveedor.update',$proveedor->prov_id,$proveedor->rucemp],'files'=>'true'])!!}
          {{Form::token()}}
            <div class="row">
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="cmbTdi">Tipo Documento Ident.</label>
                    <select class="form-control"  name="cmbTdi" id="cmbTdi">
                        <option></option>
                        @foreach($documentos as $doc)
                         @if($doc->tdicod==$proveedor->tdicod)

                           <option value="{{$doc->tdicod}}" selected>{{$doc->tdides}}</option>
                          @else
                            <option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
                         @endif
                        @endforeach
                    </select>
                  
               </div>
              </div>
            	<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            		<div class="form-group form-group-sm">
        		       	<label for="txtnum">Número Documento Ident.</label>
        		        <input type="text" name="txtnum" value="{{$proveedor->prov_ruc}}" class="form-control" placeholder="Número de Documento de Identidad...">
                      
                   </div>
            	</div>
        
          	<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
              <div class="form-group form-group-sm">
                  <label for="txtProvRaz">Nombre de Cliente o Razón Social</label>
                  <input type="text" name="txtProvRaz" value="{{$proveedor->prov_raz}}" class="form-control" placeholder="Nombre de Cliente ó Razón Social...">
                  
                 </div>
          	</div>
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txtProvDir">Dirección</label>
                    <input type="text" name="txtProvDir" value="{{$proveedor->prov_dir}}" class="form-control" >
                
               </div>
              </div>
       
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txtProvCor">Correo Electrónico</label>
                    <input type="text" name="txtProvCor" value="{{$proveedor->prov_cor}}" class="form-control" >
                    
               </div>
              </div>
                    <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
            <div class="form-group form-group-sm">
                                <label>Persona de Contacto</label>
                                <input name="txtProvCon" id="txtProvCont" value="{{$proveedor->prov_con}}" class="form-control">
                             
                            </div>
      </div>
            </div>

              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                    <label for="txtProvNumCon">Número de Contacto</label>
                    <input type="text" name="txtProvNumCon" value="{{$proveedor->prov_num_con}}" class="form-control" >
                
              </div>
            </div>
          </div>
          <div hidden="hidden" class="row">
              <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
                <div class="form-group form-group-sm">
                  <label for="cliest">Estado</label>
                          <select name="cliest"  class="form-control">
                          @if($proveedor->cliest=='Activo')
                              <option value="{{$proveedor->prov_est}}" selected>{{$proveedor->prov_est}}</option>
                              <option value="Inactivo">Inactivo</option>
                          @else
                              <option value="{{$proveedor->prov_est}}" >{{$proveedor->prov_est}}</option>
                              <option value="Activo">Activo</option>
                          @endif
                      </select>
                </div>
              </div>
          </div>
     </div>
      <div class="row">
      	<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
      		 <div class="form-group form-group-sm">
              	<button class="btn btn-primary" type="submit">Guardar</button>
              	<a href="{{config('global.ruta')}}/proveedor"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
