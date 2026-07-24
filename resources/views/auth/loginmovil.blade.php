<!DOCTYPE html>
<html lang="es">
<head>
    <title>LOGIN MÓVIL - SISTEMA DE GESTIÓN</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <link rel="icon" type="image/png" href="/login_style/images/icons/icono_hp.ico"/>
    <link rel="stylesheet" type="text/css" href="/login_style/vendor/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="/login_style/fonts/font-awesome-4.7.0/css/font-awesome.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-container {
            background: white;
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-container img {
            max-width: 150px;
            height: auto;
        }
        
        .login-title {
            text-align: center;
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            color: #666;
            font-size: 14px;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
            font-size: 18px;
        }
        
        .form-control {
            width: 100%;
            padding: 15px 15px 15px 45px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 18px;
            transition: all 0.3s;
            -webkit-appearance: none;
            appearance: none;
        }
        
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        /* Estilos para inputs numéricos en móviles */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        input[type="number"] {
            -moz-appearance: textfield;
        }
        
        select.form-control {
            background-color: #f8f9fa;
            cursor: pointer;
            padding-left: 45px;
        }
        
        .btn-login {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s;
            margin-top: 10px;
        }
        
        .btn-login:active {
            transform: scale(0.98);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-danger {
            background-color: #fee;
            color: #c33;
            border: 1px solid #fcc;
        }
        
        .footer-text {
            text-align: center;
            margin-top: 20px;
            color: #666;
            font-size: 12px;
        }
        
        .link-desktop {
            text-align: center;
            margin-top: 15px;
        }
        
        .link-desktop a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        /* Mejoras para tablets */
        @media (min-width: 768px) {
            .login-container {
                max-width: 450px;
            }
            
            .form-control {
                font-size: 20px;
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
        <div class="logo-container">
            @if(!empty($empresa))
                <img src="/{{$empresa->LogEmpresa}}" alt="Logo">
            @else
                <img src="login_style/images/logo_hp_sf.png" alt="Logo">
            @endif
        </div>
        
        <h1 class="login-title">LOGIN MÓVIL</h1>
        
        @if ($errors->any())
            <div class="alert alert-danger">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif
        
        <form method="POST" action="/logmovil" autocomplete="off">
            {{ csrf_field() }}
            
            <div class="form-group">
                <label for="codigo_movil">CÓDIGO</label>
                <div class="input-wrapper">
                    <i class="fa fa-mobile" aria-hidden="true"></i>
                    <input 
                        type="number" 
                        inputmode="numeric"
                        pattern="[0-9]*"
                        class="form-control" 
                        id="codigo_movil" 
                        name="codigo_movil" 
                        placeholder="Ingrese su código"
                        value="{{ old('codigo_movil') }}"
                        required
                        autofocus>
                </div>
            </div>
            
            <div class="form-group">
                <label for="password">CONTRASEÑA</label>
                <div class="input-wrapper">
                    <i class="fa fa-lock" aria-hidden="true"></i>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="password" 
                        name="password" 
                        placeholder="Ingrese su contraseña"
                        required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="sucursal">SUCURSAL</label>
                <div class="input-wrapper">
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
            
            <div class="form-group" style="display:none;">
                <select class="form-control" name="terminal">
                    @foreach($terminales as $terminal)
                        <option value="{{$terminal->Id}}">{{$terminal->descripcion}}</option>
                    @endforeach
                </select>
            </div>
            
            <button type="submit" class="btn-login">
                <i class="fa fa-sign-in"></i> INGRESAR
            </button>
        </form>
        
        <div class="link-desktop">
            <a href="/login">
                <i class="fa fa-desktop"></i> Ir a Login Escritorio
            </a>
        </div>
        
        <div class="footer-text">
            SISTEMA DE GESTIÓN COMERCIAL
        </div>
    </div>

    <script src="login_style/vendor/jquery/jquery-3.2.1.min.js"></script>
    <script>
        // Prevenir zoom en iOS al enfocar inputs
        document.addEventListener('touchstart', function(e) {
            if (e.touches.length > 1) {
                e.preventDefault();
            }
        }, { passive: false });
        
        // Forzar teclado numérico en el campo código
        document.getElementById('codigo_movil').addEventListener('focus', function() {
            this.setAttribute('inputmode', 'numeric');
        });
    </script>
</body>
</html>