@extends ('layouts.empresas')
@section ('contenido')

<style>
    /* Estilos para resaltar campos obligatorios y estados */
    .field-required { background-color: #fcf8e3 !important; border: 1px solid #f39c12 !important; }
    .field-mozo { background-color: #d9edf7 !important; border: 1px solid #3498db !important; }
    .shadow-box { box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-radius: 5px; }
    .section-title { 
        background: #f4f4f4; 
        padding: 10px; 
        font-weight: bold; 
        border-left: 4px solid #3c8dbc; 
        margin-bottom: 15px;
    }
</style>

<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary shadow-box">
                <div class="box-header with-border" style="background: #2c3e50; color: white;">
                    <h3 class="box-title"><i class="fa fa-user-plus"></i> <strong>REGISTRO DE NUEVO EMPLEADO</strong></h3>
                </div>

                <div class="box-body">
                    {!!Form::open(array('url'=>'empleado','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'frmEmpleado'))!!}
                    {{Form::token()}}

                    <!-- SECCIÓN 1: DATOS PERSONALES -->
                    <div class="section-title text-primary"><i class="fa fa-id-card"></i> INFORMACIÓN PERSONAL</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Sucursal de Trabajo</label>
                                <select class="form-control" name="id_empresa_negocio" id="id_empresa_negocio">
                                    @foreach($negocios as $neg)
                                        <option value="{{$neg->id_empresa_negocio}}">{{$neg->tipo_negocio}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Tipo Doc.</label>
                                <select class="form-control" name="tdicod" id="tdicod">
                                    @foreach($documentos as $doc)
                                        <option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>N° Documento <i class="fa fa-search text-info" title="Presione Enter para buscar"></i></label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="emp_num_doc" id="emp_num_doc" value="{{old('emp_num_doc')}}" 
                                           onKeypress="if(event.keyCode == 13) buscarcliente();" 
                                           class="form-control field-required" placeholder="DNI / CE">
                                    <span class="input-group-addon"><img style="display:none;" width="15px" src="/img/load.gif" id="imgload"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group form-group-sm">
                                <label>Nombres Completos</label>
                                <input type="text" name="emp_nom" id="emp_nom" value="{{old('emp_nom')}}" class="form-control field-required">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Apellido Paterno</label>
                                <input type="text" name="emp_ape_pat" id="emp_ape_pat" value="{{old('emp_ape_pat')}}" class="form-control field-required">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Apellido Materno</label>
                                <input type="text" name="emp_ape_mat" id="emp_ape_mat" value="{{old('emp_ape_mat')}}" class="form-control field-required">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Género</label>
                                <select name="sex_cod" id="sex_cod" class="form-control">
                                    @foreach($sexo as $sex)
                                        <option value="{{$sex->sex_cod}}">{{$sex->sex_nom}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Fecha Nac.</label>
                                <input type="date" name="emp_fec_nac" id="emp_fec_nac" value="{{old('emp_fec_nac')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Estado</label>
                                <select name="est_cod" id="est_cod" class="form-control">
                                    @foreach($estados as $est)
                                        <option value="{{$est->est_cod}}">{{$est->est_des}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="asistencia">Marca Asistencia</label>
                                {!! Form::select('asistencia', ['1' => 'SI', '0' => 'NO'], isset($empleado) ? $empleado->asistencia : '0', ['class' => 'form-control']) !!}
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 2: CONTACTO Y UBICACIÓN -->
                    <div class="section-title text-success"><i class="fa fa-envelope"></i> CONTACTO Y UBICACIÓN</div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Teléfono Fijo</label>
                                <input type="text" name="emp_tel" id="emp_tel" value="{{old('emp_tel')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Celular</label>
                                <input type="text" name="emp_cel" id="emp_cel" value="{{old('emp_cel')}}" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Correo Electrónico</label>
                                <input type="email" name="emp_cor" id="emp_cor" value="{{old('emp_cor')}}" class="form-control" placeholder="ejemplo@correo.com">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group form-group-sm">
                                <label>Dirección de Residencia</label>
                                <input type="text" name="emp_dir" id="emp_dir" value="{{old('emp_dir')}}" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- SECCIÓN 3: CUENTA Y ACCESO AL SISTEMA -->
                    <div class="section-title text-warning"><i class="fa fa-lock"></i> CREDENCIALES Y ROL</div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Usuario de Acceso</label>
                                <input type="text" name="email" id="email" value="{{old('email')}}" class="form-control field-required" placeholder="Nombre de usuario">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group form-group-sm">
                                <label>Rol en el Sistema</label>
                                <select name="rol_id" id="rol_id" class="form-control field-required">
                                    @foreach ($roles as $rol)
                                        <option value="{{$rol->id}}">{{$rol->description}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label for="codigo_movil">Código Mozo</label>
                                <input type="number" name="codigo_movil" id="codigo_movil" value="{{old('codigo_movil')}}" 
                                       class="form-control field-mozo" placeholder="Solo números">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Contraseña</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" name="password" id="password" class="form-control field-required">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" onclick="togglePass('password', this)"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group form-group-sm">
                                <label>Confirmar</label>
                                <div class="input-group input-group-sm">
                                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control field-required">
                                    <span class="input-group-btn">
                                        <button class="btn btn-default" type="button" onclick="togglePass('password_confirmation', this)"><i class="fa fa-eye"></i></button>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box-footer" style="background: none; border-top: 1px solid #f4f4f4; padding: 20px 0 0 0;">
                        <img style="display:none;" width="40px" src="/img/load.gif" id="imgloadreg">
                        <div class="pull-right">
                            <a href="/empleado" class="btn btn-default btn-flat botones"><i class="fa fa-times"></i> Cancelar</a>
                            <button type="button" class="btn btn-primary btn-flat botones shadow" id="btnRegistrar">
                                <i class="fa fa-save"></i> <strong>REGISTRAR EMPLEADO</strong>
                            </button>
                        </div>
                    </div>
                    {!!Form::close()!!}
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SCRIPTS DE LÓGICA -->
<script type="text/javascript">
    $(document).ready(function() {
        // Bloquear Enter para no enviar formulario accidentalmente
        $("form").keypress(function(e) {
            if (e.which == 13) return false;
        });

        // Lógica de Rol: Mozo
        $("#rol_id").on("change", function() {
            var rolSeleccionado = $("#rol_id option:selected").text().toLowerCase();
            if(rolSeleccionado.includes('mozo')) {
                $("#codigo_movil").attr('required', true).addClass('field-required').removeClass('field-mozo');
                $("label[for='codigo_movil']").html('Código Mozo <small class="text-danger">*</small>');
            } else {
                $("#codigo_movil").removeAttr('required').removeClass('field-required').addClass('field-mozo');
                $("label[for='codigo_movil']").html('Código Mozo');
            }
        });
        $("#rol_id").trigger('change');

        // Registro AJAX
        $("#btnRegistrar").on("click", function() {
            var formulario = $("#frmEmpleado").serializeArray();
            $(".botones").hide();
            $("#imgloadreg").show();
            
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: '/empleado',
                data: formulario,
            }).done(function(respuesta){
                alert(respuesta.mensaje);
                if(respuesta.estado !== 'error'){
                    window.location.href = "/empleado";
                } else {
                    $("#imgloadreg").hide();
                    $(".botones").show();
                }
            });
        });
    });

    // Función Autocompletar DNI
    function buscarcliente() {
        var dni = $("#emp_num_doc").val();
        
        if (dni.length !== 8 && dni.length !== 11) {
            alert('Por favor, ingrese un DNI válido de 8 dígitos o un RUC de 11 dígitos.');
            return;
        }

        $("#imgload").show();
        $.ajax({
            type: "get",
            dataType: 'json',
            url: '/consultar-documento',
            data: { documento: dni }
        }).done(function(respuesta){
            $("#imgload").hide();
            
            if (respuesta.error) {
                alert(respuesta.error);
                return;
            }

            if (respuesta.success) {
                var data = respuesta.data;
                if (dni.length === 8) {
                    // Si es DNI, rellena nombres y apellidos independientes
                    $('#emp_nom').val(data.nombres);
                    $('#emp_ape_pat').val(data.apellido_paterno);
                    $('#emp_ape_mat').val(data.apellido_materno);
                } else if (dni.length === 11) {
                    // Si es RUC, asigna la razón social al campo de nombre y limpia los apellidos
                    $('#emp_nom').val(data.nombre_o_razon_social);
                    $('#emp_ape_pat').val('');
                    $('#emp_ape_mat').val('');
                }
            } else {
                alert('No se encontraron resultados para el documento ingresado.');
            }
        }).fail(function(){
            $("#imgload").hide();
            alert('Ocurrió un error al procesar la consulta externa.');
        });
    }

    // Función Ver Contraseña
    function togglePass(id, btn) {
        var x = document.getElementById(id);
        var icon = btn.querySelector('i');
        if (x.type === "password") {
            x.type = "text";
            icon.className = "fa fa-eye-slash";
        } else {
            x.type = "password";
            icon.className = "fa fa-eye";
        }
    }
</script>

@endsection