@extends('layouts.empresas')

@section('contenido')
<style>
    /* Estilos responsivos y Premium para la configuración de IP */
    .ip-display-box {
        background: #f8f9fa;
        border: 2px dashed #007bff;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        margin-bottom: 25px;
    }
    .ip-badge-text {
        font-size: 2rem;
        letter-spacing: 2px;
        color: #007bff;
        font-weight: 900;
        display: inline-block;
        margin: 10px 0;
    }
    .ip-input-field {
        font-size: 1.8rem !important;
        letter-spacing: 2px;
        height: 60px !important;
        text-align: center;
        font-weight: bold;
        color: #2c3e50 !important;
        border: 2px solid #ced4da;
        border-radius: 8px 0 0 8px !important;
    }
    .btn-copiar-ip {
        font-size: 1.2rem !important;
        padding: 0 25px !important;
        font-weight: bold;
        border-radius: 0 8px 8px 0 !important;
        height: 60px !important;
    }
    
    /* Adaptación perfecta para celulares */
    @media (max-width: 768px) {
        .header-flex {
            flex-direction: column;
            text-align: center;
            gap: 12px;
        }
        .header-flex a { width: 100%; }
        .ip-badge-text { font-size: 1.4rem; letter-spacing: 1px; }
        .ip-input-field { font-size: 1.2rem !important; height: 50px !important; letter-spacing: 1px; }
        .btn-copiar-ip { font-size: 1rem !important; padding: 0 15px !important; height: 50px !important; }
        .btn-save-ip { width: 100%; font-size: 1.2rem !important; }
    }
</style>

<section class="content" style="padding-top: 20px;">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10 col-xs-12 col-lg-offset-2 col-md-offset-1">
            <div class="box shadow-box">
                
                <div class="box-header custom-header header-flex" style="display: flex; justify-content: space-between; align-items: center; padding: 15px 20px;">
                    <h3 class="box-title" style="color: white; font-weight: bold; margin: 0; font-size: 18px;">
                        <i class="fas fa-network-wired text-warning"></i> RED DE ASISTENCIA (WIFI)
                    </h3>
                    <a href="{{ route('asistencia.horarios') }}" class="btn btn-default btn-sm btn-elegant" style="font-weight: bold; color: #333;">
                        <i class="fas fa-arrow-left"></i> Volver a Horarios
                    </a>
                </div>
                
                <div class="box-body" style="padding: 30px 20px;">
                    
                    @if(session('success'))
                        <div class="alert alert-success alert-elegant alert-dismissible fade in" style="border-left: 5px solid #28a745 !important;">
                            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                            <h4><i class="icon fa fa-check-circle"></i> ¡Actualizado!</h4>
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="ip-display-box shadow-sm">
                        <h4 style="margin-top: 0; color: #2c3e50; font-weight: bold;">
                            <i class="fas fa-wifi text-info"></i> Tu IP de conexión a Internet actual es:
                        </h4>
                        
                        <div>
                            <span id="txtIpPublica" class="ip-badge-text">
                                <i class="fas fa-spinner fa-spin"></i> Detectando IP...
                            </span>
                        </div>
                        
                        <p class="text-muted" style="margin-bottom: 0; font-size: 14px;">
                            Para que los empleados solo puedan marcar asistencia desde la cafetería, 
                            <strong>asegúrate de estar conectado al Wi-Fi del local</strong> antes de guardar esta IP.
                        </p>
                    </div>

                    <form action="{{ route('asistencia.configurar_ip.update') }}" method="POST">
                        @csrf
                        
                        <div class="form-group" style="margin-top: 30px;">
                            <label style="color: #2c3e50; font-size: 15px; font-weight: bold; text-transform: uppercase;">
                                IP Pública Autorizada
                            </label>
                            
                            <div class="input-group" style="box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-radius: 8px;">
                                <input type="text" 
                                       id="input_ip"
                                       name="ip_asistencia" 
                                       class="form-control ip-input-field" 
                                       value="{{ $negocio ? $negocio->ip_asistencia : '' }}" 
                                       placeholder="Ej: 179.6.251.86">
                                       
                                <span class="input-group-btn">
                                    <button class="btn btn-primary btn-copiar-ip btn-elegant" type="button" id="btnCopiarIp" title="Copiar IP detectada" disabled>
                                        <i class="fas fa-copy"></i> <span class="hidden-xs">Copiar IP</span>
                                    </button>
                                </span>
                            </div>

                            <div class="alert alert-warning alert-elegant mt-3" style="margin-top: 15px; padding: 12px; background-color: #fff8e1; border-left: 4px solid #ffc107; color: #856404;">
                                <i class="fas fa-exclamation-triangle"></i> <strong>Nota:</strong> Si dejas este campo vacío, se desactivará temporalmente la restricción y podrán marcar desde cualquier ubicación.
                            </div>
                        </div>

                        <hr style="border-top: 1px solid #eee; margin: 30px 0 20px;">

                        <div class="text-center">
                            <button type="submit" class="btn btn-success btn-lg btn-elegant btn-save-ip" style="padding: 12px 40px; font-weight: bold;">
                                <i class="fas fa-save"></i> Guardar IP Autorizada
                            </button>
                        </div>
                        
                    </form>

                </div>
            </div>
        </div>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        var txtIpPublica = document.getElementById('txtIpPublica');
        var btnCopiarIp = document.getElementById('btnCopiarIp');
        var inputIp = document.getElementById('input_ip');
        var ipDetectada = "";

        // Consultamos un servicio externo gratuito para obtener la IP pública de internet real del cliente
        fetch('https://api.ipify.org?format=json')
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data.ip) {
                    ipDetectada = data.ip;
                    txtIpPublica.innerHTML = '<i class="fas fa-globe"></i> ' + ipDetectada;
                    btnCopiarIp.removeAttribute('disabled');
                } else {
                    txtIpPublica.innerText = "{{ request()->ip() }}";
                }
            })
            .catch(function(error) {
                // Si la API falla por bloques de red, se muestra la IP detectada por Laravel por defecto
                txtIpPublica.innerText = "{{ request()->ip() }}";
                console.log("Error al detectar IP pública externa: ", error);
            });

        // Evento para rellenar el input automáticamente al hacer clic en el botón Copiar
        btnCopiarIp.addEventListener('click', function() {
            if (ipDetectada !== "") {
                inputIp.value = ipDetectada;
            }
        });
    });
</script>
@endsection