<!DOCTYPE html>
<html lang="es">
<head>
    <title>LOGIN MÓVIL - SISTEMA DE GESTIÓN</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="icon" type="image/png" href="/login_style/images/icons/icono_hp.ico"/>
    <link rel="stylesheet" type="text/css" href="/login_style/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        
        .login-container {
            background: white;
            border-radius: 25px;
            padding: 25px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 50px rgba(0,0,0,0.3);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .logo-container img {
            max-width: 120px;
            height: auto;
        }
        
        .login-title {
            text-align: center;
            color: #333;
            font-size: 26px;
            font-weight: bold;
            margin-bottom: 25px;
        }

        /* ESTILOS PARA LOS BOTONES DE USUARIO */
        .user-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }

        .btn-user {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-radius: 15px;
            padding: 15px 10px;
            font-size: 14px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: all 0.3s;
            text-transform: uppercase;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .btn-user:active {
            transform: scale(0.95);
        }

        .btn-user:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }

        .selected-user-header {
            text-align: center;
            margin-bottom: 15px;
            font-size: 18px;
            color: #333;
            font-weight: bold;
        }

        .btn-back {
            background: none;
            border: none;
            color: #667eea;
            text-decoration: underline;
            cursor: pointer;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .pin-display {
            background: #f5f5f5;
            border: 3px solid #667eea;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
            min-height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #333;
        }
        
        .pin-display.empty {
            color: #999;
            font-size: 18px;
            letter-spacing: normal;
        }
        
        .keypad {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        
        .key {
            background: linear-gradient(145deg, #f0f0f0, #e0e0e0);
            border: none;
            border-radius: 15px;
            padding: 25px;
            font-size: 28px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            -webkit-tap-highlight-color: transparent;
        }
        
        .key:active {
            transform: scale(0.95);
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .key.delete {
            background: linear-gradient(145deg, #ff6b6b, #ee5a6f);
            color: white;
            font-size: 24px;
        }
        
        .key.zero {
            grid-column: span 2;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .select-wrapper {
            position: relative;
        }
        
        .select-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
            pointer-events: none;
        }
        
        select.form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 16px;
            background-color: #f8f9fa;
            cursor: pointer;
            -webkit-appearance: none;
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 15px center;
        }
        
        select.form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 20px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            margin-bottom: 15px;
        }
        
        .btn-login:active {
            transform: scale(0.98);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.4);
        }
        
        .btn-login:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 15px;
            font-size: 14px;
            text-align: center;
        }
        
        .alert-danger {
            background-color: #fee;
            color: #c33;
            border: 2px solid #fcc;
        }
        
        .link-desktop {
            text-align: center;
            margin-top: 15px;
        }
        
        .link-desktop a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 15px;
            color: #999;
            font-size: 11px;
        }
        
        @media (min-width: 768px) {
            .login-container {
                max-width: 450px;
            }
        }
    </style>
</head>
<body>
    @php
        $sucursales = DB::table('empresa_negocios')->get();
        $terminales = DB::table('configuracion_impresoras')->get();
        $empresa = DB::table('empresa')->first();
    @endphp

    <div class="login-container">
        <div class="logo-container" hidden='hidden'>
            @if(!empty($empresa))
                <img src="/{{$empresa->LogEmpresa}}" alt="Logo">
            @else
                <img src="/login_style/images/logo_hp_sf.png" alt="Logo">
            @endif
        </div>
        
        <h1 class="login-title">INICIAR SESIÓN</h1>
        
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <div id="userSelectionSection">
            <h3 style="text-align: center; color: #333; margin-bottom: 15px; font-size: 16px;">Selecciona tu usuario</h3>
            <div class="user-grid">
                @foreach($usuarios as $usuario)
                    <button type="button" class="btn-user" onclick="selectUser('{{$usuario->IdUsuario}}', '{{$usuario->name}} {{$usuario->apeusu}}')">
                        <i class="fa fa-user" style="font-size: 24px; margin-bottom: 8px; color: #667eea;"></i>
                        {{$usuario->name}} <br> {{$usuario->apeusu}}
                    </button>
                @endforeach
            </div>
        </div>

        <div id="pinPadSection" style="display: none;">
            
            <div class="selected-user-header">
                <span id="displaySelectedUserName"></span><br>
                <button type="button" class="btn-back" onclick="changeUser()">
                    <i class="fa fa-arrow-left"></i> Cambiar usuario
                </button>
            </div>

            <div class="pin-display" id="pinDisplay"></div>
            
            <div class="keypad">
                <button type="button" class="key" onclick="addDigit('1')">1</button>
                <button type="button" class="key" onclick="addDigit('2')">2</button>
                <button type="button" class="key" onclick="addDigit('3')">3</button>
                <button type="button" class="key" onclick="addDigit('4')">4</button>
                <button type="button" class="key" onclick="addDigit('5')">5</button>
                <button type="button" class="key" onclick="addDigit('6')">6</button>
                <button type="button" class="key" onclick="addDigit('7')">7</button>
                <button type="button" class="key" onclick="addDigit('8')">8</button>
                <button type="button" class="key" onclick="addDigit('9')">9</button>
                <button type="button" class="key zero" onclick="addDigit('0')">0</button>
                <button type="button" class="key delete" onclick="deleteDigit()">
                    <i class="fa fa-arrow-left"></i>
                </button>
            </div>
            
            <form method="POST" action="/logmovil" id="loginForm">
                {{ csrf_field() }}
                
                <input type="hidden" name="usuario_id" id="usuarioInput" value="">
                <input type="hidden" name="codigo_movil" id="codigoMovilInput" value="">
                
                <div class="form-group" hidden='hidden'>
                    <label for="sucursal">SUCURSAL</label>
                    <div class="select-wrapper">
                        <i class="fa fa-home" aria-hidden="true"></i>
                        <select class="form-control" name="sucursal" id="sucursal" required>
                            @foreach($sucursales as $sucursal)
                                <option value="{{$sucursal->id_empresa_negocio}}">
                                    {{$sucursal->tipo_negocio}}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div style="display:none;">
                    <select class="form-control" name="terminal">
                        @foreach($terminales as $terminal)
                            <option value="{{$terminal->Id}}">{{$terminal->descripcion}}</option>
                        @endforeach
                    </select>
                </div>
                
                <button type="submit" class="btn-login" id="btnLogin" disabled>
                    <i class="fa fa-sign-in"></i> INGRESAR
                </button>
            </form>
        </div>
        
        <div class="link-desktop">
            <a href="/login">
                <i class="fa fa-desktop"></i> Ir a Login Escritorio
            </a>
        </div>
        
        <div class="footer-text">
            SISTEMA DE GESTIÓN COMERCIAL
        </div>
    </div>

    <script>
        let pin = '';
        const pinDisplay = document.getElementById('pinDisplay');
        const codigoMovilInput = document.getElementById('codigoMovilInput');
        const usuarioInput = document.getElementById('usuarioInput');
        const btnLogin = document.getElementById('btnLogin');
        
        const userSelectionSection = document.getElementById('userSelectionSection');
        const pinPadSection = document.getElementById('pinPadSection');
        const displaySelectedUserName = document.getElementById('displaySelectedUserName');

        function selectUser(id, name) {
            usuarioInput.value = id;
            displaySelectedUserName.textContent = 'Hola, ' + name;
            userSelectionSection.style.display = 'none';
            pinPadSection.style.display = 'block';
            checkFormValidity();
        }

        function changeUser() {
            usuarioInput.value = '';
            pin = '';
            updateDisplay();
            userSelectionSection.style.display = 'block';
            pinPadSection.style.display = 'none';
        }

        function addDigit(digit) {
            if (pin.length < 10) { 
                pin += digit;
                updateDisplay();
            }
        }

        function deleteDigit() {
            pin = pin.slice(0, -1);
            updateDisplay();
        }

        function updateDisplay() {
            if (pin.length === 0) {
                pinDisplay.textContent = 'Ingrese su código';
                pinDisplay.classList.add('empty');
            } else {
                // Aquí puedes poner una 'x' o '*' en vez de la variable pin si deseas ocultar la contraseña al tipear
                pinDisplay.textContent = pin; 
                pinDisplay.classList.remove('empty');
            }
            codigoMovilInput.value = pin;
            checkFormValidity();
        }

        function checkFormValidity() {
            if (pin.length > 0 && usuarioInput.value !== '') {
                btnLogin.disabled = false;
            } else {
                btnLogin.disabled = true;
            }
        }

        // Soporte para teclado físico
        document.addEventListener('keydown', function(e) {
            if (pinPadSection.style.display === 'block') {
                if (e.key >= '0' && e.key <= '9') {
                    addDigit(e.key);
                } else if (e.key === 'Backspace') {
                    e.preventDefault();
                    deleteDigit();
                } else if (e.key === 'Enter' && pin.length > 0 && usuarioInput.value !== '') {
                    document.getElementById('loginForm').submit();
                }
            }
        });

        // Prevenir zoom en iOS
        document.addEventListener('touchstart', function(e) {
            if (e.touches.length > 1) {
                e.preventDefault();
            }
        }, { passive: false });

        // Inicializar estado
        updateDisplay();
    </script>
</body>
</html>