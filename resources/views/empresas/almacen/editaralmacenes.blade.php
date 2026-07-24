@extends ('layouts.empresas')
@section ('contenido')


<section class="content">
 

    {!!Form::open(array('url'=>'/actualizaralmacenes','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
	<div class="row">
        	<div class="col-lg-12">
          		<div class="box">
                     <div class="box-header with-border" style="background-color:#337ab7;">
                        <center><font color="white"><strong>EDITAR ALMACEN</strong></font></center>
                     </div>
	            	<div class="box-body">
                        <div class="row">
                            <div class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
                            <div class="form-group form-group-sm">
                                <label for="codigo">C&oacute;digo:</label>
                                <input type="text" name="codigo" id="codigo" value="{{$almacen->codigo}}" class="form-control" >
                               
                               </div>
                          </div>
                          
                          
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                <div class="form-group form-group-sm">
                                    <label>Empresas</label>
                                    <select class="form-control" name="sucursal">
                                         @foreach($negocios as $negocio)
                                           @if($negocio->id_empresa_negocio == $almacen->id_empresa_negocio)
                                            <option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
                                          @else
                                              <option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
                                         @endif
                                         @endforeach
                                    </select>
                                </div>
                            </div>

                        	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        		<div class="form-group form-group-sm">
                    		       	<label for="descripcion">DESCRIPCION</label><font color="red">*</font>
                    		        <input type="text" name="descripcion" id="descripcion" value="{{$almacen->descripcion}}" class="form-control" >
                    		        <input type="hidden" name="id_almacen"  value="{{$almacen->id_almacen}}" class="form-control" >
                               </div>
                        	</div>
                          
                          
                            <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                <div class="form-group form-group-sm">
                            
                                      <div class="form-group form-group-sm">
                                    <label>Ubigeo</label>
                                    <select class="form-control selectpicker input-sm" data-show-subtext="true" data-live-search="true" name="ubigeo">
                                         @foreach($ubigeo as $ubi)
                                         @if($ubi->ubi_cod == $almacen->ubigeo)
                                            <option selected="selected" value="{{$ubi->ubi_cod}}">{{$ubi->ubi_des}}</option>
                                          @else
                                              <option value="{{$ubi->ubi_cod}}">{{$ubi->ubi_des}}</option>
                                          @endif
                                         @endforeach
                                    </select>
                                </div>
                               </div>
                            </div>

                                <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
                                <div class="form-group form-group-sm">
                                    <label for="direccion">Dirección</label><font color="red">*</font>
                                    <input type="text" name="direccion" id="direccion" value="{{$almacen->direccion}}" class="form-control" >
                               </div>
                            </div>
                        </div>
          
                        <div class="row">
                        	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                        		 <div class="form-group form-group-sm">
                                	<button class="btn btn-primary" type="submit">Guardar</button>
                                	<a href="/administrador/empresas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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