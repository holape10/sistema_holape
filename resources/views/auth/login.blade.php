<!DOCTYPE html>
<html lang="es">

<script language="JavaScript1.2"> 
function tremer(n) { 
    if (self.moveBy) { 
        for (i = 10; i > 0; i--) { 
            for (j = n; j > 0; j--) { 
                self.moveBy(0,i); self.moveBy(i,0); self.moveBy(0,-i); self.moveBy(-i,0); 
            }
        }
    }
} 
</script>

<script language=javascript> 
function right(e) { 
    if(navigator.appName == 'Netscape' && (e.which == 3 || e.which == 2)){
        return false; 
    }
    if (navigator.appName == 'Microsoft Internet Explorer' && (event.button == 2 || event.button == 3)){
        return false; 
    }else{
        return true; 
    } 
}
document.onmousedown=right; 
if (document.layers) window.captureEvents(Event.MOUSEDOWN); window.onmousedown=right; 
</script>

<head>
    <title>SISTEMA DE GESTION COMERCIAL</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <link rel="icon" type="image/png" href="login_style/images/icons/icono_hp.ico"/>
    <link rel="stylesheet" type="text/css" href="login_style/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="login_style/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" type="text/css" href="login_style/vendor/animate/animate.css">
    <link rel="stylesheet" type="text/css" href="login_style/vendor/css-hamburgers/hamburgers.min.css">
    <link rel="stylesheet" type="text/css" href="login_style/vendor/select2/select2.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('{{ asset("img/logo_holape.png") }}');
            background-attachment: fixed;
            background-position: center;
            background-size: cover; 
            background-repeat: no-repeat;
            opacity: 0.10; 
            pointer-events: none;
            z-index: 0;
        }
        
        .limiter {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .container-login100 {
            width: 100%;
            min-height: 100vh;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            padding: 15px;
            background: transparent;
            position: relative;
            z-index: 1;
        }
        
        .wrap-login100 {
            width: 380px; 
            max-width: 90%; 
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.2);
            overflow: hidden;
            position: relative;
            border: 1px solid rgba(255,255,255,0.3);
            animation: slideUp 0.8s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(40px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .login100-pic {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 30px 20px; 
            position: relative;
            overflow: hidden;
            text-align: center;
            min-height: 180px; 
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login100-pic::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="25" cy="25" r="1.5" fill="rgba(255,255,255,0.08)"/><circle cx="75" cy="75" r="1.5" fill="rgba(255,255,255,0.08)"/></svg>') repeat;
            animation: float 20s infinite linear;
            pointer-events: none;
        }

        @keyframes float {
            0% { transform: translateY(0) rotate(0deg); }
            100% { transform: translateY(-20px) rotate(10deg); }
        }
        
        .login100-pic img {
            width: 130px; 
            height: 130px; 
            border-radius: 15px;
            border: 5px solid rgba(255,255,255,0.95);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 2;
            background: white;
            object-fit: contain;
            padding: 10px; 
        }
        
        .login100-pic img:hover {
            transform: scale(1.08) rotateY(5deg);
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
        }
        
        .login100-form {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            padding: 30px 35px 40px 35px; 
        }
        
        .login100-form-title {
            font-size: 24px; 
            color: #333;
            line-height: 1.2;
            text-align: center;
            width: 100%;
            font-weight: 700;
            margin-bottom: 25px; 
            text-shadow: 0 2px 4px rgba(0,0,0,0.08);
            letter-spacing: -0.5px;
        }
        
        .wrap-input100 {
            position: relative;
            width: 100%;
            margin-bottom: 18px; 
            z-index: 1;
        }
        
        .input100 {
            font-size: 14px; 
            color: #333;
            line-height: 1.5;
            display: block;
            width: 100%;
            height: 48px; 
            background: rgba(255,255,255,0.9);
            padding: 0 25px 0 45px;
            border-radius: 12px;
            border: 2px solid rgba(102, 126, 234, 0.2);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            outline: none;
            font-weight: 500;
        }
        
        .input100::placeholder {
            color: #999;
            font-weight: 500;
        }
        
        .input100:focus {
            border-color: #667eea;
            background: #fff;
            box-shadow: 0 0 0 0.3rem rgba(102, 126, 234, 0.15);
            transform: translateY(-2px);
        }
        
        .symbol-input100 {
            font-size: 16px; 
            color: #667eea;
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            font-weight: 600;
        }
        
        .input100:focus ~ .symbol-input100 {
            color: #764ba2;
            font-size: 18px; 
        }
        
        .focus-input100 {
            display: block;
            position: absolute;
            border-radius: 12px;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            pointer-events: none;
            background-color: #667eea;
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }
        
        .input100:focus + .focus-input100 {
            transform: scaleX(1);
        }
        
        .container-login100-form-btn {
            width: 100%;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            margin-top: 15px;
        }
        
        .login100-form-btn {
            font-size: 14px; 
            line-height: 1.5;
            color: #fff;
            text-transform: uppercase;
            width: 100%;
            height: 50px; 
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            letter-spacing: 1.5px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.35);
            position: relative;
            overflow: hidden;
        }

        .login100-form-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s ease;
        }

        .login100-form-btn:hover::before {
            left: 100%;
        }
        
        .login100-form-btn:hover {
            transform: translateY(-3px); 
            box-shadow: 0 12px 40px rgba(102, 126, 234, 0.4);
        }
        
        .login100-form-btn:active {
            transform: translateY(-1px);
        }

        .login100-form-btn i {
            margin-right: 8px;
            font-size: 15px;
        }
        
        .text-center {
            width: 100%;
            margin-top: 20px;
            text-align: center;
        }
        
        .txt1, .txt2 {
            font-size: 13px; 
            color: #666;
            line-height: 1.5;
        }
        
        .txt2 {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .txt2:hover {
            color: #764ba2;
            text-decoration: none;
        }
        
        .select2-container--default .select2-selection--single {
            height: 48px !important; 
            border-radius: 12px !important;
            border: 2px solid rgba(102, 126, 234, 0.2) !important;
            background: rgba(255,255,255,0.9) !important;
            transition: all 0.3s ease !important;
        }
        
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 44px !important; 
            padding-left: 45px !important;
            color: #333 !important;
            font-weight: 500 !important;
            font-size: 14px; 
        }
        
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 44px !important; 
        }
        
        .p-t-136 {
            padding-top: 20px; 
        }

        @media (max-width: 768px) {
            .wrap-login100 {
                width: calc(100% - 30px);
                max-width: 90%;
            }
            .login100-pic img {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</head>

<body>
    {{-- Obtenemos los datos y los convertimos para JS --}}
    @php
        $empresas = DB::table('empresa')->get();
        $sucursales = DB::table('empresa_negocios')->get();
        $terminales = DB::table('configuracion_impresoras')->get();
        $empresaFirst = DB::table('empresa')->first();
        
        // Convertimos las sucursales a JSON para filtrarlas con JavaScript
        $sucursalesJson = json_encode($sucursales);
    @endphp

    <div class="limiter">
        <div class="container-login100">
            <div class="wrap-login100">
                <div class="login100-pic">
                    @if(!empty($empresaFirst) && !empty($empresaFirst->LogEmpresa))
                        <img src="/{{$empresaFirst->LogEmpresa}}" alt="Logo Empresa">
                    @else
                        <img src="{{ asset('img/logo_holape.png') }}" alt="Logo Principal">
                    @endif
                </div>

                <form class="login100-form validate-forml" autocomplete="off" method="POST" action="/login">
                    {{ csrf_field() }}
                    
                    <span class="login100-form-title">
                        INICIAR SESIÓN
                    </span>

                    <div class="wrap-input100 validate-input" data-validate="Usuario requerido">
                        <input class="input100" type="text" name="email" id="email" placeholder="USUARIO" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-envelope" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Contraseña requerida">
                        <input class="input100" type="password" id="password" name="password" placeholder="CONTRASEÑA" required>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-lock" aria-hidden="true"></i>
                        </span>
                    </div>
                    
                    <!-- SELECT EMPRESA -->
                    <div class="wrap-input100 validate-input">
                        <select class="input100" name="empresa" id="empresa_select" required style="padding-left: 45px;">
                            @foreach($empresas as $index => $emp)
                            <option value="{{$emp->IdEmpresa}}" {{ $index == 0 ? 'selected' : '' }}>
                                {{$emp->NomEmpresa}}
                            </option>
                            @endforeach
                        </select>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-building" aria-hidden="true"></i>
                        </span>
                    </div>

                    <!-- SELECT SUCURSAL (Se llenará con JavaScript) -->
                    <div class="wrap-input100 validate-input">
                        <select class="input100" name="sucursal" id="sucursal_select" required style="padding-left: 45px;">
                            <!-- JS se encarga de esto -->
                        </select>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-home" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div hidden="hidden" class="wrap-input100 validate-input">
                        <select class="input100" name="terminal" id="terminal_select" style="padding-left: 45px;">
                            @foreach($terminales as $terminal)
                            <option value="{{$terminal->Id}}" {{ $loop->first ? 'selected' : '' }}>
                                {{$terminal->descripcion}}
                            </option>
                            @endforeach
                        </select>
                        <span class="focus-input100"></span>
                        <span class="symbol-input100">
                            <i class="fa fa-print" aria-hidden="true"></i>
                        </span>
                    </div>

                    <div class="container-login100-form-btn">
                        <button class="login100-form-btn" type="submit">
                            <i class="fa fa-sign-in"></i> INGRESAR
                        </button>
                    </div>

                    <div class="text-center p-t-12">
                        <span class="txt1"></span>
                        <a class="txt2" href="#"></a>
                    </div>

                    <div class="text-center p-t-136">
                        <a class="txt2" href="#" style="font-size: 12px; color: #999;">
                            SISTEMA DE GESTIÓN COMERCIAL 
                            <i class="fa fa-rocket m-l-5" aria-hidden="true"></i>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="login_style/vendor/jquery/jquery-3.2.1.min.js"></script>
    <script src="login_style/vendor/bootstrap/js/popper.js"></script>
    <script src="login_style/vendor/bootstrap/js/bootstrap.min.js"></script>
    <script src="login_style/vendor/select2/select2.min.js"></script>
    <script src="login_style/vendor/tilt/tilt.jquery.min.js"></script>
    
    <script>
        // Array global de todas las sucursales cargado desde PHP
        var todasLasSucursales = {!! $sucursalesJson !!};

        $('.js-tilt').tilt({
            scale: 1.1,
            speed: 400,
            transition: true,
            axis: null,
            reset: true,
            easing: "cubic-bezier(.25,.46,.45,.94)"
        });
        
        $(document).ready(function() {
            // Inicializar Select2
            $('#empresa_select').select2({
                placeholder: "Selecciona una empresa",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: Infinity
            });

            $('#sucursal_select').select2({
                placeholder: "Selecciona una sucursal",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: Infinity
            });
            
            $('#terminal_select').select2({
                width: '100%',
                minimumResultsForSearch: Infinity 
            });

            // Función para filtrar y repoblar el Select de Sucursales
            function filtrarSucursales(idEmpresa) {
                var sucursalSelect = $('#sucursal_select');
                sucursalSelect.empty(); // Limpiar las opciones actuales
                
                // Filtrar las sucursales que pertenecen a la empresa seleccionada
                var filtradas = todasLasSucursales.filter(function(sucursal) {
                    return sucursal.IdEmpresa === idEmpresa;
                });

                // Si hay sucursales, agregarlas al select
                if (filtradas.length > 0) {
                    filtradas.forEach(function(sucursal) {
                        var option = new Option(sucursal.tipo_negocio, sucursal.id_empresa_negocio, false, false);
                        sucursalSelect.append(option);
                    });
                } else {
                    // Opcional: mostrar un mensaje si no hay sucursales
                    var option = new Option("Sin sucursales registradas", "", false, false);
                    sucursalSelect.append(option);
                }
                
                // Actualizar la vista de Select2 y seleccionar la primera opción por defecto
                sucursalSelect.trigger('change');
            }

            // Detectar cuando el usuario cambia la empresa
            $('#empresa_select').on('change', function() {
                var idEmpresa = $(this).val();
                filtrarSucursales(idEmpresa);
            });

            // Carga inicial: Filtrar las sucursales de la empresa seleccionada por defecto al cargar la página
            var empresaInicial = $('#empresa_select').val();
            if (empresaInicial) {
                filtrarSucursales(empresaInicial);
            }
        });
    </script>
    
    <script src="login_style/js/main.js"></script>
</body>
</html>