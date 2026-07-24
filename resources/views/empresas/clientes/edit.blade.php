@extends ('layouts.empresas')
@section ('contenido')
<section class="content">
    <div class="row">
        <div class="col-md-10 col-md-offset-1">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title"><i class="fa fa-edit"></i> Editar Cliente</h3>
                </div>

                {!!Form::model($clientes,['method'=>'PATCH','route'=>['clientes.update',$clientes->clicod,$clientes->rucemp],'files'=>'true'])!!}
                {{Form::token()}}
                
                <div class="box-body">
                    <h4 class="text-primary">Datos de Identificación</h4>
                    <hr style="margin-top: 5px; margin-bottom: 15px;">
                    <div class="row">
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="tdicod">Tipo de Documento</label>
                                <select class="form-control" name="tdicod" id="tdicod">
                                    <option value="" disabled>Seleccione...</option>
                                    @foreach($documentos as $doc)
                                        <option value="{{$doc->tdicod}}" {{ $doc->tdicod == $clientes->tdicod ? 'selected' : '' }}>
                                            {{$doc->tdides}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3 col-sm-6">
                            <div class="form-group">
                                <label for="clinum">Número de Documento</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-id-card"></i></span>
                                    <input type="text" name="clinum" readonly="readonly" value="{{$clientes->clinum}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                <label for="clinom">Razón Social / Nombre Completo</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                    <input type="text" name="clinom" value="{{$clientes->clinom}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2 col-sm-6">
                            <div class="form-group">
                                <label for="cliest">Estado</label>
                                <select name="cliest" class="form-control">
                                    <option value="Activo" {{ $clientes->cliest == 'Activo' ? 'selected' : '' }}>Activo</option>
                                    <option value="Inactivo" {{ $clientes->cliest == 'Inactivo' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <h4 class="text-primary mt-4">Dirección Principal</h4>
                    <hr style="margin-top: 5px; margin-bottom: 15px;">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="clidir">Dirección Fiscal / Principal</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-map-marker"></i></span>
                                    <input type="text" name="clidir" value="{{$clientes->clidir}}" class="form-control">
                                </div>
                            </div>
                        </div>
                         <div class="col-md-4">
                            <div class="form-group">
                                <label for="clidir">Cuenta Cliente 12</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-sort-numeric-asc"></i></span>
                                    <input type="text" name="cuenta12" value="{{$clientes->cuenta12}}" class="form-control" placeholder="Cuenta Contable Cliente 12...">
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="text-primary mt-4">Contacto y Fidelización</h4>
                    <hr style="margin-top: 5px; margin-bottom: 15px;">
                    <div class="row">
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                <label for="clitel">Celular</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-whatsapp"></i></span>
                                    <input type="text" name="clitel" value="{{$clientes->telefono}}" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-6">
                            <div class="form-group">
                                <label for="clicor">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                    <input type="email" name="clicor" value="{{$clientes->clicor}}" class="form-control">
                                </div>
                                @if ($errors->has('clicor'))
                                    <span class="help-block"><strong><font color="red">{{ $errors->first('clicor') }}</font></strong></span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-4 col-sm-12">
                            <div class="form-group">
                                <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-birthday-cake"></i></span>
                                    <input type="date" name="fecha_nacimiento" class="form-control" value="{{$clientes->fecha_nacimiento}}">
                                </div>
                            </div>
                        </div>
                    </div>



                    <!-- Campos ocultos -->
                    <div style="display: none;">
                        <input type="text" name="clidir1" value="{{$clientes->direccion1}}">
                        <input type="text" name="clidir2" value="{{$clientes->direccion2}}">
                        <input type="text" name="clidir3" value="{{$clientes->direccion3}}">
                        <input type="text" name="clidir4" value="{{$clientes->direccion4}}">
                        <input type="text" name="clidir5" value="{{$clientes->direccion5}}">
                        <input type="text" name="clicor2" value="{{$clientes->clicor2}}">
                        <input type="text" name="clicor3" value="{{$clientes->clicor3}}">
                        <input type="text" name="clicor4" value="{{$clientes->clicor4}}">
                    </div>
                </div>

                <div class="box-footer text-right">
                    <a href="{{config('global.ruta')}}/clientes" class="btn btn-default"><i class="fa fa-arrow-left"></i> Cancelar</a>
                    <button class="btn btn-primary" type="submit"><i class="fa fa-save"></i> Actualizar Cliente</button>
                </div>

                {!!Form::close()!!}
            </div>
        </div>
    </div>
</section>
@endsection