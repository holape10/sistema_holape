<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asistencia - HolaPE</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; }
        
        /* Botón de Pantalla Completa */
        .btn-fullscreen {
            position: fixed; top: 10px; right: 10px; z-index: 9999;
            background: rgba(0,0,0,0.2); color: white; border: none; padding: 10px 15px; border-radius: 50%;
        }
        .btn-fullscreen:hover { background: rgba(0,0,0,0.5); }

        .btn-asistencia-base {
            border-radius: 12px; padding: 15px 5px; transition: all 0.3s ease; font-weight: bold;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            min-height: 140px; text-align: center; word-wrap: break-word; box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        /* --- ESTILOS DE ESTADOS --- */
        .btn-usuario-falta { background-color: #e2e6ea; border: 2px dashed #dc3545; color: #dc3545; opacity: 0.9; }
        .btn-usuario-falta:hover { background-color: #d3d9df; cursor: pointer; }

        .btn-usuario-tardanza { background-color: #fff3cd; border: 2px solid #ffc107; color: #856404; }
        .btn-usuario-tardanza:hover { background-color: #ffe8a1; cursor: pointer; transform: translateY(-3px); }
        
        .btn-usuario-descanso { background-color: #e0f7fa; border: 2px dashed #00acc1; color: #00838f; opacity: 0.9; }
        .btn-usuario-descanso:hover { background-color: #b2ebf2; cursor: pointer; } /* Ahora se puede dar click */
        
        .letra-estado { font-size: 3.5rem; font-weight: 900; line-height: 1; margin-bottom: 5px; }

        .btn-usuario { background-color: #ffffff; border: 2px solid #dee2e6; color: #333; }
        .btn-usuario:hover { background-color: #e2e6ea; cursor: pointer; transform: translateY(-3px); }
        
        .btn-usuario-entrada { background-color: #d4edda; border: 2px solid #28a745; color: #155724; }
        .btn-usuario-entrada:hover { background-color: #c3e6cb; cursor: pointer; transform: translateY(-3px); }

        .btn-usuario-salida { background-color: #f8d7da; border: 2px solid #dc3545; color: #721c24; }
        .btn-usuario-salida:hover { background-color: #f5c6cb; cursor: pointer; transform: translateY(-3px); }

        .nombre-empleado { font-size: 1.1rem; line-height: 1.2; }

        /* --- CONTENEDOR NUEVO DEL LECTOR --- */
        .seccion-lector {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            padding: 20px;
            margin-bottom: 25px;
            border: 1px solid #e3e6f0;
        }
        .input-lector-visible {
            font-size: 1.5rem;
            font-weight: bold;
            letter-spacing: 4px;
            text-align: center;
            border: 2px solid #4e73df;
            border-radius: 10px;
            color: #4e73df;
            background-color: #f8f9fc;
            transition: all 0.3s ease;
        }
        .input-lector-visible:focus {
            background-color: #fff;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            border-color: #2e59d9;
        }

        @media (max-width: 576px) {
            .nombre-empleado { font-size: 0.85rem; }
            .fa-user { font-size: 2em !important; }
            .btn-asistencia-base { min-height: 120px; }
        }

        #contenedorQR img, #contenedorQR canvas {
            max-width: 100%; height: auto !important; margin: 0 auto;
        }
    </style>
</head>
<body>

<button onclick="toggleFullScreen()" class="btn-fullscreen" title="Pantalla completa">
    <i class="fas fa-expand-arrows-alt" id="iconFullscreen"></i>
</button>

<div class="container-fluid mt-4 mb-5 px-4">
    <div class="text-center mt-3">
        <h2 class="mb-2 text-primary font-weight-bold">SISTEMA DE ASISTENCIA</h2>
        <p class="text-muted mb-3">Selecciona tu usuario para generar tu código QR o usa el Lector de Barra físico</p>
    </div>

    <div class="row justify-content-center mb-2">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="seccion-lector text-center">
                <label for="lector_dni" class="font-weight-bold text-secondary mb-2">
                    <i class="fas fa-barcode fa-lg mr-1 text-primary"></i> CONTROL POR LECTOR ÓPTICO
                </label>
                <input type="text" id="lector_dni" class="form-control input-lector-visible" autofocus autocomplete="off" placeholder="[ ESCANEE DNI AQUÍ ]">
                <small class="text-muted d-block mt-2">El sistema mantiene el enfoque automático en esta barra para recibir la lectura física.</small>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        @foreach($empleados as $emp)
            @php
                $asistencia = $asistenciasHoy->get($emp->emp_id);
                $estadoSistema = $estadosVisuales[$emp->emp_id] ?? 'normal';
                
                $claseBoton = 'btn-usuario';
                $iconoColor = 'text-primary';
                $mostrarLetra = false;
                $letraTexto = '';
                
                $funcionClick = "obtenerQRRealtime('{$emp->emp_nom} {$emp->emp_ape_pat}', '{$emp->emp_num_doc}')";

                if ($asistencia) {
                    $esTarde = $asistencia->tardanza_minutos > 0;
                    $claseActiva = $esTarde ? 'btn-usuario-tardanza' : 'btn-usuario-entrada';
                    $iconoActivo = $esTarde ? 'text-warning' : 'text-success';

                    if ($asistencia->check_in_1 && is_null($asistencia->check_out_1)) {
                        $claseBoton = $claseActiva; $iconoColor = $iconoActivo;
                    } elseif ($asistencia->check_out_1 && is_null($asistencia->check_in_2)) {
                        $claseBoton = 'btn-usuario-salida'; $iconoColor = 'text-danger';
                    } elseif ($asistencia->check_in_2 && is_null($asistencia->check_out_2)) {
                        $claseBoton = $claseActiva; $iconoColor = $iconoActivo;
                    } elseif ($asistencia->check_out_2) {
                        $claseBoton = 'btn-usuario-salida'; $iconoColor = 'text-danger';
                    }
                } 
                else {
                    if ($estadoSistema === 'falta') {
                        $claseBoton = 'btn-usuario-falta';
                        $mostrarLetra = true;
                        $letraTexto = 'F';
                        $iconoColor = 'text-danger';
                    } elseif ($estadoSistema === 'descanso') {
                        $claseBoton = 'btn-usuario-descanso';
                        $mostrarLetra = true;
                        $letraTexto = 'D';
                        $iconoColor = 'text-info';
                        // Ahora permitimos dar click para que el admin pueda registrar asistencia en días de descanso
                        $funcionClick = "obtenerQRRealtime('{$emp->emp_nom} {$emp->emp_ape_pat}', '{$emp->emp_num_doc}')";
                    }
                }
            @endphp

            <div class="col-6 col-sm-4 col-md-3 col-xl-2 mb-3 px-2">
                <div class="btn-asistencia-base {{ $claseBoton }} h-100" onclick="{{ $funcionClick }}">
                    
                    @if($mostrarLetra)
                        <div class="letra-estado {{ $iconoColor }}">{{ $letraTexto }}</div>
                    @else
                        <i class="fa fa-user fa-3x {{ $iconoColor }} mb-2"></i>
                    @endif
                    
                    <span class="nombre-empleado">{{ $emp->emp_nom }} <br> {{ $emp->emp_ape_pat }}  {{ $emp->emp_ape_mat }}</span>
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="modal fade" id="modalQR" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content border-0 shadow">
      <div class="modal-header bg-primary text-white border-0">
        <h5 class="modal-title">Escanea para marcar asistencia</h5>
        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body text-center p-4">
        <h4 id="nombreEmpleadoQR" class="mb-4 font-weight-bold"></h4>
        <div id="contenedorQR" class="d-inline-block p-3 border shadow-sm" style="background: white; border-radius: 15px;"></div>
        <p class="mt-4 text-muted" style="font-size: 0.95rem;">
            <i class="fas fa-mobile-alt mr-1"></i> Abre la cámara de tu celular.<br>
            <small>Este código expira en 1 minuto por seguridad.</small>
        </p>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="modalAuthAdmin" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title"><i class="fas fa-user-shield"></i> Autorización de Administrador</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-4 text-left">
                <div class="alert alert-warning text-center font-weight-bold" id="msgAuthAdmin"></div>
                
                <input type="hidden" id="auth_dni">
                
                <div class="form-group">
                    <label class="font-weight-bold">Usuario (Login Admin):</label>
                    <input type="text" id="admin_user" class="form-control" placeholder="Ej: admin">
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Contraseña:</label>
                    <input type="password" id="admin_pass" class="form-control">
                </div>
                
                <div class="form-group border-top pt-3 mt-2">
                    <label class="font-weight-bold text-primary">Hora de Ingreso a Registrar:</label>
                    <input type="time" id="admin_hora_ingreso" class="form-control border-primary">
                    <small class="text-muted">Por defecto se muestra la hora actual. Modifícala si olvidaron marcar.</small>
                </div>
                
                <div class="form-group border-top pt-3 mt-2">
                    <label class="font-weight-bold">Motivo (Predeterminado):</label>
                    <select id="admin_motivo_select" class="form-control">
                        <option value="">-- Seleccionar --</option>
                        @isset($motivos)
                            @foreach($motivos as $m)
                                <option value="{{ $m->descripcion }}">{{ $m->descripcion }}</option>
                            @endforeach
                        @endisset
                    </select>
                </div>
                <div class="form-group">
                    <label class="font-weight-bold">Motivo de Tardanza:</label>
                    <textarea id="admin_motivo" class="form-control" rows="2" placeholder="Ej: Lluvia extrema, permiso de gerencia..."></textarea>
                </div>
                
                <button type="button" class="btn btn-danger btn-block font-weight-bold mt-4 py-2" onclick="autorizarTardanzaBtn()">
                    <i class="fas fa-key"></i> Aprobar Ingreso
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
    const inputLector = document.getElementById('lector_dni');

    // Función para obtener la hora actual en formato HH:MM
    function getCurrentTime() {
        let now = new Date();
        let hours = String(now.getHours()).padStart(2, '0');
        let minutes = String(now.getMinutes()).padStart(2, '0');
        return `${hours}:${minutes}`;
    }

    // Mantenemos el enfoque dinámico sin molestar la edición en campos de texto legítimos externos
    document.addEventListener('click', function(e) {
        if (!$('#modalAuthAdmin').hasClass('show') && !$(e.target).closest('input, textarea, select').length) {
            inputLector.focus();
        }
    });

    // Escuchamos la pistola de código de barras
    inputLector.addEventListener('keypress', function (e) {
        if (e.key === 'Enter') {
            let dniEscaneado = this.value.trim();
            this.value = ''; // Se limpia inmediatamente para permitir la fluidez de marcas en fila

            if(dniEscaneado !== '') {
                procesarLectorFisico(dniEscaneado);
            }
        }
    });

    function procesarLectorFisico(dni) {
        fetch('/asistencia/lector-fisico/' + dni)
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                alert("✅ " + data.empleado + "\n" + data.message);
                location.reload();
            } else if (data.require_auth) {
                document.getElementById('msgAuthAdmin').innerText = data.message;
                document.getElementById('auth_dni').value = data.dni;
                document.getElementById('admin_user').value = '';
                document.getElementById('admin_pass').value = '';
                document.getElementById('admin_motivo').value = '';
                document.getElementById('admin_hora_ingreso').value = getCurrentTime(); // Set current time
                $('#modalAuthAdmin').modal('show');
            } else {
                alert("❌ Aviso:\n" + data.message);
            }
        }).catch(err => console.log(err));
    }
    
    function toggleFullScreen() {
        var elem = document.documentElement;
        var icon = document.getElementById('iconFullscreen');
        if (!document.fullscreenElement) {
            elem.requestFullscreen().catch(err => { alert(`Error: ${err.message}`); });
            icon.classList.replace('fa-expand-arrows-alt', 'fa-compress-arrows-alt');
        } else {
            document.exitFullscreen();
            icon.classList.replace('fa-compress-arrows-alt', 'fa-expand-arrows-alt');
        }
    }

    let intervalConsulta = null;
    let estadoInicial = null;

    function obtenerQRRealtime(nombre, dni) {
        document.getElementById('nombreEmpleadoQR').innerText = nombre;
        document.getElementById('contenedorQR').innerHTML = "";
        
        fetch('/asistencia/verificar-estado/' + dni).then(res => res.json()).then(estadoData => {
            estadoInicial = estadoData.status;
            
            fetch('/asistencia/generar-url/' + dni).then(response => response.json()).then(data => {
                if(data.success) {
                    new QRCode(document.getElementById("contenedorQR"), { text: data.url, width: 220, height: 220, colorDark : "#000000", colorLight : "#ffffff", correctLevel : QRCode.CorrectLevel.H });
                    $('#modalQR').modal('show');
                    intervalConsulta = setInterval(() => {
                        fetch('/asistencia/verificar-estado/' + dni).then(r => r.json()).then(nuevoEstado => {
                            if (nuevoEstado.status !== estadoInicial) {
                                clearInterval(intervalConsulta);
                                $('#modalQR').modal('hide');
                                location.reload(); 
                            }
                        });
                    }, 2000);
                } else if (data.require_auth) {
                    document.getElementById('msgAuthAdmin').innerText = data.message;
                    document.getElementById('auth_dni').value = dni;
                    document.getElementById('admin_user').value = '';
                    document.getElementById('admin_pass').value = '';
                    document.getElementById('admin_motivo').value = '';
                    document.getElementById('admin_hora_ingreso').value = getCurrentTime(); // Set current time
                    $('#modalAuthAdmin').modal('show');
                } else { 
                    alert("🚫 Error: " + data.message); 
                }
            });
        });
    }

    function autorizarTardanzaBtn() {
        var dni = document.getElementById('auth_dni').value;
        var user = document.getElementById('admin_user').value;
        var pass = document.getElementById('admin_pass').value;
        var horaIngreso = document.getElementById('admin_hora_ingreso').value;
        
        var selectMotivo = document.getElementById('admin_motivo_select').value;
        var textMotivo = document.getElementById('admin_motivo').value;
        var motivoFinal = selectMotivo;
        
        if(textMotivo.trim() !== '') {
            motivoFinal += (motivoFinal ? ' - ' : '') + textMotivo.trim();
        }

        if (!user || !pass || motivoFinal === '') {
            alert('Por favor ingresa tu usuario, contraseña y selecciona o escribe un motivo.');
            return;
        }

        fetch('/asistencia/autorizar-tardanza', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                dni: dni,
                admin_user: user,
                admin_password: pass,
                motivo: motivoFinal,
                hora_ingreso: horaIngreso
            })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                alert("✅ ¡ÉXITO!\n" + data.message);
                $('#modalAuthAdmin').modal('hide');
                location.reload();
            } else {
                alert("❌ ACCESO DENEGADO\n" + data.message);
            }
        });
    }

    $('#modalQR').on('hidden.bs.modal', function () {
        if (intervalConsulta) { clearInterval(intervalConsulta); }
    });
</script>
</body>
</html>