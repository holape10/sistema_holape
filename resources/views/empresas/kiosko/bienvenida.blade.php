<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido - Pedidos DEVSOFT</title>
    <link rel="shortcut icon" href="img/icono_hp.ico">
    <link rel="stylesheet" href="{{ asset('adminlte/bower_components/bootstrap/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/font-awesome6/css/all.min.css') }}">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: #f8f9fa; /* Color de fondo suave */
            font-family: Arial, sans-serif;
            text-align: center;
        }
        .welcome-container {
            max-width: 800px;
            padding: 30px;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        /* Estilo para el logo */
        .welcome-container img.logo {
            max-width: 250px; /* Ajusta el tamaño máximo del logo */
            height: auto;
            margin-bottom: 30px; /* Espacio debajo del logo */
        }
        h1 {
            color: #3498db; /* Un azul vibrante */
            font-size: 3.5em;
            margin-bottom: 20px;
        }
        p {
            color: #555;
            font-size: 1.8em;
            margin-bottom: 40px;
        }
        .btn-start {
            background-color: #28a745; /* Verde para acción principal */
            color: white;
            font-size: 2.5em;
            padding: 25px 50px;
            border-radius: 10px;
            transition: background-color 0.3s ease, transform 0.1s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            border: none;
            cursor: pointer;
        }
        .btn-start:hover {
            background-color: #218838;
            transform: translateY(-3px);
        }
        /* Media Queries para pantallas más pequeñas */
        @media (max-width: 768px) {
            .welcome-container img.logo {
                max-width: 200px;
                margin-bottom: 20px;
            }
            h1 {
                font-size: 2.5em;
            }
            p {
                font-size: 1.4em;
            }
            .btn-start {
                font-size: 2em;
                padding: 20px 40px;
            }
        }
        @media (max-width: 480px) {
            .welcome-container img.logo {
                max-width: 150px;
                margin-bottom: 15px;
            }
            h1 {
                font-size: 2em;
            }
            p {
                font-size: 1.2em;
            }
            .btn-start {
                font-size: 1.8em;
                padding: 15px 30px;
                width: 90%; /* Ajusta para llenar más el ancho en móviles */
            }
        }
    </style>
</head>
<body>
    @php

    $sucursales= DB::tABLE('empresa_negocios')->get();    
    $empresa = DB::tABLE('empresa')->first();
    @endphp
    <div class="welcome-container">
        {{-- Aquí se inserta la etiqueta del logo --}}
        {{-- Asegúrate de que 'images/tu_logo.png' sea la ruta correcta a tu logo dentro de la carpeta 'public' --}}
        {{-- Por ejemplo, si tu logo está en public/mi_empresa/logo.png, usa asset('mi_empresa/logo.png') --}}

                    @if(!empty($empresa))
                        <img src="/{{$empresa->LogEmpresa}}" style="padding-left:0px; height:auto;" width="300px" alt="IMG">
                        <!--<img src="login_style/images/logo_hp_sf.png" alt="holape">-->
                    @else
                        <img src="img/logo_inicio.png" style="padding-left:0px; height:auto;" width="300px" alt="holape">
                    @endif
        <!--<img src="{{ asset('img/logo_devsoft_engranaje.png') }}" alt="Logo de DEVSOFT" class="logo">-->

        <!--<h1>¡Bienvenidos a DEVSOFT</h1>
        <p>Toca la pantalla para iniciar tu pedido.</p>-->
        <a href="{{ route('kiosko.seleccion_servicio') }}" class="btn btn-start">
            <strong>INICIAR PEDIDO</strong>
        </a>

        <br><br><br><br><br>
        @if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja'))
         <a href="/consolacaja" class="btn btn btn-md btn-primary">
            <strong>CAJA</strong>
        </a>
        @endif

        <a href="/logout" class="btn btn btn-md btn-danger">
            <strong>CERRAR SESIÓN</strong>
        </a>

        <!--<a hidden='hidden' href="/consola" class="btn btn btn-md btn-primary">
            <strong>VOLVER</strong>
        </a>-->
    </div>

    <script src="{{ asset('adminlte/bower_components/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('adminlte/bower_components/bootstrap/dist/js/bootstrap.min.js') }}"></script>
</body>
</html>