@extends ('layouts.empresas')
@section ('contenido')

<section class="content">
    {!!Form::model($empresa,['method'=>'PATCH','route'=>['empresas.update',$empresa->IdEmpresa],'files'=>'true'])!!}
    {{Form::token()}}

    <div class="row">
        <div class="col-md-12">
            <!-- SECCIÓN 1: DATOS GENERALES -->
            <div class="box box-primary shadow">
                <div class="box-header with-border" style="background: #2c3e50; color: white;">
                    <h3 class="box-title"><i class="fa fa-industry"></i> <strong>EDITAR EMPRESA</strong></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label for="rucEmpresa">RUC <span class="text-danger">*</span></label>
                                <input readonly type="text" name="rucEmpresa" value="{{ $empresa->IdEmpresa}}" class="form-control" style="background: #eee; font-weight: bold;">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group form-group-sm">
                                <label for="nomEmpresa">Razón Social <span class="text-danger">*</span></label>
                                <input type="text" name="nomEmpresa" value="@if($empresa->NomEmpresa==''){{old('nomEmpresa')}}@elseif(old('nomEmpresa')!=''){{old('nomEmpresa')}}@else{{$empresa->NomEmpresa}}@endif" class="form-control" placeholder="Razón Social...">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group form-group-sm">
                                <label for="dirEmpresa">Dirección</label>
                                <input type="text" name="dirEmpresa" value="@if($empresa->DirEmpresa==''){{old('dirEmpresa')}}@elseif(old('dirEmpresa')!=''){{old('dirEmpresa')}}@else{{$empresa->DirEmpresa}}@endif" class="form-control" placeholder="Direccion...">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 2: FACTURACIÓN ELECTRÓNICA -->
                <div class="box-header with-border" style="background: #34495e; color: white;">
                    <h3 class="box-title"><i class="fa fa-file-code-o"></i> <strong>FACTURACIÓN ELECTRÓNICA</strong></h3>
                </div>
                <div class="box-body bg-gray-light">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>TIPO DE ENVÍO</label>
                                <select name="tip_env_fac" class="form-control">
                                    @foreach($tip_env_fac as $tef)
                                        <option value="{{$tef->tip_env_fac_id}}" {{ $empresa->tip_env_fac_id == $tef->tip_env_fac_id ? 'selected' : '' }}>
                                            {{$tef->tip_env_fac_des}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Producción</label>
                                <select name="produccion" class="form-control">
                                    <option value="0" {{ $empresa->produccion == '0' ? 'selected' : '' }}>BETA</option>
                                    <option value="1" {{ $empresa->produccion == '1' ? 'selected' : '' }}>PRODUCCIÓN</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Envío a SUNAT</label>
                                <select class="form-control" name="envio">
                                    <option value="1" {{ $empresa->tipo_envio == '1' ? 'selected' : '' }}>ENVÍO AUTOMÁTICO</option>
                                    <option value="0" {{ $empresa->tipo_envio == '0' ? 'selected' : '' }}>ENVÍO MANUAL</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Usuario SUNAT</label>
                                <input type="text" name="txtWsUsuario" value="{{$empresa->wsusuario}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Contraseña SUNAT</label>
                                <div class="input-group group-sm">
                                    <input type="password" name="txtWsContrasena" id="txtWsContrasena" value="{{$empresa->claveSunat}}" class="form-control">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" onclick="togglePassword('txtWsContrasena', this)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <!-- CAMPO ICBPER RESTAURADO AQUÍ -->
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>ICBPER</label>
                                <input type="number" step="any" name="icbper" class="form-control" min='0' value="{{$empresa->icbper}}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 3: CERTIFICADO DIGITAL -->
                <div class="box-header with-border" style="background: #2980b9; color: white;">
                    <h3 class="box-title"><i class="fa fa-key"></i> <strong>CERTIFICADO DIGITAL</strong></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Certificado Digital (.pfx)</label>
                                <input type="file" name="txtCertificado" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Contraseña Certificado</label>
                                <div class="input-group group-sm">
                                    <input type="password" name="txtPassCert" id="txtPassCert" value="{{$empresa->passcert}}" class="form-control">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" onclick="togglePassword('txtPassCert', this)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Válido Desde:</label>
                                <input type="date" name="fecini" value="{{$empresa->fec_ini_cer}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Válido Hasta:</label>
                                <input type="date" name="fecfin" value="{{$empresa->fec_fin_cer}}" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 4: CONFIGURACIÓN DE IMPRESIÓN -->
                <div class="box-header with-border" style="background: #7f8c8d; color: white;">
                    <h3 class="box-title"><i class="fa fa-print"></i> <strong>CONFIGURACIÓN DE IMPRESIÓN</strong></h3>
                </div>
                <div class="box-body bg-gray-light">
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>IMP. PEDIDOS</label>
                                <input type="number" step="any" name="imp_pedido" class="form-control" min='1' value="{{$empresa->imp_pedido}}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>IMP. COMPROBANTES</label>
                                <input type="number" step="any" name="imp_venta" class="form-control" min='1' value="{{$empresa->imp_venta}}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Formato</label>
                                <select name="formato" class="form-control">
                                    <option value="TICKET" {{ $empresa->formato == 'TICKET' ? 'selected' : '' }}>TICKET</option>
                                    <option value="A4" {{ $empresa->formato == 'A4' ? 'selected' : '' }}>A4</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Ticket-Pantalla</label>
                                <select name="ticket_pantalla" class="form-control">
                                    <option value="1" {{ $empresa->ticket_pantalla == '1' ? 'selected' : '' }}>SI</option>
                                    <option value="0" {{ $empresa->ticket_pantalla == '0' ? 'selected' : '' }}>NO</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Logo Login</label>
                                <input type="file" name="logologin" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SECCIÓN 5: CORREO DE ENVÍO -->
                <div class="box-header with-border" style="background: #16a085; color: white;">
                    <h3 class="box-title"><i class="fa fa-envelope"></i> <strong>CONFIGURACIÓN DE CORREO DE ENVÍO</strong></h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label>Correo de Envío</label>
                                <input type="text" class="form-control" name="correo_envio" value="{{$empresa->correo_envio}}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group form-group-sm">
                                <label>Contraseña de Envío</label>
                                <div class="input-group group-sm">
                                    <input type="password" class="form-control" name="contrasena_envio" id="contrasena_envio" value="{{$empresa->contrasena_envio}}">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" onclick="togglePassword('contrasena_envio', this)">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ACCIONES FINALES -->
                <div class="box-footer">
                    <div class="pull-right">
                        <a href="{{config('global.ruta')}}/administrador/empresas" class="btn btn-default shadow-sm"><i class="fa fa-times"></i> Cancelar</a>
                        <button class="btn btn-primary shadow-sm" type="submit"><i class="fa fa-save"></i> Guardar Cambios</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {!!Form::close()!!}
</section>

<!-- SCRIPT TOGGLE PASSWORD -->
<script>
    function togglePassword(inputId, button) {
        var input = document.getElementById(inputId);
        var icon = button.querySelector('i');
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }
</script>

@endsection